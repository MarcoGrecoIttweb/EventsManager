<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Event;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\GuestController;


// Route pubbliche
Route::get('/', [EventController::class, 'index'])->name('home');
Route::get('/events', [EventController::class, 'index'])->name('events.index');

// AGGIUNGI QUESTA RIGA QUI - PRIMA di events/{event}
Route::get('/events/past', [EventController::class, 'pastEvents'])
    ->name('events.past')
    ->middleware('auth'); // Middleware auth qui

Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

// Route di autenticazione
Route::middleware('guest')->group(function () {
    // Pagine di login/registrazione
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');

    // Gestione del login/registrazione
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Route protette
Route::middleware(['auth'])->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Partecipazione eventi
    Route::post('/events/{event}/participate', [EventController::class, 'participate'])
        ->name('events.participate')
        ->middleware('approved');

    Route::post('/events/{event}/cancel', [EventController::class, 'cancelParticipation'])
        ->name('events.cancel');

    // RIMUOVI events/past da qui ↓

    // Route per i profili utente
    Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/{user}/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/{user}', [ProfileController::class, 'update'])->name('profile.update');

    // Commenti
    Route::post('/events/{event}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::get('/comments/{comment}/edit', [CommentController::class, 'edit'])->name('comments.edit');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Gestione ospiti
    Route::post('/events/{event}/add-guest', [GuestController::class, 'addGuest'])->name('events.add-guest');
    Route::post('/events/{event}/remove-guest', [GuestController::class, 'removeGuest'])->name('events.remove-guest');
});

// Route amministrative
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard admin
    Route::get('/dashboard', function () {
        $usersCount = User::where('is_admin', false)->count();
        $eventsCount = Event::where('is_active', true)->count();
        $pendingUsers = User::where('status', 'pending')->where('is_admin', false)->count();

        return view('admin.dashboard', compact('usersCount', 'eventsCount', 'pendingUsers'));
    })->name('dashboard');

    Route::resource('events', \App\Http\Controllers\Admin\EventController::class);
    Route::post('/events/{event}/toggle-status', [\App\Http\Controllers\Admin\EventController::class, 'toggleStatus'])->name('events.toggle-status');
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/approve', [\App\Http\Controllers\Admin\UserController::class, 'approve'])->name('users.approve');
    Route::post('/users/{user}/ban', [\App\Http\Controllers\Admin\UserController::class, 'ban'])->name('users.ban');
    Route::post('/users/{user}/unban', [\App\Http\Controllers\Admin\UserController::class, 'unban'])->name('users.unban');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
    // Newsletter routes
    Route::get('/newsletter', [\App\Http\Controllers\Admin\NewsletterController::class, 'create'])->name('newsletter.create');
    Route::post('/newsletter/send', [\App\Http\Controllers\Admin\NewsletterController::class, 'send'])->name('newsletter.send');
    Route::get('/newsletter/stats', [\App\Http\Controllers\Admin\NewsletterController::class, 'stats'])->name('newsletter.stats');
    Route::get('/newsletter/users', [\App\Http\Controllers\Admin\NewsletterController::class, 'getUsers'])->name('newsletter.users');
});
