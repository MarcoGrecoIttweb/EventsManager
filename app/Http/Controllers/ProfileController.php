<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the specified user profile.
     */
    /**
     * Display the specified user profile.
     */
    public function show(User $user)
    {
        // Autorizzazione tramite policy
        $this->authorize('view', $user);

        // Carica gli eventi a cui l'utente partecipa (PROSSIMI EVENTI)
        $upcomingEvents = $user->events()
            ->where('is_active', true)
            ->where('date', '>', now())
            ->orderBy('date')
            ->get();

        // Carica gli eventi passati a cui l'utente ha partecipato
        $pastEvents = $user->events()
            ->where('is_active', true)
            ->where('date', '<=', now())
            ->orderBy('date', 'desc')
            ->get();

        return view('profile.show', compact('user', 'upcomingEvents', 'pastEvents'));
    }

    /**
     * Show the form for editing the user profile.
     */
    public function edit(User $user)
    {
        // Autorizzazione tramite policy
        $this->authorize('update', $user);

        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user profile.
     */
    public function update(Request $request, User $user)
    {
        // Autorizzazione tramite policy
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'required|string|max:255|unique:users,nickname,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'bio' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Gestisci l'upload della foto
        if ($request->hasFile('photo')) {
            // Elimina la vecchia foto se esiste
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            // Salva la nuova foto
            $path = $request->file('photo')->store('profiles', 'public');
            $validated['photo'] = $path;
        }

        $user->update($validated);

        return redirect()->route('profile.show', $user)
            ->with('success', 'Profilo aggiornato con successo!');
    }
}
