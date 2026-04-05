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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


// Route pubbliche
Route::get('/', [EventController::class, 'index'])->name('home');
// Compatibilità: alcuni link puntano ancora a /home
Route::get('/home', function () {
    return redirect()->route('home');
});
Route::get('/events', [EventController::class, 'index'])->name('events.index');

Route::get('/events/past', [EventController::class, 'pastEvents'])
    ->name('events.past')
    ->middleware('auth');

Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

// Chat: solo utenti autenticati e approvati (come il resto del sito riservato)
Route::get('/chat', [ChatController::class, 'index'])
    ->name('chat.index')
    ->middleware(['auth', 'approved']);
Route::post('/chat', [ChatController::class, 'store'])
    ->name('chat.store')
    ->middleware(['auth', 'approved']);
Route::post('/chat/header-image', [ChatController::class, 'updateHeaderImage'])->name('chat.header-image')->middleware('admin');

// Mercatino: solo utenti registrati e approvati (come la chat)
Route::get('/mercatino', function () {
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

    return view('mercatino.index', compact('bozze'));
})->name('mercatino.index')->middleware(['auth', 'approved']);
Route::post('/mercatino', function (Request $request) {
    $validated = $request->validate([
        'titolo' => ['required', 'string', 'max:120'],
        'categoria' => ['required', 'in:attrezzatura,abbigliamento,libri_mappe,trasporti,altro'],
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

    $batchDir = 'mercatino_bozze/' . auth()->id() . '/' . now()->format('Ymd_His') . '_' . Str::lower(Str::random(8));
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
    ];
    Storage::disk('local')->put(
        $batchDir . '/dati.json',
        json_encode($datiAnnuncio, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );

    $msg = 'Bozza salvata: i dettagli sono nella sezione «Le tue bozze» sotto. Quando la vetrina sarà attiva potrai pubblicare per tutta la community.';
    if ($fotoCaricate > 0) {
        $msg .= ' ' . $fotoCaricate . ' ' . ($fotoCaricate === 1 ? 'foto salvata' : 'foto salvate') . ' sul server (bozza).';
    }

    return redirect()->route('mercatino.index')->with('success', $msg);
})->name('mercatino.store')->middleware(['auth', 'approved']);

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

    Route::post('/events/{event}/participate', [EventController::class, 'participate'])
        ->name('events.participate')
        ->middleware('approved');

    Route::post('/events/{event}/cancel', [EventController::class, 'cancelParticipation'])
        ->name('events.cancel');

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
    Route::post('/profile/{user}/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password.update')
        ->middleware('admin');
    Route::post('/profile/{user}/password-self', [ProfileController::class, 'updateOwnPassword'])
        ->name('profile.password.self');

    // Amicizie
    Route::get('/friends', [FriendController::class, 'index'])->name('friends.index');
    Route::post('/friends/{user}/add', [FriendController::class, 'add'])->name('friends.add');
    Route::delete('/friends/{user}/remove', [FriendController::class, 'remove'])->name('friends.remove');

    // Ricerca utenti
    Route::get('/users/search', [SearchController::class, 'users'])->name('users.search');

    // Commenti
    Route::post('/events/{event}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::get('/comments/{comment}/edit', [CommentController::class, 'edit'])->name('comments.edit');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

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
    Route::get('/events', [\App\Http\Controllers\EventManageController::class, 'index'])->name('events.index');
});

// Route amministrative
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard admin
    Route::get('/dashboard', function () {
        $usersCount = User::where('ruolo', '!=', 0)->count();
        $eventsCount = Event::where('pubblicato', 1)->count();
        $pendingUsers = User::where('abilitato', 0)->where('ruolo', '!=', 0)->count();

        return view('admin.dashboard', compact('usersCount', 'eventsCount', 'pendingUsers'));
    })->name('dashboard');

    Route::resource('events', \App\Http\Controllers\Admin\EventController::class);
    Route::post('/events/{event}/duplicate', [\App\Http\Controllers\Admin\EventController::class, 'duplicate'])->name('events.duplicate');
    Route::post('/events/{event}/toggle-status', [\App\Http\Controllers\Admin\EventController::class, 'toggleStatus'])->name('events.toggle-status');
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/logins', [AdminUserController::class, 'logins'])->name('users.logins');
    Route::post('/users/{user}/approve', [\App\Http\Controllers\Admin\UserController::class, 'approve'])->name('users.approve');
    Route::post('/users/{user}/ban', [\App\Http\Controllers\Admin\UserController::class, 'ban'])->name('users.ban');
    Route::post('/users/{user}/unban', [\App\Http\Controllers\Admin\UserController::class, 'unban'])->name('users.unban');
    Route::post('/users/{user}/role', [\App\Http\Controllers\Admin\UserController::class, 'updateRole'])->name('users.update-role');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/gallery', [\App\Http\Controllers\Admin\UserController::class, 'gallery'])->name('users.gallery');

    // Gruppi
    Route::resource('groups', \App\Http\Controllers\Admin\GroupController::class);
    Route::post('/groups/{group}/members', [\App\Http\Controllers\Admin\GroupController::class, 'addMember'])->name('groups.add-member');
    Route::delete('/groups/{group}/members/{user}', [\App\Http\Controllers\Admin\GroupController::class, 'removeMember'])->name('groups.remove-member');

    // Newsletter routes
    Route::get('/newsletter', [\App\Http\Controllers\Admin\NewsletterController::class, 'create'])->name('newsletter.create');
    Route::post('/newsletter/send', [\App\Http\Controllers\Admin\NewsletterController::class, 'send'])->name('newsletter.send');
    Route::get('/newsletter/stats', [\App\Http\Controllers\Admin\NewsletterController::class, 'stats'])->name('newsletter.stats');
    Route::get('/newsletter/users', [\App\Http\Controllers\Admin\NewsletterController::class, 'getUsers'])->name('newsletter.users');
    Route::get('/newsletter/group-recipients', [\App\Http\Controllers\Admin\NewsletterController::class, 'groupRecipients'])->name('newsletter.group-recipients');

    Route::get('/mail-test', [\App\Http\Controllers\Admin\MailTestController::class, 'show'])->name('mail-test');
    Route::post('/mail-test', [\App\Http\Controllers\Admin\MailTestController::class, 'send'])->name('mail-test.send');
});
