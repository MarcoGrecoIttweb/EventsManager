<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\PasswordResetController;

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Event;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\CookieConsentController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\CkeditorUploadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


// Route pubbliche
Route::get('/', [EventController::class, 'index'])->name('home');

// Cookie consent (custom)
Route::post('/cookie/consent', [CookieConsentController::class, 'store'])->name('cookie.consent.store');

// Legal pages
Route::get('/privacy-policy', function () {
    return view('legal.privacy-policy');
})->name('legal.privacy');

Route::get('/cookie-policy', function () {
    return view('legal.cookie-policy');
})->name('legal.cookie');
// Se il progetto viene aperto come /excursio/public/ (xampp senza vhost), reindirizza alla home corretta
Route::get('/public', function () {
    return redirect()->route('home');
});
Route::get('/public/', function () {
    return redirect()->route('home');
});
Route::get('/public/index.php', function () {
    return redirect()->route('home');
});
// Compatibilità: alcuni link puntano ancora a /home
Route::get('/home', function () {
    return redirect()->route('home');
});
Route::get('/events', [EventController::class, 'index'])->name('events.index');

Route::get('/events/past', [EventController::class, 'pastEvents'])
    ->name('events.past')
    ->middleware('auth');

Route::get('/organizzatore/richiesta', function () {
    $adminNotifyEmail = '';
    try {
        $adminId = (int) env('ADMIN_NOTIFY_ADMIN_ID', 0);
        $adminUsername = trim((string) env('ADMIN_NOTIFY_ADMIN_USERNAME', ''));

        $admin = User::query()
            ->where('ruolo', 0)
            ->when($adminId > 0, fn ($q) => $q->whereKey($adminId))
            ->when($adminId <= 0 && $adminUsername !== '', fn ($q) => $q->where('username', $adminUsername))
            ->when($adminId <= 0 && $adminUsername === '', fn ($q) => $q->where('username', 'scintilla'))
            ->first();

        $adminNotifyEmail = trim((string) ($admin?->email ?? ''));
    } catch (\Throwable $e) {
        $adminNotifyEmail = '';
    }

    return view('organizer.request', compact('adminNotifyEmail'));
})->name('organizer.request')->middleware(['auth', 'approved']);

Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

// Chat: solo utenti autenticati e approvati (come il resto del sito riservato)
Route::get('/chat', [ChatController::class, 'index'])
    ->name('chat.index')
    ->middleware(['auth', 'approved', 'feature:chat_salottino']);

// Chat "in arrivo" (quando la feature è disattivata)
Route::get('/chat/in-arrivo', function () {
    return view('chat.coming-soon');
})->name('chat.coming-soon')->middleware(['auth', 'approved']);
Route::post('/chat', [ChatController::class, 'store'])
    ->name('chat.store')
    ->middleware(['auth', 'approved', 'feature:chat_salottino']);
Route::put('/chat/{message}', [ChatController::class, 'update'])
    ->name('chat.update')
    ->middleware(['auth', 'approved', 'feature:chat_salottino']);
Route::delete('/chat/{message}', [ChatController::class, 'destroy'])
    ->name('chat.destroy')
    ->middleware(['auth', 'approved', 'feature:chat_salottino']);
Route::post('/chat/header-image', [ChatController::class, 'updateHeaderImage'])->name('chat.header-image')->middleware('admin');

// Mercatino: solo utenti registrati e approvati (come la chat)
Route::get('/mercatino', function (Request $request) {
    $bozze = collect();
    $base = 'mercatino_bozze/' . auth()->id();
    if (Storage::disk('local')->exists($base)) {
        foreach (Storage::disk('local')->directories($base) as $subdir) {
            $jsonPath = $subdir . '/dati.json';
            if (! Storage::disk('local')->exists($jsonPath)) {
                continue;
            }
            $decoded = json_decode(Storage::disk('local')->get($jsonPath), true);
            if (! is_array($decoded)) {
                continue;
            }
            $bozze->push([
                'cartella' => basename($subdir),
                'dati' => $decoded,
            ]);
        }
    }
    $bozze = $bozze->sortByDesc(function ($row) {
        return $row['dati']['inviato_il'] ?? '';
    })->values();

    $editFolder = trim((string) $request->query('edit', ''));
    $editDraft = null;
    if ($editFolder !== '' && preg_match('/^[a-zA-Z0-9_\-]+$/', $editFolder) === 1) {
        $jsonPath = $base . '/' . $editFolder . '/dati.json';
        if (Storage::disk('local')->exists($jsonPath)) {
            $decoded = json_decode(Storage::disk('local')->get($jsonPath), true);
            if (is_array($decoded)) {
                $editDraft = $decoded;
            }
        }
    }

    return view('mercatino.index', compact('bozze', 'editFolder', 'editDraft'));
})->name('mercatino.index')->middleware(['auth', 'approved', 'feature:mercatino']);

