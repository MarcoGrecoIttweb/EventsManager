<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CkeditorUploadController extends Controller
{
    public function upload(Request $request)
    {
        // CKEditor 4 expects CKEditorFuncNum to be echoed back in a JS callback.
        $funcNum = (string) ($request->input('CKEditorFuncNum') ?? $request->query('CKEditorFuncNum') ?? '');
        $funcNum = preg_replace('/\D+/', '', $funcNum) ?? '';

        $request->validate([
            // CKEditor sends the file in "upload"
            'upload' => ['required', 'image', 'max:10240', 'mimes:jpeg,jpg,png,webp,gif'],
        ], [
            'upload.required' => 'Seleziona un file immagine.',
            'upload.image' => 'Il file deve essere un’immagine valida.',
            'upload.max' => 'L’immagine non può superare 10MB.',
            'upload.mimes' => 'Formati ammessi: JPEG, PNG, WebP, GIF.',
        ]);

        $file = $request->file('upload');
        if (!$file || !$file->isValid()) {
            return $this->respond($funcNum, '', 'Upload non riuscito.', false);
        }

        $dest = public_path('upload_immagini');
        if (!is_dir($dest)) {
            @mkdir($dest, 0755, true);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'jpg';
        $filename = 'ck_' . now()->format('Ymd_His_u') . '_' . Str::lower(Str::random(8)) . '.' . $ext;

        $file->move($dest, $filename);

        // Usa url() (request-aware) così funziona anche se l'app gira sotto /excursio/public su XAMPP.
        $url = url('upload_immagini/' . $filename);

        return $this->respond($funcNum, $url, '', true);
    }

    /**
     * CKEditor 4 supports two common response formats:
     * - JS callback: CKEDITOR.tools.callFunction(CKEditorFuncNum, url, message)
     * - JSON: { uploaded: 1, fileName: "...", url: "..." }
     *
     * Some dialogs (Image->Upload) can require JSON depending on config/plugins.
     */
    private function respond(string $funcNum, string $url, string $message, bool $ok)
    {
        // If CKEditorFuncNum is present, always use callback (classic filebrowser upload).
        if ($funcNum !== '') {
            $funcNumJs = json_encode($funcNum, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $urlJs = json_encode($url, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $messageJs = json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $html = '<script>window.parent.CKEDITOR.tools.callFunction(' . $funcNumJs . ', ' . $urlJs . ', ' . $messageJs . ');</script>';
            return response($html, 200)->header('Content-Type', 'text/html; charset=utf-8');
        }

        // Otherwise return JSON (some CKEditor upload flows require this).
        if ($ok) {
            return response()->json([
                'uploaded' => 1,
                'fileName' => basename(parse_url($url, PHP_URL_PATH) ?: 'image'),
                'url' => $url,
            ]);
        }

        return response()->json([
            'uploaded' => 0,
            'error' => ['message' => $message !== '' ? $message : 'Upload non riuscito.'],
        ], 422);
    }
}

