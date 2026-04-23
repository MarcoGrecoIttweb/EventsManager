<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function myActiveEvents(Request $request)
    {
        $user = $request->user();

        $events = $user->participatingEvents()
            ->paginate(20)
            ->withQueryString();

        return view('profile.my-active-events', compact('events'));
    }

    public function show(Request $request, User $user)
    {
        $this->authorize('view', $user);

        $allParticipatedEvents = $user->events()
            ->where('pubblicato', 1)
            ->orderBy('dataevento', 'desc')
            ->get();

        $profileReturnUrl = $this->safeInternalReturnUrl($request->query('return'));

        return view('profile.show', compact('user', 'allParticipatedEvents', 'profileReturnUrl'));
    }

    /**
     * Evita open-redirect: solo stesso sito (root della richiesta) o path assoluto locale.
     */
    private function safeInternalReturnUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        $root = rtrim(request()->root(), '/');

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $urlNorm = rtrim($url, '/');
            $rootNorm = rtrim($root, '/');
            if ($urlNorm === $rootNorm || strpos($url, $root . '/') === 0) {
                return $url;
            }

            return null;
        }

        if (isset($url[0]) && $url[0] === '/' && strpos($url, '//') !== 0) {
            return $root . $url;
        }

        return null;
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $isAdmin = $request->user() && $request->user()->isAdmin();

        if ($isAdmin) {
            $validated = $request->validate([
                'username' => 'required|string|max:20|unique:utente,username,' . $user->getKey() . ',userID',
                'nome' => 'required|string|max:20',
                'cognome' => 'required|string|max:20',
                // Permetti email duplicate: requisito richiesto (non bloccare se già usata).
                'email' => 'required|email|max:60',
                'telefono' => 'nullable|string|max:30',
                'sesso' => 'required|in:m,f',
                'residenza' => 'nullable|string|max:30',
                'datanascita' => 'nullable|date',
                'description' => 'nullable|string|max:65535',
            ]);

            $user->update([
                'username' => $validated['username'],
                'nome' => $validated['nome'],
                'cognome' => $validated['cognome'],
                'email' => $validated['email'],
                'telefono' => $validated['telefono'] ?? null,
                'sesso' => $validated['sesso'],
                'residenza' => $validated['residenza'] ?? '',
                'datanascita' => $validated['datanascita'] ?? $user->datanascita,
                'descr' => $validated['description'] ?? '',
            ]);
        } else {
            $validated = $request->validate([
                // Permetti email duplicate: requisito richiesto (non bloccare se già usata).
                'email' => 'required|email|max:60',
                'telefono' => 'nullable|string|max:20',
            ]);

            $user->update([
                'email' => $validated['email'],
                'telefono' => $validated['telefono'] ?? null,
            ]);
        }

        return redirect()->route('profile.show', $user)
            ->with('success', 'Profilo aggiornato con successo!');
    }

    /**
     * Imposta la password di un utente (solo amministratori, da pagina profilo).
     * Allineato a AuthController: hash Laravel + MD5 legacy per il sito vecchio.
     */
    public function updatePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $plain = $request->input('password');
        $user->password = md5($plain);
        $user->password_laravel = Hash::make($plain);
        $user->save();

        return redirect()->route('profile.show', $user)
            ->with('success', 'Password aggiornata per l\'utente ' . ($user->username ?: '') . '.');
    }

    /**
     * Cambio password personale (solo proprietario profilo).
     */
    public function updateOwnPassword(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $plain = $request->input('password');
        $user->password = md5($plain);
        $user->password_laravel = Hash::make($plain);
        $user->save();

        return redirect()->route('profile.edit', $user)
            ->with('success', 'Password aggiornata.');
    }
}
