<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = User::withCount('events');

        if ($request->query('registrations') === 'pending') {
            $query->where('abilitato', 3)->where('ruolo', '!=', 0)
                ->orderBy('userID');
        } elseif ($request->query('status') === 'suspended') {
            $query->where('abilitato', 0)->where('ruolo', '!=', 0)
                ->orderBy('userID');
        } else {
            $query->orderBy('ruolo')
                ->orderBy('abilitato')
                ->orderBy('iscrittodal', 'desc');
        }

        $users = $query->get();

        $awaitingCount  = User::where('abilitato', 3)->count();
        $suspendedCount = User::where('abilitato', 0)->count();
        $approvedCount = User::where('abilitato', 1)->count();
        $bannedCount   = User::where('abilitato', 2)->count();

        return view('admin.users.index', compact(
            'users',
            'awaitingCount',
            'suspendedCount',
            'approvedCount',
            'bannedCount'
        ));
    }

    /**
     * Elenco ingressi giornalieri (ultimi 10 giorni).
     */
    public function logins(Request $request)
    {
        $days = (int) $request->query('days', 10);
        if ($days < 1) {
            $days = 1;
        }
        if ($days > 30) {
            $days = 30;
        }

        // Il sito legacy (parallelo) tipicamente aggiorna solo `utente.ultimo_accesso`,
        // mentre `user_login_events` viene popolata dal sito nuovo al momento del login.
        // Per mostrare gli accessi di ENTRAMBE le versioni, usiamo `ultimo_accesso`.
        $users = User::query()
            ->whereNotNull('ultimo_accesso')
            ->where('ultimo_accesso', '>=', now()->subDays($days))
            ->orderByDesc('ultimo_accesso')
            ->get();

        return view('admin.users.logins', compact('users', 'days'));
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
        $user->note_utente = null;
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
        $user->note_utente = null;
        $user->save();

        return back()->with('success', "Utente {$user->nickname} sbannato con successo!");
    }

    /**
     * Suspend an approved user (abilitato 0, distinto dall'iscrizione in attesa = 3).
     */
    public function suspend(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Non puoi modificare lo stato di un amministratore.');
        }

        $user->status = 'suspended';
        $user->save();

        return back()->with('success', "Utente {$user->nickname} sospeso con successo!");
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
