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
    public function index()
    {
        $users = User::withCount('events')
            ->orderBy('ruolo') // admin/organizzatore/utente insieme, ordinati
            ->orderBy('abilitato')
            ->orderBy('iscrittodal', 'desc')
            ->get();

        $pendingCount  = User::where('abilitato', 0)->count();
        $approvedCount = User::where('abilitato', 1)->count();
        $bannedCount   = User::where('abilitato', 2)->count();

        return view('admin.users.index', compact('users', 'pendingCount', 'approvedCount', 'bannedCount'));
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
}
