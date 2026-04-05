<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLoginEvent;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = User::withCount('events')
            ->orderBy('ruolo') // admin/organizzatore/utente insieme, ordinati
            ->orderBy('abilitato')
            ->orderBy('iscrittodal', 'desc');

        if ($request->query('registrations') === 'pending') {
            $query->where('abilitato', 0)->where('ruolo', '!=', 0);
        }

        $users = $query->get();

        $pendingCount  = User::where('abilitato', 0)->count();
        $approvedCount = User::where('abilitato', 1)->count();
        $bannedCount   = User::where('abilitato', 2)->count();

        return view('admin.users.index', compact('users', 'pendingCount', 'approvedCount', 'bannedCount'));
    }

    /**
     * Elenco ingressi giornalieri (ultimi 10 giorni).
     */
    public function logins()
    {
        $events = UserLoginEvent::with('user')
            ->where('logged_in_at', '>=', now()->subDays(10))
            ->orderByDesc('logged_in_at')
            ->get();

        return view('admin.users.logins', compact('events'));
    }

    /**
     * Display registered users gallery with profile links.
     */
    public function gallery()
    {
        $users = User::orderBy('nome')
            ->orderBy('cognome')
            ->get();

        return view('admin.users.gallery', compact('users'));
    }

    /**
     * Approve a user.
     */
    public function approve(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Non puoi modificare lo stato di un amministratore.');
        }

        $user->status = 'approved';
        $user->save();

        return back()->with('success', "Utente {$user->nickname} approvato con successo!");
    }

    /**
     * Ban a user.
     */
    public function ban(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Non puoi bannare un amministratore.');
        }

        $user->status = 'banned';
        $user->save();

        return back()->with('success', "Utente {$user->nickname} bannato con successo!");
    }

    /**
     * Unban a user.
     */
    public function unban(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Non puoi modificare lo stato di un amministratore.');
        }

        $user->status = 'approved';
        $user->save();

        return back()->with('success', "Utente {$user->nickname} sbannato con successo!");
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Non puoi eliminare un amministratore.');
        }

        $user->delete();

        return back()->with('success', "Utente {$user->nickname} eliminato con successo!");
    }

    /**
     * Update user role (admin/organizzatore/utente).
     */
    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'ruolo' => 'required|in:0,1,2',
        ]);

        // Non permettere di cambiare il ruolo del proprio account admin a ruoli più bassi per sicurezza minima
        if ($user->isAdmin() && (int) $validated['ruolo'] !== 0) {
            return back()->with('error', 'Non puoi modificare il ruolo di un amministratore.');
        }

        $user->ruolo = (int) $validated['ruolo'];
        $user->save();

        return back()->with('success', "Ruolo di {$user->nickname} aggiornato a {$user->role_name}.");
    }
}
