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
        $users = User::where('ruolo', '!=', 0)
            ->orderBy('abilitato')
            ->orderBy('iscrittodal', 'desc')
            ->get();

        $pendingCount  = User::where('ruolo', '!=', 0)->where('abilitato', 0)->count();
        $approvedCount = User::where('ruolo', '!=', 0)->where('abilitato', 1)->count();
        $bannedCount   = User::where('ruolo', '!=', 0)->where('abilitato', 2)->count();

        return view('admin.users.index', compact('users', 'pendingCount', 'approvedCount', 'bannedCount'));
    }

    /**
     * Approve a user.
     */
    public function approve(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Non puoi modificare lo stato di un amministratore.');
        }

        $user->update(['status' => 'approved']);

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

        $user->update(['status' => 'banned']);

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

        $user->update(['status' => 'approved']);

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
