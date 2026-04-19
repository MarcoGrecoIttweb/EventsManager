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
