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
use App\Http\Controllers\CommentController;
use App\Http\Controllers\GuestController;


// Route pubbliche
Route::get('/', [EventController::class, 'index'])->name('home');
Route::get('/events', [EventController::class, 'index'])->name('events.index');

Route::get('/events/past', [EventController::class, 'pastEvents'])
    ->name('events.past')
    ->middleware('auth');

Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

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
    Route::post('/users/{user}/approve', [\App\Http\Controllers\Admin\UserController::class, 'approve'])->name('users.approve');
    Route::post('/users/{user}/ban', [\App\Http\Controllers\Admin\UserController::class, 'ban'])->name('users.ban');
    Route::post('/users/{user}/unban', [\App\Http\Controllers\Admin\UserController::class, 'unban'])->name('users.unban');
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
});
