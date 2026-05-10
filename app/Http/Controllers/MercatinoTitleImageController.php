<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MercatinoTitleImageController extends Controller
{
    /**
     * Percorso pubblico relativo (es. upload_immagini/mercatino_title.webp) o null se assente.
     */
    public static function titleImagePublicRelative(): ?string
    {
        $basePath = public_path('upload_immagini');
        if (! is_dir($basePath)) {
            return null;
        }
        $matches = glob($basePath . DIRECTORY_SEPARATOR . 'mercatino_title.*');
        if (! $matches || count($matches) === 0) {
            return null;
        }
        usort($matches, function ($a, $b) {
            $ta = @filemtime($a) ?: 0;
            $tb = @filemtime($b) ?: 0;

            return $tb <=> $ta;
        });

        return 'upload_immagini/' . basename($matches[0]);
    }

    public function update(Request $request)
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'title_image' => 'required|file|max:10240',
        ]);

        $file = $request->file('title_image');
        $clientExt = strtolower((string) ($file->getClientOriginalExtension() ?: ''));
        if ($clientExt === 'jpeg') {
            $clientExt = 'jpg';
        }
        $allowedExt = ['webp', 'png', 'gif', 'jpg'];
        if (! in_array($clientExt, $allowedExt, true)) {
            return redirect()->route('mercatino.index')
                ->with('error', 'Formato file non ammesso. Usa JPG, PNG, GIF o WebP.');
        }

        $dest = public_path('upload_immagini');
        if (! is_dir($dest)) {
            @mkdir($dest, 0755, true);
        }

        foreach (glob($dest . DIRECTORY_SEPARATOR . 'mercatino_title.*') ?: [] as $oldPath) {
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $filename = 'mercatino_title.' . $clientExt;
        $file->move($dest, $filename);

        return redirect()->route('mercatino.index')
            ->with('success', 'Immagine accanto al titolo Mercatino aggiornata.');
    }
}
