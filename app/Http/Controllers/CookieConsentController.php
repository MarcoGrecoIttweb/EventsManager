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
            'action' => ['required', 'in:accept,reject,save'],
        ]);

        $action = $validated['action'];

        $status = 'rejected';
        $categories = [CookieConsent::CAT_NECESSARY];

        if ($action === 'accept') {
            $status = 'accepted';
            $categories = CookieConsent::normalizeCategories([
                CookieConsent::CAT_NECESSARY,
                CookieConsent::CAT_EXTERNAL_MEDIA,
            ]);
        } elseif ($action === 'reject') {
            $status = 'rejected';
            $categories = [CookieConsent::CAT_NECESSARY];
        } elseif ($action === 'save') {
            $status = 'accepted';
            $cats = $request->input('categories', []);
            if (!is_array($cats)) {
                $cats = [];
            }
            $categories = CookieConsent::normalizeCategories($cats);
            // If only necessary is selected, treat as rejected for UX parity.
            if ($categories === [CookieConsent::CAT_NECESSARY]) {
                $status = 'rejected';
            }
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

