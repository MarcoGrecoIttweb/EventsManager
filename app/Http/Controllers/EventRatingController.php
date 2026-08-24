<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventRatingController extends Controller
{
    public function store(Request $request, Event $event)
    {
        $user = Auth::user();

        if (!$event->canBeRatedBy($user)) {
            return back()->with('error', 'Puoi votare solo eventi conclusi a cui hai partecipato.');
        }

        if ($event->ratings()->where('user_id', $user->getKey())->exists()) {
            return back()->with('error', 'Hai già votato questo evento. Il voto non può essere modificato.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $event->ratings()->create([
            'user_id' => $user->getKey(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return back()->with('success', 'Grazie per il tuo voto!');
    }

    /**
     * Modifica di un singolo voto: riservata all'amministratore.
     */
    public function update(Request $request, Event $event, EventRating $rating)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        if ((int) $rating->event_id !== (int) $event->getKey()) {
            abort(404);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $rating->update([
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return back()->with('success', 'Voto aggiornato.');
    }

    /**
     * Eliminazione di un singolo voto: riservata all'amministratore.
     */
    public function destroy(Event $event, EventRating $rating)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        if ((int) $rating->event_id !== (int) $event->getKey()) {
            abort(404);
        }

        $rating->delete();

        return back()->with('success', 'Voto eliminato.');
    }
}