Route::post('/mercatino/bozze/delete', function () {
    $base = 'mercatino_bozze/' . auth()->id();
    if (Storage::disk('local')->exists($base)) {
        Storage::disk('local')->deleteDirectory($base);
    }

    return redirect()->route('mercatino.index')->with('success', 'Bozze eliminate con successo.');
})->name('mercatino.bozze.delete')->middleware(['auth', 'approved', 'feature:mercatino']);

Route::post('/mercatino/bozze/{folder}/delete', function (Request $request, string $folder) {
    if (preg_match('/^[a-zA-Z0-9_\-]+$/', $folder) !== 1) {
        return back()->with('error', 'Bozza non valida.');
    }
    $base = 'mercatino_bozze/' . auth()->id();
    $dir = $base . '/' . $folder;
    if (!Storage::disk('local')->exists($dir)) {
        return back()->with('error', 'Bozza non trovata.');
    }
    Storage::disk('local')->deleteDirectory($dir);
    return redirect()->route('mercatino.index')->with('success', 'Bozza eliminata.');
})->name('mercatino.bozza.destroy')->middleware(['auth', 'approved', 'feature:mercatino']);

Route::post('/mercatino/bozze/{folder}', function (Request $request, string $folder) {
    if (preg_match('/^[a-zA-Z0-9_\-]+$/', $folder) !== 1) {
        return back()->with('error', 'Bozza non valida.');
    }

    $base = 'mercatino_bozze/' . auth()->id();
    $dir = $base . '/' . $folder;
    $jsonPath = $dir . '/dati.json';
    if (!Storage::disk('local')->exists($jsonPath)) {
        return back()->with('error', 'Bozza non trovata.');
    }

    $validated = $request->validate([
        'titolo' => ['required', 'string', 'max:120'],
        'categoria' => ['required', 'in:abbigliamento,veicoli,casa,sport,elettronica_videogiochi,altro'],
        'descrizione' => ['required', 'string', 'max:2000'],
        'tipo_prezzo' => ['required', 'in:fisso,gratis,trattabile,scambio'],
        'prezzo' => ['nullable', 'numeric', 'min:0', 'required_if:tipo_prezzo,fisso'],
        'condizione' => ['required', 'in:nuovo,ottimo,buono,discreto'],
        'zona_ritiro' => ['required', 'string', 'max:120'],
        'contatto' => ['required', 'in:excursio,email,telefono'],
        'foto_1' => ['nullable', 'image', 'max:4096', 'mimes:jpeg,jpg,png,webp,gif'],
        'foto_2' => ['nullable', 'image', 'max:4096', 'mimes:jpeg,jpg,png,webp,gif'],
        'foto_3' => ['nullable', 'image', 'max:4096', 'mimes:jpeg,jpg,png,webp,gif'],
        'remove_foto_1' => ['nullable', 'boolean'],
        'remove_foto_2' => ['nullable', 'boolean'],
        'remove_foto_3' => ['nullable', 'boolean'],
    ]);

    $validated['titolo'] = mb_strtoupper(trim((string) $validated['titolo']), 'UTF-8');

    $decoded = json_decode(Storage::disk('local')->get($jsonPath), true);
    if (!is_array($decoded)) {
        $decoded = [];
    }

    $decoded['titolo'] = $validated['titolo'];
    $decoded['categoria'] = $validated['categoria'];
    $decoded['descrizione'] = $validated['descrizione'];
    $decoded['tipo_prezzo'] = $validated['tipo_prezzo'];
    $decoded['prezzo'] = $validated['prezzo'] ?? null;
    $decoded['condizione'] = $validated['condizione'];
    $decoded['zona_ritiro'] = $validated['zona_ritiro'];
    $decoded['contatto'] = $validated['contatto'];
    $decoded['modificato_il'] = now()->toIso8601String();

    $exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    for ($i = 1; $i <= 3; $i++) {
        $removeKey = 'remove_foto_' . $i;
        $fileKey = 'foto_' . $i;

        $remove = (bool) ($validated[$removeKey] ?? false);
        if ($remove) {
            foreach ($exts as $ext) {
                $rel = $dir . '/foto_' . $i . '.' . $ext;
                if (Storage::disk('local')->exists($rel)) {
                    Storage::disk('local')->delete($rel);
                }
            }
        }

        if ($request->hasFile($fileKey)) {
            $file = $request->file($fileKey);
            if ($file && $file->isValid()) {
                foreach ($exts as $ext) {
                    $rel = $dir . '/foto_' . $i . '.' . $ext;
                    if (Storage::disk('local')->exists($rel)) {
                        Storage::disk('local')->delete($rel);
                    }
                }
                $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
                $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'jpg';
                Storage::disk('local')->putFileAs($dir, $file, 'foto_' . $i . '.' . $ext);
            }
        }
    }

    $fotoCaricate = 0;
    for ($i = 1; $i <= 3; $i++) {
        foreach ($exts as $ext) {
            if (Storage::disk('local')->exists($dir . '/foto_' . $i . '.' . $ext)) {
                $fotoCaricate++;
                break;
            }
        }
    }
    $decoded['foto_caricate'] = $fotoCaricate;

    Storage::disk('local')->put(
        $jsonPath,
        json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );

    return redirect()->route('mercatino.index')->with('success', 'Bozza aggiornata con successo.');
})->name('mercatino.bozza.update')->middleware(['auth', 'approved', 'feature:mercatino']);

