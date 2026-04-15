<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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

        return view('events.index', compact(
            'events',
            'activeUsersCount',
            'todayVisitsCount',
            'visitVsActivePct'
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

        if (Auth::check() && Auth::user()->isApproved()) {
            $userParticipating = $event->participants()
                ->where('utente.userID', Auth::id())
                ->exists();

            $comments = $event->comments()
                ->with('user')
                ->latest('data')
                ->get();
        }

        return view('events.show', compact('event', 'userParticipating', 'comments'));
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

        return back()->with('success', 'Iscrizione annullata con successo');
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
