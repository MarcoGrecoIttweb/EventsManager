<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventWaitlistEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Admin\HomePendingBannerController;
use App\Models\User;
use App\Models\UserLoginEvent;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with(['user', 'participants'])
            ->active()
            ->upcoming()
            ->ordered()
            ->paginate(12);

        $waitlistedEventIds = [];
        $waitlistByEventId = [];
        if (Auth::check() && Auth::user()->isApproved()) {
            $eventIds = $events->getCollection()->pluck('IDevento')->filter()->values();
            if ($eventIds->count() > 0) {
                $waitlistedEventIds = EventWaitlistEntry::query()
                    ->where('user_id', Auth::id())
                    ->whereIn('event_id', $eventIds->all())
                    ->pluck('event_id')
                    ->all();

                $waitlistByEventId = EventWaitlistEntry::query()
                    ->with(['user'])
                    ->whereIn('event_id', $eventIds->all())
                    ->orderBy('created_at')
                    ->get()
                    ->groupBy('event_id')
                    ->all();
            }
        }

        // "Utenti attivi" = utenti approvati/abilitati (non admin)
        $activeUsersCount = User::query()
            ->nonAdmin()
            ->where('abilitato', 1)
            ->count();

        // Nel progetto esiste già il tracciamento accessi giornalieri via tabella user_login_events.
        // Lo usiamo come "Visite odierne" (accessi di oggi).
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        $todayVisitsCount = UserLoginEvent::query()
            ->whereBetween('logged_in_at', [$todayStart, $todayEnd])
            ->count();

        $visitVsActivePct = $activeUsersCount > 0
            ? round(($todayVisitsCount / $activeUsersCount) * 100, 2)
            : 0;

        $adminPendingRegistrationBanner = null;
        if (Auth::check() && Auth::user()->isAdmin()) {
            $dismissed = session(HomePendingBannerController::SESSION_DISMISSED_IDS, []);
            if (!is_array($dismissed)) {
                $dismissed = [];
            }
            $dismissed = array_map('intval', $dismissed);
            $pendingForBanner = User::nonAdmin()
                ->where('abilitato', 3)
                ->when(count($dismissed) > 0, static function ($q) use ($dismissed) {
                    $q->whereNotIn('userID', $dismissed);
                })
                ->orderByDesc('userID')
                ->get(['userID', 'username']);
            if ($pendingForBanner->isNotEmpty()) {
                $latest = $pendingForBanner->first();
                $adminPendingRegistrationBanner = [
                    'count' => $pendingForBanner->count(),
                    'latest_username' => (string) ($latest?->username ?? ''),
                    'dismiss_user_ids' => $pendingForBanner->pluck('userID')->implode(','),
                ];
            }
        }

        return view('events.index', compact(
            'events',
            'waitlistedEventIds',
            'waitlistByEventId',
            'activeUsersCount',
            'todayVisitsCount',
            'visitVsActivePct',
            'adminPendingRegistrationBanner'
        ));
    }

    public function pastEvents()
    {
        $events = Event::with(['user', 'participants'])
            ->active()
            ->past()
            ->ordered('desc')
            ->paginate(12);

        return view('events.past', compact('events'));
    }

    public function show(Event $event)
    {
        // Gli admin possono vedere anche eventi disattivati dalla lista gestione eventi.
        if (!$event->is_active && (!Auth::check() || !Auth::user()->isAdmin())) {
            abort(404);
        }

        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Devi essere loggato per vedere i dettagli dell\'evento.');
        }

        $userParticipating = false;
        $comments = collect();
        $isWaitlisted = false;
        $waitlistCount = 0;
        $waitlistEntries = collect();

        // Waitlist: visibile nella pagina evento (se presente), anche se l'utente non è ancora approvato.
        $waitlistEntries = EventWaitlistEntry::query()
            ->with(['user'])
            ->where('event_id', $event->getKey())
            ->orderBy('created_at')
            ->get();
        $waitlistCount = $waitlistEntries->count();

        if (Auth::check() && Auth::user()->isApproved()) {
            $userParticipating = $event->participants()
                ->where('utente.userID', Auth::id())
                ->exists();

            $comments = $event->comments()
                ->with('user')
                ->latest('data')
                ->get();

            $isWaitlisted = EventWaitlistEntry::query()
                ->where('event_id', $event->getKey())
                ->where('user_id', Auth::id())
                ->exists();
        }

        return view('events.show', compact('event', 'userParticipating', 'comments', 'isWaitlisted', 'waitlistCount', 'waitlistEntries'));
    }

    public function participate(Event $event)
    {
        if (!Auth::check() || !Auth::user()->isApproved()) {
            return redirect()->route('login')
                ->with('error', 'Devi essere un utente approvato per partecipare agli eventi');
        }

        if (!$event->isRegistrationOpen()) {
            return back()->with('error', 'Le iscrizioni a questo evento sono chiuse.');
        }

        if ($event->isFull()) {
            return back()->with('error', 'Evento al completo');
        }

        if ($event->participants()->where('utente.userID', Auth::id())->exists()) {
            return back()->with('error', 'Sei già iscritto a questo evento');
        }

        Auth::user()->events()->attach($event->getKey(), ['amici' => 0]);

        $this->notifyAdminsEventSubscriptionChange($event, Auth::user(), 'iscritto');

        return back()->with('success', 'Iscrizione effettuata con successo');
    }

    public function cancelParticipation(Event $event)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        Auth::user()->events()->detach($event->getKey());

        $this->notifyAdminsEventSubscriptionChange($event, Auth::user(), 'cancellato');

        $this->notifyNextFromWaitlistIfAny($event);

        return back()->with('success', 'Iscrizione annullata con successo');
    }

    public function joinWaitlist(Event $event)
    {
        if (!Auth::check() || !Auth::user()->isApproved()) {
            return redirect()->route('login')
                ->with('error', 'Devi essere un utente approvato per iscriverti alla lista d’attesa.');
        }

        $alreadyParticipant = $event->participants()->where('utente.userID', Auth::id())->exists();
        if ($alreadyParticipant) {
            return back()->with([
                'error' => 'Sei già iscritto a questo evento.',
                'waitlist_flash_event_id' => $event->getKey(),
            ]);
        }

        $alreadyWaitlisted = EventWaitlistEntry::query()
            ->where('event_id', $event->getKey())
            ->where('user_id', Auth::id())
            ->exists();

        if ($alreadyWaitlisted) {
            return back()->with([
                'success' => 'Sei già nella lista d’attesa.',
                'waitlist_flash_event_id' => $event->getKey(),
            ]);
        }

        EventWaitlistEntry::query()->create([
            'event_id' => $event->getKey(),
            'user_id' => Auth::id(),
            'email' => Auth::user()->email ?? null,
            'display_name' => Auth::user()->nickname ?? trim((Auth::user()->nome ?? '') . ' ' . (Auth::user()->cognome ?? '')) ?: null,
            'status' => 'pending',
            'notified_at' => null,
        ]);

        return back()->with([
            'success' => 'Perfetto: ti ho messo in lista d’attesa. Se si libera un posto, ti avvisiamo via email.',
            'waitlist_flash_event_id' => $event->getKey(),
        ]);
    }

    public function leaveWaitlist(Event $event)
    {
        if (!Auth::check() || !Auth::user()->isApproved()) {
            return redirect()->route('login');
        }

        EventWaitlistEntry::query()
            ->where('event_id', $event->getKey())
            ->where('user_id', Auth::id())
            ->delete();

        return back()->with([
            'success' => 'Ok: sei stato rimosso dalla lista d’attesa.',
            'waitlist_flash_event_id' => $event->getKey(),
        ]);
    }

    private function notifyAdminsEventSubscriptionChange(Event $event, User $actor, string $action): void
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

            $notifyEmail = trim((string) ($admin?->email ?? ''));
            if ($notifyEmail === '') {
                return;
            }

            $eventUrl = route('events.show', $event);
            $when = optional($event->date)->timezone(config('app.timezone'))->format('d/m/Y H:i');
            $actorLabel = trim(($actor->nome ?? '') . ' ' . ($actor->cognome ?? ''));
            if ($actorLabel === '') {
                $actorLabel = $actor->nickname ?? ('ID ' . $actor->getKey());
            } else {
                $actorLabel .= ' (' . ($actor->nickname ?? $actor->getKey()) . ')';
            }

            $subject = "Excursio - Evento: utente {$action}";
            $body =
                "Notifica iscrizioni evento\n\n" .
                "Utente: {$actorLabel}\n" .
                "Azione: {$action}\n" .
                "Evento: {$event->title}\n" .
                "Quando: {$when}\n" .
                "Link evento: {$eventUrl}\n";

            Mail::raw($body, function ($message) use ($notifyEmail, $subject) {
                $message->to($notifyEmail)->subject($subject);
            });
        } catch (\Throwable $e) {
            // Non bloccare il flusso di iscrizione/cancellazione se la mail fallisce.
            \Log::warning('Notify admins subscription change failed: ' . $e->getMessage());
        }
    }

    private function notifyNextFromWaitlistIfAny(Event $event): void
    {
        try {
            if (!$event->isRegistrationOpen()) {
                return;
            }
            if ($event->isFull()) {
                return;
            }

            $entry = EventWaitlistEntry::query()
                ->where('event_id', $event->getKey())
                ->where('status', 'pending')
                ->orderBy('created_at')
                ->first();

            if (!$entry) {
                return;
            }

            $user = User::query()->whereKey($entry->user_id)->first();
            $to = trim((string) ($user?->email ?? $entry->email ?? ''));
            if ($to === '') {
                return;
            }

            $eventUrl = route('events.show', $event);
            $when = optional($event->date)->timezone(config('app.timezone'))->format('d/m/Y H:i');
            $subject = 'Excursio - Si è liberato un posto!';
            $body =
                "Ciao!\n\n" .
                "Buone notizie: si è liberato un posto per questo evento:\n" .
                "{$event->title}\n" .
                "Quando: {$when}\n\n" .
                "Vai alla pagina evento e iscriviti appena puoi:\n" .
                "{$eventUrl}\n\n" .
                "A presto,\nExcursio\n";

            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });

            $entry->status = 'notified';
            $entry->notified_at = now();
            $entry->save();
        } catch (\Throwable $e) {
            \Log::warning('Notify waitlist failed: ' . $e->getMessage());
        }
    }

    public function printParticipants(Event $event)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $participants = $event->participants()
            ->orderBy('cognome')
            ->orderBy('nome')
            ->get();

        return view('events.print', compact('event', 'participants'));
    }
}