// Mercatino: vetrina annunci pubblicati
Route::get('/mercatino/vetrina', function () {
    $annunci = collect();
    $base = 'mercatino_annunci';
    if (Storage::disk('public')->exists($base)) {
        foreach (Storage::disk('public')->directories($base) as $subdir) {
            $jsonPath = $subdir . '/dati.json';
            if (! Storage::disk('public')->exists($jsonPath)) {
                continue;
            }
            $decoded = json_decode(Storage::disk('public')->get($jsonPath), true);
            if (! is_array($decoded)) {
                continue;
            }
            $annunci->push([
                'cartella' => basename($subdir),
                'dati' => $decoded,
            ]);
        }
    }

    $annunci = $annunci->sortByDesc(function ($row) {
        return $row['dati']['inviato_il'] ?? '';
    })->values();

    return view('mercatino.vetrina', compact('annunci'));
})->name('mercatino.vetrina')->middleware(['auth', 'approved', 'feature:mercatino']);

// Mercatino: contatta inserzionista (via email)
Route::post('/mercatino/contatta', function (Request $request) {
    $validated = $request->validate([
        'folder' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z0-9_\-]+$/'],
        'messaggio' => ['required', 'string', 'min:5', 'max:2000'],
    ]);

    $folder = $validated['folder'];
    $jsonPath = 'mercatino_annunci/' . $folder . '/dati.json';
    if (!Storage::disk('public')->exists($jsonPath)) {
        return back()->with('error', 'Annuncio non trovato o non più disponibile.');
    }

    $decoded = json_decode(Storage::disk('public')->get($jsonPath), true);
    if (!is_array($decoded)) {
        return back()->with('error', 'Annuncio non valido.');
    }

    $sellerUsername = trim((string) ($decoded['autore_username'] ?? ''));
    if ($sellerUsername === '') {
        return back()->with('error', 'Impossibile contattare l’inserzionista (utente non indicato).');
    }

    $seller = User::query()
        ->whereRaw('LOWER(username) = ?', [mb_strtolower($sellerUsername, 'UTF-8')])
        ->first();

    if (!$seller) {
        return back()->with('error', 'Impossibile contattare l’inserzionista (utente non trovato).');
    }

    if ((int) $seller->getKey() === (int) auth()->id()) {
        return back()->with('error', 'Non puoi contattare te stesso.');
    }

    $sellerEmail = trim((string) ($seller->email ?? ''));
    if ($sellerEmail === '') {
        return back()->with('error', 'L’inserzionista non ha un’email disponibile.');
    }

    $buyer = auth()->user();
    $buyerUsername = trim((string) ($buyer?->username ?? ''));
    $buyerEmail = trim((string) ($buyer?->email ?? ''));

    $titolo = trim((string) ($decoded['titolo'] ?? 'Annuncio Mercatino'));
    $when = now()->timezone(config('app.timezone'))->format('d/m/Y H:i');
    $vetrinaUrl = \Illuminate\Support\Facades\URL::route('mercatino.vetrina', [], true);

    $body =
        "Hai ricevuto un nuovo messaggio da un utente interessato al tuo annuncio nel Mercatino di Excursio.\n\n" .
        "Annuncio: {$titolo}\n" .
        "Data: {$when}\n" .
        "Da: " . ($buyerUsername !== '' ? $buyerUsername : ('Utente #' . auth()->id())) . "\n\n" .
        "Messaggio:\n" .
        trim((string) $validated['messaggio']) . "\n\n" .
        "Apri la vetrina: {$vetrinaUrl}\n";

    $subject = config('app.name', 'Excursio') . ' — Messaggio su annuncio Mercatino: ' . ($titolo !== '' ? $titolo : 'Annuncio');

    try {
        $admins = User::query()
            ->where('ruolo', 0)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();
        $adminBcc = $admins->pluck('email')->filter()->unique()->values()->all();

        Mail::raw($body, function ($message) use ($sellerEmail, $buyerEmail, $subject, $adminBcc) {
            $message->to($sellerEmail)->subject($subject);
            if ($buyerEmail !== '') {
                $message->replyTo($buyerEmail);
            }
            if (!empty($adminBcc)) {
                $message->bcc($adminBcc);
            }
        });
    } catch (\Throwable $e) {
        \Log::warning('Mercatino contact email failed: ' . $e->getMessage(), [
            'exception' => $e,
            'folder' => $folder,
            'seller_id' => $seller->getKey(),
            'buyer_id' => auth()->id(),
        ]);
        return back()->with('error', 'Messaggio non inviato (errore email). Riprova più tardi.');
    }

    return back()->with('success', 'Messaggio inviato all’inserzionista. Se ti risponde, riceverai una email.');
})->name('mercatino.contact')->middleware(['auth', 'approved', 'feature:mercatino']);

