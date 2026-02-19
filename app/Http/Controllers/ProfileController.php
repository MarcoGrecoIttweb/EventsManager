<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(User $user)
    {
        $this->authorize('view', $user);

        $upcomingEvents = $user->events()
            ->where('pubblicato', 1)
            ->where('dataevento', '>', now())
            ->orderBy('dataevento')
            ->get();

        $pastEvents = $user->events()
            ->where('pubblicato', 1)
            ->where('dataevento', '<=', now())
            ->orderBy('dataevento', 'desc')
            ->get();

        return view('profile.show', compact('user', 'upcomingEvents', 'pastEvents'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:20',
            'cognome' => 'required|string|max:20',
            'nickname' => 'required|string|max:20|unique:utente,username,' . $user->getKey() . ',userID',
            'email' => 'required|email|max:60|unique:utente,email,' . $user->getKey() . ',userID',
            'telefono' => 'nullable|string|max:20',
            'datanascita' => 'nullable|date|before:today',
            'residenza' => 'nullable|string|max:30',
            'sesso' => 'required|in:m,f',
            'mail_visibile' => 'sometimes|boolean',
            'description' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $updateData = [
            'nome' => $validated['name'],
            'cognome' => $validated['cognome'],
            'username' => $validated['nickname'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'] ?? null,
            'datanascita' => $validated['datanascita'] ?? null,
            'residenza' => $validated['residenza'] ?? null,
            'sesso' => $validated['sesso'],
            'mail_visibile' => $request->has('mail_visibile') ? 1 : 0,
            'descr' => $validated['description'] ?? null,
        ];

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $file->move(public_path('upload_avatar'), $filename);
            $updateData['avatar'] = $filename;
        }

        $user->update($updateData);

        return redirect()->route('profile.show', $user)
            ->with('success', 'Profilo aggiornato con successo!');
    }
}
