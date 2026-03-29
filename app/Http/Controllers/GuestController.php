<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Support\OspitiGuestStore;
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

        $currentGuests = (int) ($participation->pivot->amici ?? 0);
        $maxGuests = $event->max_guests_per_user ?? 10;

        if ($currentGuests >= $maxGuests) {
            return back()->with('error', 'Hai raggiunto il limite massimo di ' . $maxGuests . ' ospiti per questo evento.');
        }

        if ($event->isFull()) {
            return back()->with('error', 'L\'evento è al completo, non puoi aggiungere altri ospiti.');
        }

        try {
            $entries = OspitiGuestStore::decode($participation->pivot->ospiti_inseriti_il ?? null);
            $entries = OspitiGuestStore::normalizeToCount($entries, $currentGuests, $participation->pivot);
            $entries[] = [
                'at' => now()->format('Y-m-d H:i:s'),
                'nome' => '',
            ];

            $event->participants()->updateExistingPivot(Auth::id(), [
                'amici' => $currentGuests + 1,
                'ospiti_inseriti_il' => OspitiGuestStore::encode($entries),
            ]);

            return back()->with('success', 'Aggiunta una riga amico: inserisci il nome nel campo sotto.')
                ->with('scrollTo', 'participant-' . Auth::id());
        } catch (\Exception $e) {
            \Log::error('Errore aggiunta ospite: ' . $e->getMessage());

            return back()->with('error', 'Errore durante l\'aggiunta dell\'ospite.');
        }
    }

    public function removeGuest(Request $request, Event $event)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'guest_index' => 'required|integer|min:0',
        ]);

        $participation = $event->participants()->where('utente.userID', Auth::id())->first();
        if (!$participation) {
            return back()->with('error', 'Non sei iscritto a questo evento.');
        }

        $currentGuests = (int) ($participation->pivot->amici ?? 0);
        $idx = (int) $validated['guest_index'];

        if ($currentGuests <= 0) {
            return back()->with('error', 'Non hai ospiti da rimuovere.');
        }

        if ($idx >= $currentGuests) {
            return back()->with('error', 'Selezione amico non valida.');
        }

        try {
            $entries = OspitiGuestStore::decode($participation->pivot->ospiti_inseriti_il ?? null);
            $entries = OspitiGuestStore::normalizeToCount($entries, $currentGuests, $participation->pivot);
            array_splice($entries, $idx, 1);

            $event->participants()->updateExistingPivot(Auth::id(), [
                'amici' => $currentGuests - 1,
                'ospiti_inseriti_il' => OspitiGuestStore::encode($entries),
            ]);

            return back()->with('success', 'Amico rimosso dall\'elenco.')
                ->with('scrollTo', 'participant-' . Auth::id());
        } catch (\Exception $e) {
            \Log::error('Errore rimozione ospite: ' . $e->getMessage());

            return back()->with('error', 'Errore durante la rimozione dell\'ospite.');
        }
    }

    public function updateGuestName(Request $request, Event $event)
    {
        if (!Auth::check() || !Auth::user()->isApproved()) {
            return back()->with('error', 'Non autorizzato.');
        }

        $validated = $request->validate([
            'guest_index' => 'required|integer|min:0',
            'nome' => 'required|string|min:2|max:120',
        ]);

        $participation = $event->participants()->where('utente.userID', Auth::id())->first();
        if (!$participation) {
            return back()->with('error', 'Non sei iscritto a questo evento.');
        }

        $amici = (int) ($participation->pivot->amici ?? 0);
        if ($validated['guest_index'] >= $amici) {
            return back()->with('error', 'Indice ospite non valido.');
        }

        try {
            $entries = OspitiGuestStore::decode($participation->pivot->ospiti_inseriti_il ?? null);
            $entries = OspitiGuestStore::normalizeToCount($entries, $amici, $participation->pivot);
            $entries[$validated['guest_index']]['nome'] = trim((string) ($validated['nome'] ?? ''));

            $event->participants()->updateExistingPivot(Auth::id(), [
                'ospiti_inseriti_il' => OspitiGuestStore::encode($entries),
            ]);

            return back()->with('success', 'Nome amico aggiornato.')
                ->with('scrollTo', 'participant-' . Auth::id());
        } catch (\Exception $e) {
            \Log::error('Errore aggiornamento nome ospite: ' . $e->getMessage());

            return back()->with('error', 'Errore durante il salvataggio.');
        }
    }
}
