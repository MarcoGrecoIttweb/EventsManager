<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestController extends Controller
{
    public function addGuest(Event $event)
    {
        if (!Auth::check() || !Auth::user()->isApproved()) {
            return back()->with('error', 'Devi essere un utente approvato per gestire gli ospiti.');
        }

        $participation = $event->participants()->where('utente.userID', Auth::id())->first();
        if (!$participation) {
            return back()->with('error', 'Devi prima iscriverti all\'evento per portare ospiti.');
        }

        if (!$event->allow_guests) {
            return back()->with('error', 'Questo evento non permette di portare ospiti.');
        }

        $currentGuests = $participation->pivot->amici ?? 0;
        $maxGuests = $event->max_guests_per_user ?? 10;

        if ($currentGuests >= $maxGuests) {
            return back()->with('error', 'Hai raggiunto il limite massimo di ' . $maxGuests . ' ospiti per questo evento.');
        }

        if ($event->isFull()) {
            return back()->with('error', 'L\'evento è al completo, non puoi aggiungere altri ospiti.');
        }

        try {
            $event->participants()->updateExistingPivot(Auth::id(), [
                'amici' => $currentGuests + 1
            ]);

            return back()->with('success', 'Ospite aggiunto con successo!')
                ->with('scrollTo', 'participant-' . Auth::id());

        } catch (\Exception $e) {
            \Log::error('Errore aggiunta ospite: ' . $e->getMessage());
            return back()->with('error', 'Errore durante l\'aggiunta dell\'ospite.');
        }
    }

    public function removeGuest(Event $event)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $participation = $event->participants()->where('utente.userID', Auth::id())->first();
        if (!$participation) {
            return back()->with('error', 'Non sei iscritto a questo evento.');
        }

        $currentGuests = $participation->pivot->amici ?? 0;

        if ($currentGuests <= 0) {
            return back()->with('error', 'Non hai ospiti da rimuovere.');
        }

        try {
            $event->participants()->updateExistingPivot(Auth::id(), [
                'amici' => $currentGuests - 1
            ]);

            return back()->with('success', 'Ospite rimosso con successo!')
                ->with('scrollTo', 'participant-' . Auth::id());

        } catch (\Exception $e) {
            \Log::error('Errore rimozione ospite: ' . $e->getMessage());
            return back()->with('error', 'Errore durante la rimozione dell\'ospite.');
        }
    }
}