// Mercatino: modifica annuncio in vetrina (solo autore o admin) - aggiorna dati.json, non le foto
Route::post('/mercatino/modifica', function (Request $request) {
    $validated = $request->validate([
        'folder' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z0-9_\-]+$/'],
        'titolo' => ['required', 'string', 'max:120'],
        'categoria' => ['required', 'in:abbigliamento,veicoli,casa,sport,elettronica_videogiochi,altro'],
        'descrizione' => ['required', 'string', 'max:2000'],
        'tipo_prezzo' => ['required', 'in:fisso,gratis,trattabile,scambio'],
        'prezzo' => ['nullable', 'numeric', 'min:0', 'required_if:tipo_prezzo,fisso'],
        'condizione' => ['required', 'in:nuovo,ottimo,buono,discreto'],
        'zona_ritiro' => ['required', 'string', 'max:120'],
        'contatto' => ['required', 'in:excursio,email,telefono'],
        'foto_1' => ['nullable', 'image', 'max:4096', 'mimes:jpeg,jpg,png,webp,gif'],
        'foto_2' => ['nullable', 'image', 'max:4096', 'mimes:jpeg,jpg,png,webp,gif'],
        'foto_3' => ['nullable', 'image', 'max:4096', 'mimes:jpeg,jpg,png,webp,gif'],
        'remove_foto_1' => ['nullable', 'boolean'],
        'remove_foto_2' => ['nullable', 'boolean'],
        'remove_foto_3' => ['nullable', 'boolean'],
    ]);

    $folder = $validated['folder'];
    $jsonPath = 'mercatino_annunci/' . $folder . '/dati.json';
    if (!Storage::disk('public')->exists($jsonPath)) {
        return back()->with('error', 'Annuncio non trovato o non più disponibile.');
    }

    $decoded = json_decode(Storage::disk('public')->get($jsonPath), true);
    if (!is_array($decoded)) {
        return back()->with('error', 'Annuncio non valido.');
    }

    $authorUsername = trim((string) ($decoded['autore_username'] ?? ''));
    $isAdmin = auth()->check() && auth()->user()->isAdmin();
    $me = auth()->user();
    $meUsername = trim((string) ($me?->username ?? ''));
    $isOwner = $meUsername !== '' && $authorUsername !== '' && mb_strtolower($meUsername, 'UTF-8') === mb_strtolower($authorUsername, 'UTF-8');
    if (!$isAdmin && !$isOwner) {
        abort(403);
    }

    $validated['titolo'] = mb_strtoupper(trim((string) $validated['titolo']), 'UTF-8');

    // Mantieni i campi non editabili (foto, autore, data invio).
    $decoded['titolo'] = $validated['titolo'];
    $decoded['categoria'] = $validated['categoria'];
    $decoded['descrizione'] = $validated['descrizione'];
    $decoded['tipo_prezzo'] = $validated['tipo_prezzo'];
    $decoded['prezzo'] = $validated['prezzo'] ?? null;
    $decoded['condizione'] = $validated['condizione'];
    $decoded['zona_ritiro'] = $validated['zona_ritiro'];
    $decoded['contatto'] = $validated['contatto'];
    $decoded['modificato_il'] = now()->toIso8601String();

    // Gestione foto (public disk): rimozione / sostituzione
    $baseDir = 'mercatino_annunci/' . $folder;
    $exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    for ($i = 1; $i <= 3; $i++) {
        $removeKey = 'remove_foto_' . $i;
        $fileKey = 'foto_' . $i;

        $remove = (bool) ($validated[$removeKey] ?? false);
        if ($remove) {
            foreach ($exts as $ext) {
                $rel = $baseDir . '/foto_' . $i . '.' . $ext;
                if (Storage::disk('public')->exists($rel)) {
                    Storage::disk('public')->delete($rel);
                }
            }
        }

        if ($request->hasFile($fileKey)) {
            $file = $request->file($fileKey);
            if ($file && $file->isValid()) {
                // Elimina eventuali versioni precedenti (qualsiasi estensione)
                foreach ($exts as $ext) {
                    $rel = $baseDir . '/foto_' . $i . '.' . $ext;
                    if (Storage::disk('public')->exists($rel)) {
                        Storage::disk('public')->delete($rel);
                    }
                }
                $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
                $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'jpg';
                Storage::disk('public')->putFileAs($baseDir, $file, 'foto_' . $i . '.' . $ext);
            }
        }
    }

    // Ricalcola foto caricate
    $fotoCaricate = 0;
    for ($i = 1; $i <= 3; $i++) {
        foreach ($exts as $ext) {
            if (Storage::disk('public')->exists($baseDir . '/foto_' . $i . '.' . $ext)) {
                $fotoCaricate++;
                break;
            }
        }
    }
    $decoded['foto_caricate'] = $fotoCaricate;

    Storage::disk('public')->put(
        $jsonPath,
        json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );

    return back()->with('success', 'Annuncio aggiornato con successo.');
})->name('mercatino.update')->middleware(['auth', 'approved', 'feature:mercatino']);

