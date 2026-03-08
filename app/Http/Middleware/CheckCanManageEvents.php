<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckCanManageEvents
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || !Auth::user()->canManageEvents()) {
            abort(403, 'Accesso non autorizzato.');
        }

        return $next($request);
    }
}
