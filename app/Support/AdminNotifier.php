<?php

namespace App\Support;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminNotifier
{
    public static function resolveEmail(): string
    {
        try {
            $adminId = (int) env('ADMIN_NOTIFY_ADMIN_ID', 0);
            $adminUsername = trim((string) env('ADMIN_NOTIFY_ADMIN_USERNAME', ''));

            $admin = User::query()
                ->where('ruolo', 0)
                ->when($adminId > 0, fn ($q) => $q->whereKey($adminId))
                ->when($adminId <= 0 && $adminUsername !== '', fn ($q) => $q->where('username', $adminUsername))
                ->when($adminId <= 0 && $adminUsername === '', fn ($q) => $q->where('username', 'scintilla'))
                ->first();

            return trim((string) ($admin?->email ?? ''));
        } catch (\Throwable $e) {
            return '';
        }
    }

    public static function actorLabel(User $actor): string
    {
        $label = trim(($actor->nome ?? '') . ' ' . ($actor->cognome ?? ''));
        if ($label === '') {
            return $actor->nickname ?? ('ID ' . $actor->getKey());
        }

        return $label . ' (' . ($actor->nickname ?? $actor->getKey()) . ')';
    }

    public static function notifyGuestAddedToEvent(Event $event, User $actor): void
    {
        try {
            $adminId = (int) env('ADMIN_NOTIFY_ADMIN_ID', 0);
            $adminUsernameEnv = trim((string) env('ADMIN_NOTIFY_ADMIN_USERNAME', ''));

            $admin = User::query()
                ->where('ruolo', 0)
                ->when($adminId > 0, fn ($q) => $q->whereKey($adminId))
                ->when($adminId <= 0 && $adminUsernameEnv !== '', fn ($q) => $q->where('username', $adminUsernameEnv))
                ->when($adminId <= 0 && $adminUsernameEnv === '', fn ($q) => $q->where('username', 'scintilla'))
                ->first();

            $notifyEmail = trim((string) ($admin?->email ?? ''));
            if ($notifyEmail === '') {
                return;
            }

            $adminGreeting = trim((string) ($admin->username ?? 'scintilla'));
            $userUsername = trim((string) ($actor->username ?? ''));
            if ($userUsername === '') {
                $userUsername = trim((string) ($actor->nickname ?? 'utente'));
            }

            $title = trim((string) ($event->title ?? 'Evento'));
            $when = optional($event->date)->timezone(config('app.timezone'))->format('d/m/Y H:i');

            $subject = "un'ospite si è iscritto all'evento {$title}";
            $body =
                "Ciao {$adminGreeting}, un amico di {$userUsername} si è appena iscritto al tuo evento {$title} {$when}\n";

            Mail::raw($body, function ($message) use ($notifyEmail, $subject) {
                $message->to($notifyEmail)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning('Notify admin guest added failed: ' . $e->getMessage());
        }
    }

    public static function notifyGuestRemovedFromEvent(Event $event, User $actor): void
    {
        try {
            $adminId = (int) env('ADMIN_NOTIFY_ADMIN_ID', 0);
            $adminUsernameEnv = trim((string) env('ADMIN_NOTIFY_ADMIN_USERNAME', ''));

            $admin = User::query()
                ->where('ruolo', 0)
                ->when($adminId > 0, fn ($q) => $q->whereKey($adminId))
                ->when($adminId <= 0 && $adminUsernameEnv !== '', fn ($q) => $q->where('username', $adminUsernameEnv))
                ->when($adminId <= 0 && $adminUsernameEnv === '', fn ($q) => $q->where('username', 'scintilla'))
                ->first();

            $notifyEmail = trim((string) ($admin?->email ?? ''));
            if ($notifyEmail === '') {
                return;
            }

            $adminGreeting = trim((string) ($admin->username ?? 'scintilla'));
            $userUsername = trim((string) ($actor->username ?? ''));
            if ($userUsername === '') {
                $userUsername = trim((string) ($actor->nickname ?? 'utente'));
            }

            $title = trim((string) ($event->title ?? 'Evento'));
            $when = optional($event->date)->timezone(config('app.timezone'))->format('d/m/Y H:i');

            $subject = "un'ospite si è cancellato dall'evento {$title}";
            $body =
                "Ciao {$adminGreeting}, un amico di {$userUsername} si è appena cancellato dal tuo evento {$title} {$when}\n";

            Mail::raw($body, function ($message) use ($notifyEmail, $subject) {
                $message->to($notifyEmail)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning('Notify admin guest removed failed: ' . $e->getMessage());
        }
    }

    public static function notifyEventDeleted(Event $event, User $actor): void
    {
        try {
            $notifyEmail = self::resolveEmail();
            if ($notifyEmail === '') {
                return;
            }

            $username = trim((string) ($actor->username ?? ''));
            if ($username === '') {
                $username = trim((string) ($actor->nickname ?? 'utente'));
            }

            $title = trim((string) ($event->title ?? 'Evento'));
            $when = optional($event->date)->timezone(config('app.timezone'))->format('d/m/Y H:i');

            $subject = "cancellazione evento {$title}";
            $body = "{$username} ha cancellato l'evento {$title} in programma {$when}\n";

            Mail::raw($body, function ($message) use ($notifyEmail, $subject) {
                $message->to($notifyEmail)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning('Notify admin event deleted failed: ' . $e->getMessage());
        }
    }
}
