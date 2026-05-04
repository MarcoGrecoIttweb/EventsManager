<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImpersonationController extends Controller
{
    /**
     * Avvia l'impersonazione (solo admin).
     */
    public function start(Request $request, User $user)
    {
        $actor = Auth::user();
        if (!$actor || !$actor->isAdmin()) {
            return redirect()->route('home')->with('error', 'Accesso non autorizzato.');
        }

        if ($request->session()->has('impersonator_id')) {
            return redirect()->back()->with('error', 'Stai già impersonando un utente.');
        }

        if ((int) $user->getKey() === (int) $actor->getKey()) {
            return redirect()->back()->with('error', 'Non puoi impersonare te stesso.');
        }

        // Evita di impersonare altri admin: riduce rischi/confusione permessi
        if ($user->isAdmin()) {
            return redirect()->back()->with('error', 'Non puoi impersonare un altro amministratore.');
        }

        $request->session()->regenerate();

        $request->session()->put([
            'impersonator_id' => $actor->getKey(),
            'impersonator_username' => $actor->username,
            'impersonated_user_id' => $user->getKey(),
            'impersonated_username' => $user->username,
            'impersonated_at' => now()->toIso8601String(),
        ]);

        Auth::login($user);

        Log::info('Impersonation started', [
            'impersonator_id' => $actor->getKey(),
            'impersonated_user_id' => $user->getKey(),
            'ip' => $request->ip(),
        ]);

        return redirect()->route('profile.show', $user)->with('success', 'Ora stai impersonando ' . $user->username . '.');
    }

    /**
     * Termina l'impersonazione (accessibile anche mentre impersoni).
     */
    public function stop(Request $request)
    {
        $impersonatorId = $request->session()->get('impersonator_id');
        if (!$impersonatorId) {
            return redirect()->back()->with('error', 'Nessuna impersonazione attiva.');
        }

        $request->session()->forget([
            'impersonator_id',
            'impersonator_username',
            'impersonated_user_id',
            'impersonated_username',
            'impersonated_at',
        ]);

        Auth::loginUsingId($impersonatorId);
        $request->session()->regenerate();

        Log::info('Impersonation stopped', [
            'impersonator_id' => $impersonatorId,
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Impersonazione terminata: sei tornato come admin.');
    }
}

