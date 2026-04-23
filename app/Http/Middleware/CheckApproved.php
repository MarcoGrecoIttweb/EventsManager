<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        // Se un admin sta impersonando un utente, permetti l’accesso anche se l’utente non è approvato
        // (serve per poter entrare nei profili sospesi/in attesa e gestirli).
        if ($request->session()->has('impersonator_id')) {
            return $next($request);
        }

        if (Auth::check() && !Auth::user()->isApproved()) {
            $user = Auth::user();
            $message = match (true) {
                $user->isAwaitingApproval() => 'Il tuo account è in attesa di approvazione.',
                $user->isSuspended() => 'Il tuo account è stato sospeso. Contatta un amministratore.',
                $user->isBanned() => 'Il tuo account è stato disattivato.',
                default => 'Non puoi accedere con questo account.',
            };
            Auth::logout();
            return redirect()->route('login')->with('error', $message);
        }

        return $next($request);
    }
}