// Mercatino "in arrivo" (quando la feature è disattivata)
Route::get('/mercatino/in-arrivo', function () {
    return view('mercatino.coming-soon');
})->name('mercatino.coming-soon')->middleware(['auth', 'approved']);
Route::post('/mercatino', function (Request $request) {
    $validated = $request->validate([
        'titolo' => ['required', 'string', 'max:120'],
        'categoria' => ['required', 'in:abbigliamento,veicoli,casa,sport,elettronica_videogiochi,altro'],
        'descrizione' => ['required', 'string', 'max:2000'],
        'tipo_prezzo' => ['required', 'in:fisso,gratis,trattabile,scambio'],
        'prezzo' => ['nullable', 'numeric', 'min:0', 'required_if:tipo_prezzo,fisso'],
        'condizione' => ['required', 'in:nuovo,ottimo,buono,discreto'],
        'zona_ritiro' => ['required', 'string', 'max:120'],
        'contatto' => ['required', 'in:excursio,email,telefono'],
        'accetto_regole' => ['accepted'],
        'foto_1' => ['nullable', 'image', 'max:4096', 'mimes:jpeg,jpg,png,webp,gif'],
        'foto_2' => ['nullable', 'image', 'max:4096', 'mimes:jpeg,jpg,png,webp,gif'],
        'foto_3' => ['nullable', 'image', 'max:4096', 'mimes:jpeg,jpg,png,webp,gif'],
    ], [
        'titolo.required' => 'Inserisci un titolo per l’annuncio.',
        'categoria.required' => 'Scegli una categoria.',
        'descrizione.required' => 'Descrivi l’oggetto o il servizio offerto.',
        'tipo_prezzo.required' => 'Indica come vuoi gestire il prezzo.',
        'condizione.required' => 'Indica lo stato dell’oggetto.',
        'zona_ritiro.required' => 'Indica dove si può ritirare o incontrarsi.',
        'contatto.required' => 'Scegli come preferisci essere contattato.',
        'accetto_regole.accepted' => 'Devi accettare le condizioni per inviare la bozza.',
        'foto_1.image' => 'La prima immagine deve essere un file immagine valido.',
        'foto_2.image' => 'La seconda immagine deve essere un file immagine valido.',
        'foto_3.image' => 'La terza immagine deve essere un file immagine valido.',
        'foto_1.max' => 'La prima immagine non può superare 4 MB.',
        'foto_2.max' => 'La seconda immagine non può superare 4 MB.',
        'foto_3.max' => 'La terza immagine non può superare 4 MB.',
        'foto_1.mimes' => 'Formati ammessi per la 1ª foto: JPEG, PNG, WebP, GIF.',
        'foto_2.mimes' => 'Formati ammessi per la 2ª foto: JPEG, PNG, WebP, GIF.',
        'foto_3.mimes' => 'Formati ammessi per la 3ª foto: JPEG, PNG, WebP, GIF.',
    ]);

    // Titolo sempre in MAIUSCOLO (UTF-8)
    $validated['titolo'] = mb_strtoupper(trim((string) ($validated['titolo'] ?? '')), 'UTF-8');

    $batchKey = now()->format('Ymd_His') . '_' . Str::lower(Str::random(8));
    $batchDir = 'mercatino_bozze/' . auth()->id() . '/' . $batchKey;
    $publicDir = 'mercatino_annunci/' . $batchKey;
    $fotoCaricate = 0;
    foreach (['foto_1', 'foto_2', 'foto_3'] as $i => $field) {
        if (!$request->hasFile($field)) {
            continue;
        }
        $file = $request->file($field);
        if (!$file->isValid()) {
            continue;
        }
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'jpg';
        $file->storeAs($batchDir, 'foto_' . ($i + 1) . '.' . $ext, 'local');
        // Copia anche su disco pubblico (vetrina annunci)
        $file->storeAs($publicDir, 'foto_' . ($i + 1) . '.' . $ext, 'public');
        $fotoCaricate++;
    }

    $datiAnnuncio = [
        'titolo' => $validated['titolo'],
        'categoria' => $validated['categoria'],
        'descrizione' => $validated['descrizione'],
        'tipo_prezzo' => $validated['tipo_prezzo'],
        'prezzo' => $validated['prezzo'] ?? null,
        'condizione' => $validated['condizione'],
        'zona_ritiro' => $validated['zona_ritiro'],
        'contatto' => $validated['contatto'],
        'inviato_il' => now()->toIso8601String(),
        'foto_caricate' => $fotoCaricate,
        'autore_username' => auth()->user()->username ?? '',
    ];
    Storage::disk('local')->put(
        $batchDir . '/dati.json',
        json_encode($datiAnnuncio, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );
    Storage::disk('public')->put(
        $publicDir . '/dati.json',
        json_encode($datiAnnuncio, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );

    // Notifica amministratori: nuova bozza Mercatino (non bloccare l'invio se l'email fallisce)
    try {
        $mercatinoUrl = \Illuminate\Support\Facades\URL::route('mercatino.index', [], true);
        $autore = auth()->user();
        $autoreData = [
            'id' => $autore?->getKey(),
            'username' => $autore?->username,
            'email' => $autore?->email,
        ];
        $admins = User::query()
            ->where('ruolo', 0)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new \App\Mail\MercatinoNewAnnouncementMail($datiAnnuncio, $autoreData, $mercatinoUrl));
        }
    } catch (\Throwable $e) {
        \Log::error('Email Mercatino (admin) non inviata: ' . $e->getMessage(), [
            'exception' => $e,
            'user_id' => auth()->id(),
            'categoria' => $datiAnnuncio['categoria'] ?? null,
            'titolo' => $datiAnnuncio['titolo'] ?? null,
        ]);
    }

    $msg = 'Annuncio pubblicato nella vetrina del Mercatino. Lo vedi anche nella sezione «Le tue bozze» sotto.';
    if ($fotoCaricate > 0) {
        $msg .= ' ' . $fotoCaricate . ' ' . ($fotoCaricate === 1 ? 'foto caricata' : 'foto caricate') . '.';
    }

    return redirect()->route('mercatino.vetrina')->with('success', $msg);
})->name('mercatino.store')->middleware(['auth', 'approved', 'feature:mercatino']);

