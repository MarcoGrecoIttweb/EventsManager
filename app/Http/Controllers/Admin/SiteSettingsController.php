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
        $allowed = ['mercatino', 'chat_salottino', 'albums_foto'];
        if (! in_array($featureKey, $allowed, true)) {
            return redirect()->route('admin.dashboard')->with('error', 'Impostazione non valida.');
        }

        $new = SiteSettings::toggleBool('feature.' . $featureKey, true);

        $label = match ($featureKey) {
            'mercatino' => 'Mercatino',
            'chat_salottino' => 'Salottino chat',
            'albums_foto' => 'Album foto',
            default => 'Funzione',
        };

        return redirect()
            ->route('admin.dashboard')
            ->with('success', $label . ': ' . ($new ? 'attivato' : 'nascosto') . '.');
    }
}

