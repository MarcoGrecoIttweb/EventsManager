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
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Il tuo account è in attesa di approvazione.');
        }

        return $next($request);
    }
}
