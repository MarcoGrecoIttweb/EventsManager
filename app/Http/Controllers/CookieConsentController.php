<?php

namespace App\Http\Controllers;

use App\Models\CookieConsentLog;
use App\Support\CookieConsent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class CookieConsentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:accept,reject'],
        ]);

        $status = $validated['action'] === 'accept' ? 'accepted' : 'rejected';

        // Per ora: categorie minime.
        // "necessary" è implicita; "third_party" viene abilitata solo con "accept".
        $categories = ['necessary'];
        if ($status === 'accepted') {
            $categories[] = 'third_party';
        }

        $payload = [
            'status' => $status,
            'categories' => $categories,
            'updated_at' => now()->toIso8601String(),
        ];

        // Log nel DB solo se utente autenticato (opzionale, come richiesto).
        if (Auth::check()) {
            CookieConsentLog::create([
                'user_id' => Auth::id(),
                'consent' => $payload,
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);
        }

        // Cookie tecnico: HTTP-only per evitare manipolazioni lato JS.
        $cookie = Cookie::make(
            CookieConsent::COOKIE_NAME,
            json_encode($payload, JSON_UNESCAPED_UNICODE),
            CookieConsent::COOKIE_MAX_MINUTES,
            '/',
            null,
            $request->isSecure(),
            true, // httpOnly
            false,
            'Lax'
        );

        $redirectTo = $request->input('redirect', url()->previous() ?: route('home'));

        return redirect()->to($redirectTo)->withCookie($cookie);
    }
}

