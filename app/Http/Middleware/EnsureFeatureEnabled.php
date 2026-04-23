<?php

namespace App\Http\Middleware;

use App\Support\SiteSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        // Gli admin possono sempre accedere (utile per verifiche/configurazione).
        if (Auth::check() && Auth::user()->isAdmin()) {
            return $next($request);
        }

        if (! SiteSettings::getBool('feature.' . $featureKey, true)) {
            if ($featureKey === 'chat_salottino' && \Route::has('chat.coming-soon')) {
                return redirect()->route('chat.coming-soon');
            }
            if ($featureKey === 'mercatino' && \Route::has('mercatino.coming-soon')) {
                return redirect()->route('mercatino.coming-soon');
            }
            return redirect()->route('home')->with('error', 'Questa sezione è momentaneamente disattivata dall’amministratore.');
        }

        return $next($request);
    }
}

