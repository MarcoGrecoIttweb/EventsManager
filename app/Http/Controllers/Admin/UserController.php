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
        $users = User::where('is_admin', false)
            ->withCount(['events' => function($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('status')
            ->orderBy('created_at', 'desc')
            ->get();

        $pendingCount = User::where('status', 'pending')->where('is_admin', false)->count();
        $approvedCount = User::where('status', 'approved')->where('is_admin', false)->count();
        $bannedCount = User::where('status', 'banned')->where('is_admin', false)->count();

        return view('admin.users.index', compact('users', 'pendingCount', 'approvedCount', 'bannedCount'));
    }

    /**
     * Approve a user.
     */
    public function approve(User $user)
    {
        if ($user->is_admin) {
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
        if ($user->is_admin) {
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
        if ($user->is_admin) {
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
        if ($user->is_admin) {
            return back()->with('error', 'Non puoi eliminare un amministratore.');
        }

        $user->delete();

        return back()->with('success', "Utente {$user->nickname} eliminato con successo!");
    }
}
