<?php

namespace App\Http\Controllers;

use App\Models\Event;
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
}