// Route di autenticazione
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');

    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    // Recupero password
    Route::get('/forgot-password', [PasswordResetController::class, 'showForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendReset'])->name('password.send');
    Route::get('/reset-password', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Route protette
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/impersonation/stop', [ImpersonationController::class, 'stop'])->name('impersonation.stop');

    Route::get('/albums-foto', [EventController::class, 'photoAlbums'])
        ->name('photo-albums.index')
        ->middleware('feature:albums_foto');

    // Azioni admin per pagina Album foto
    Route::delete('/albums-foto/{event}', [EventController::class, 'destroyPhotoAlbumLink'])
        ->name('photo-albums.destroy')
        ->middleware('admin');
});

// Route protette (solo utenti approvati)
Route::middleware(['auth', 'approved'])->group(function () {
    Route::post('/events/{event}/participate', [EventController::class, 'participate'])
        ->name('events.participate');

    Route::post('/events/{event}/cancel', [EventController::class, 'cancelParticipation'])
        ->name('events.cancel');

    Route::post('/events/{event}/waitlist', [EventController::class, 'joinWaitlist'])
        ->name('events.waitlist.join');

    Route::delete('/events/{event}/waitlist', [EventController::class, 'leaveWaitlist'])
        ->name('events.waitlist.leave');

    // Stampa partecipanti (solo amministratori)
    Route::get('/events/{event}/print', [EventController::class, 'printParticipants'])
        ->name('events.print')
        ->middleware('admin');

    // Invito amici a evento
    Route::post('/events/{event}/invite', [FriendController::class, 'inviteToEvent'])->name('events.invite');

    // Route per i profili utente
    Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/{user}/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/{user}', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/my-events/active', [ProfileController::class, 'myActiveEvents'])->name('my-events.active');
    Route::post('/profile/{user}/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password.update')
        ->middleware('admin');
    Route::post('/profile/{user}/password-self', [ProfileController::class, 'updateOwnPassword'])
        ->name('profile.password.self');
    Route::post('/profile/{user}/admin-note', [ProfileController::class, 'updateAdminNote'])
        ->name('profile.admin-note.update')
        ->middleware('admin');

    // Amicizie
    Route::get('/friends', [FriendController::class, 'index'])->name('friends.index');
    Route::post('/friends/{user}/add', [FriendController::class, 'add'])->name('friends.add');
    Route::delete('/friends/{user}/remove', [FriendController::class, 'remove'])->name('friends.remove');

    // Ricerca utenti (solo per username; link nella barra per tutti gli utenti approvati)
    Route::get('/users/search', [SearchController::class, 'users'])->name('users.search');
    Route::get('/users/autocomplete', [SearchController::class, 'usersAutocomplete'])->name('users.autocomplete');

    // Commenti
    Route::post('/events/{event}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::get('/comments/{comment}/edit', [CommentController::class, 'edit'])->name('comments.edit');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Comunicazioni evento: invio email a tutti gli iscritti (solo admin o organizzatore)
    Route::post('/events/{event}/communications', [EventController::class, 'sendCommunication'])
        ->name('events.communications.send');

    // Upload immagini da CKEditor (Forum eventi / descrizioni)
    Route::post('/ckeditor/upload', [CkeditorUploadController::class, 'upload'])->name('ckeditor.upload');

    // Gestione ospiti
    Route::post('/events/{event}/add-guest', [GuestController::class, 'addGuest'])->name('events.add-guest');
    Route::post('/events/{event}/remove-guest', [GuestController::class, 'removeGuest'])->name('events.remove-guest');
    Route::post('/events/{event}/guest-name', [GuestController::class, 'updateGuestName'])->name('events.update-guest-name');
});

