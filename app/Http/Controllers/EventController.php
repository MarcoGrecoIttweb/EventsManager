<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // AGGIUNGI QUESTA RIGA

class EventController extends Controller
{
    /**
     * Display a listing of the events.
     */
// Nel metodo index() (eventi futuri)
    public function index()
    {
        $events = Event::with(['user', 'participants'])
            ->where('is_active', true)
            ->where('date', '>', now())
            ->orderBy('date')
            ->paginate(12);

        // Titolo per l'hero
        $heroTitle = 'Prossimi Eventi';
        $heroSubtitle = 'Scopri i tornei in programma';

        return view('events.index', compact('events', 'heroTitle', 'heroSubtitle'));
    }

// Nel metodo pastEvents()
    public function pastEvents()
    {
        $events = Event::with(['user', 'participants'])
            ->where('is_active', true)
            ->where('date', '<=', now())
            ->orderBy('date', 'desc')
            ->paginate(12);

        $heroTitle = 'Eventi Passati';
        $heroSubtitle = 'Rivedi i tornei conclusi';

        return view('events.past', compact('events', 'heroTitle', 'heroSubtitle'));
    }

// Nel metodo show()
    public function show(Event $event)
    {
        if (!$event->is_active) {
            abort(404);
        }

        $userParticipating = false;
        $comments = collect();

        if (Auth::check() && Auth::user()->isApproved()) {
            $userParticipating = Auth::user()->events()
                ->where('event_id', $event->id)
                ->exists();

            $comments = $event->comments()
                ->with('user')
                ->latest()
                ->get();
        }

        // Titolo per l'hero specifico dell'evento
        $heroTitle = $event->title;
        $heroSubtitle = $event->date->format('d/m/Y H:i') . ' • ' . $event->city;

        return view('events.show',
            compact('event', 'userParticipating', 'comments', 'heroTitle', 'heroSubtitle')
        );
    }


    /**
     * Participate in an event.
     */
    public function participate(Event $event)
    {
        if (!Auth::check() || !Auth::user()->isApproved()) {
            return redirect()->route('login')
                ->with('error', 'Devi essere un utente approvato per partecipare agli eventi');
        }

        if ($event->isFull()) {
            return back()->with('error', 'Evento al completo');
        }

        if (Auth::user()->events()->where('event_id', $event->id)->exists()) {
            return back()->with('error', 'Sei già iscritto a questo evento');
        }

        Auth::user()->events()->attach($event->id);

        return back()->with('success', 'Iscrizione effettuata con successo');
    }

    /**
     * Cancel participation in an event.
     */
    public function cancelParticipation(Event $event)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        Auth::user()->events()->detach($event->id);

        return back()->with('success', 'Iscrizione annullata con successo');
    }
}
