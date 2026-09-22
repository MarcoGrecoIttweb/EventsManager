<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteSettingsController extends Controller
{
    public function toggleFeature(Request $request, string $featureKey): RedirectResponse
    {
        $allowed = ['mercatino', 'chat_salottino', 'albums_foto', 'chat_reply_email_users'];
        if (! in_array($featureKey, $allowed, true)) {
            return redirect()->route('admin.dashboard')->with('error', 'Impostazione non valida.');
        }

        $new = SiteSettings::toggleBool('feature.' . $featureKey, true);

        $label = match ($featureKey) {
            'mercatino' => 'Mercatino',
            'chat_salottino' => 'Salottino chat',
            'albums_foto' => 'Album foto',
            'chat_reply_email_users' => 'Email risposta chat (utenti)',
            default => 'Funzione',
        };

        return redirect()
            ->route('admin.dashboard')
            ->with('success', $label . ': ' . ($new ? 'attivato' : 'nascosto') . '.');
    }

    public function updateAnnouncement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'message' => 'nullable|string|max:2000',
        ]);

        $message = trim((string) ($validated['message'] ?? ''));

        SiteSettings::set('site.announcement_message', $message);
        // Attivo automaticamente se c'è del testo, disattivo se il campo è vuoto:
        // niente interruttore separato da dimenticare di spuntare.
        SiteSettings::set('site.announcement_enabled', $message !== '');

        $successMessage = $message !== ''
            ? 'Messaggio salvato e attivo: verrà mostrato agli utenti al prossimo login.'
            : 'Messaggio rimosso: non verrà più mostrato.';

        return redirect()->route('home')->with('success', $successMessage);
    }
}
