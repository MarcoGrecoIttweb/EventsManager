<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestController extends Controller
{
    /**
     * Aggiungi un ospite all'evento.
     */
    public function addGuest(Event $event)
    {
        if (!Auth::check() || !Auth::user()->isApproved()) {
            return back()->with('error', 'Devi essere un utente approvato per gestire gli ospiti.');
        }

        // Verifica che l'utente sia iscritto all'evento
        $participation = $event->participants()->where('user_id', Auth::id())->first();
        if (!$participation) {
            return back()->with('error', 'Devi prima iscriverti all\'evento per portare ospiti.');
        }

        // Verifica che l'evento permetta ospiti
        if (!$event->allow_guests) {
            return back()->with('error', 'Questo evento non permette di portare ospiti.');
        }

        // Verifica il limite di ospiti
        if ($participation->pivot->guests_count >= $event->max_guests_per_user) {
            return back()->with('error', 'Hai raggiunto il limite massimo di ' . $event->max_guests_per_user . ' ospiti per questo evento.');
        }

        // Verifica che l'evento non sia al completo
        if ($event->isFull()) {
            return back()->with('error', 'L\'evento è al completo, non puoi aggiungere altri ospiti.');
        }

        try {
            // Incrementa il contatore ospiti
            $event->participants()->updateExistingPivot(Auth::id(), [
                'guests_count' => $participation->pivot->guests_count + 1
            ]);

            return back()->with('success', 'Ospite aggiunto con successo!')
                ->with('scrollTo', 'participant-' . Auth::id());

        } catch (\Exception $e) {
            \Log::error('Errore aggiunta ospite: ' . $e->getMessage());
            return back()->with('error', 'Errore durante l\'aggiunta dell\'ospite.');
        }
    }

    /**
     * Rimuovi un ospite dall'evento.
     */
    public function removeGuest(Event $event)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $participation = $event->participants()->where('user_id', Auth::id())->first();
        if (!$participation) {
            return back()->with('error', 'Non sei iscritto a questo evento.');
        }

        if ($participation->pivot->guests_count <= 0) {
            return back()->with('error', 'Non hai ospiti da rimuovere.');
        }

        try {
            // Decrementa il contatore ospiti
            $event->participants()->updateExistingPivot(Auth::id(), [
                'guests_count' => $participation->pivot->guests_count - 1
            ]);

            return back()->with('success', 'Ospite rimosso con successo!')
                ->with('scrollTo', 'participant-' . Auth::id());

        } catch (\Exception $e) {
            \Log::error('Errore rimozione ospite: ' . $e->getMessage());
            return back()->with('error', 'Errore durante la rimozione dell\'ospite.');
        }
    }
}
