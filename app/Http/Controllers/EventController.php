<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with(['user', 'participants'])
            ->active()
            ->upcoming()
            ->ordered()
            ->paginate(12);

        return view('events.index', compact('events'));
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

        return back()->with('success', 'Iscrizione effettuata con successo');
    }

    public function cancelParticipation(Event $event)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        Auth::user()->events()->detach($event->getKey());

        return back()->with('success', 'Iscrizione annullata con successo');
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
