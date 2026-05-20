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
            $notifyEmail = self::resolveEmail();
            if ($notifyEmail === '') {
                return;
            }

            $nickname = trim((string) ($actor->nickname ?? ''));
            if ($nickname === '') {
                $nickname = 'ID ' . $actor->getKey();
            }

            $eventUrl = route('events.show', $event);
            $when = optional($event->date)->timezone(config('app.timezone'))->format('d/m/Y H:i');

            $subject = 'Excursio - Evento: ospite aggiunto';
            $body =
                "Notifica ospiti evento\n\n" .
                "{$nickname} ha invitato un amico dall'evento\n\n" .
                "Evento: {$event->title}\n" .
                "Quando: {$when}\n" .
                "Link evento: {$eventUrl}\n";

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
            $notifyEmail = self::resolveEmail();
            if ($notifyEmail === '') {
                return;
            }

            $nickname = trim((string) ($actor->nickname ?? ''));
            if ($nickname === '') {
                $nickname = 'ID ' . $actor->getKey();
            }

            $eventUrl = route('events.show', $event);
            $when = optional($event->date)->timezone(config('app.timezone'))->format('d/m/Y H:i');

            $subject = 'Excursio - Evento: ospite rimosso';
            $body =
                "Notifica ospiti evento\n\n" .
                "{$nickname} ha rimosso un amico dall'evento\n\n" .
                "Evento: {$event->title}\n" .
                "Quando: {$when}\n" .
                "Link evento: {$eventUrl}\n";

            Mail::raw($body, function ($message) use ($notifyEmail, $subject) {
                $message->to($notifyEmail)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning('Notify admin guest removed failed: ' . $e->getMessage());
        }
    }
}