// Route gestione eventi (organizzatori e admin)
Route::middleware(['auth', 'can-manage-events'])->prefix('manage')->name('manage.')->group(function () {
    Route::get('/events/create', [\App\Http\Controllers\EventManageController::class, 'create'])->name('events.create');
    Route::post('/events', [\App\Http\Controllers\EventManageController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [\App\Http\Controllers\EventManageController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [\App\Http\Controllers\EventManageController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [\App\Http\Controllers\EventManageController::class, 'destroy'])->name('events.destroy');
    Route::post('/events/{event}/toggle-status', [\App\Http\Controllers\EventManageController::class, 'toggleStatus'])->name('events.toggle-status');
    Route::get('/events', [\App\Http\Controllers\EventManageController::class, 'index'])->name('events.index');
});

// Route amministrative
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard admin
    Route::get('/dashboard', function () {
        $usersCount = User::where('ruolo', '!=', 0)->count();
        $eventsCount = Event::where('pubblicato', 1)->count();
        $pendingUsers = User::where('abilitato', 3)->where('ruolo', '!=', 0)->count();

        return view('admin.dashboard', compact('usersCount', 'eventsCount', 'pendingUsers'));
    })->name('dashboard');

    Route::post('/home-pending-registrations/dismiss', [\App\Http\Controllers\Admin\HomePendingBannerController::class, 'dismiss'])
        ->name('home-pending-dismiss');

    Route::post('/site-settings/feature/{featureKey}/toggle', [\App\Http\Controllers\Admin\SiteSettingsController::class, 'toggleFeature'])
        ->name('site-settings.feature.toggle');

    Route::get('/common-event', [\App\Http\Controllers\Admin\CommonEventController::class, 'showForm'])
        ->name('common-event.form');
    Route::post('/common-event', [\App\Http\Controllers\Admin\CommonEventController::class, 'search'])
        ->name('common-event.search');
    Route::get('/common-event/users-search', [\App\Http\Controllers\Admin\CommonEventController::class, 'usersSearch'])
        ->name('common-event.users-search');

    Route::resource('events', \App\Http\Controllers\Admin\EventController::class);
    Route::post('/events/{event}/duplicate', [\App\Http\Controllers\Admin\EventController::class, 'duplicate'])->name('events.duplicate');
    Route::post('/events/{event}/toggle-status', [\App\Http\Controllers\Admin\EventController::class, 'toggleStatus'])->name('events.toggle-status');
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/impersonate', [ImpersonationController::class, 'start'])->name('users.impersonate');
    Route::get('/users/logins', [AdminUserController::class, 'logins'])->name('users.logins');
    Route::post('/users/{user}/approve', [\App\Http\Controllers\Admin\UserController::class, 'approve'])->name('users.approve');
    Route::post('/users/{user}/suspend', [\App\Http\Controllers\Admin\UserController::class, 'suspend'])->name('users.suspend');
    Route::post('/users/{user}/ban', [\App\Http\Controllers\Admin\UserController::class, 'ban'])->name('users.ban');
    Route::post('/users/{user}/unban', [\App\Http\Controllers\Admin\UserController::class, 'unban'])->name('users.unban');
    Route::post('/users/{user}/role', [\App\Http\Controllers\Admin\UserController::class, 'updateRole'])->name('users.update-role');
    Route::post('/users/{user}/newsletter', [\App\Http\Controllers\Admin\UserController::class, 'updateNewsletter'])->name('users.update-newsletter');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/gallery', [\App\Http\Controllers\Admin\UserController::class, 'gallery'])->name('users.gallery');

    // Gruppi
    Route::resource('groups', \App\Http\Controllers\Admin\GroupController::class);
    Route::post('/groups/{group}/members', [\App\Http\Controllers\Admin\GroupController::class, 'addMember'])->name('groups.add-member');
    Route::delete('/groups/{group}/members/{user}', [\App\Http\Controllers\Admin\GroupController::class, 'removeMember'])->name('groups.remove-member');

    // Newsletter routes
    // Compatibilità: alcuni link vecchi puntano a /admin/newsletter/create
    Route::get('/newsletter/create', function () {
        return redirect()->route('admin.newsletter.create');
    })->name('newsletter.create-compat');
    Route::get('/newsletter', [\App\Http\Controllers\Admin\NewsletterController::class, 'create'])->name('newsletter.create');
    Route::post('/newsletter/send', [\App\Http\Controllers\Admin\NewsletterController::class, 'send'])->name('newsletter.send');
    Route::get('/newsletter/users', [\App\Http\Controllers\Admin\NewsletterController::class, 'getUsers'])->name('newsletter.users');
    Route::get('/newsletter/group-recipients', [\App\Http\Controllers\Admin\NewsletterController::class, 'groupRecipients'])->name('newsletter.group-recipients');
    Route::post('/newsletter/preview-recipients', [\App\Http\Controllers\Admin\NewsletterController::class, 'previewRecipients'])->name('newsletter.preview-recipients');

});
