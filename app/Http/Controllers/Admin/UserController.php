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
        } elseif ($request->query('status') === 'approved') {
            $query->where('abilitato', 1)
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
     * Suggerimenti per il campo «Trova utente» (datalist), in base a nome / cognome / nickname (username).
     */
    public function finderSuggestions(Request $request)
    {
        $field = (string) $request->query('field', 'nome');
        if (!in_array($field, ['nome', 'cognome', 'nickname'], true)) {
            $field = 'nome';
        }

        $term = trim((string) $request->query('q', ''));
        if (mb_strlen($term, 'UTF-8') < 2) {
            return response()->json([]);
        }

        $escaped = addcslashes($term, '%_\\');
        $like = '%' . $escaped . '%';

        if ($field === 'cognome') {
            $column = 'cognome';
        } elseif ($field === 'nickname') {
            $column = 'username';
        } else {
            $column = 'nome';
        }

        $values = User::query()
            ->where($column, 'like', $like)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->orderBy($column)
            ->limit(40)
            ->pluck($column)
            ->map(function ($v) {
                return trim((string) $v);
            })
            ->filter(function ($v) {
                return $v !== '';
            })
            ->unique()
            ->values()
            ->take(15);

        return response()->json($values->all());
    }

    /**
     * Elenco ingressi giornalieri (default 1 giorno, fino a 10).
     */
    public function logins(Request $request)
    {
        $days = (int) $request->query('days', 1);
        if ($days < 1) {
            $days = 1;
        }
        if ($days > 10) {
            $days = 10;
        }

        $since = now()->subDays($days);

        $loginByUser = \App\Models\UserLoginEvent::query()
            ->where('logged_in_at', '>=', $since)
            ->selectRaw('user_id, source, MAX(logged_in_at) as last_at')
            ->groupBy('user_id', 'source')
            ->get()
            ->groupBy('user_id');

        $users = User::query()
            ->whereIn('userID', $loginByUser->keys())
            ->get()
            ->map(function (User $user) use ($loginByUser) {
                $rows = $loginByUser->get($user->userID, collect());
                $laravel = $rows->firstWhere('source', \App\Models\UserLoginEvent::SOURCE_LARAVEL);
                $legacy = $rows->firstWhere('source', \App\Models\UserLoginEvent::SOURCE_LEGACY);

                $user->last_login_laravel = $laravel?->last_at;
                $user->last_login_legacy = $legacy?->last_at;

                return $user;
            })
            ->sortByDesc(function (User $user) {
                $laravel = $user->last_login_laravel ? strtotime($user->last_login_laravel) : 0;
                $legacy = $user->last_login_legacy ? strtotime($user->last_login_legacy) : 0;

                return max($laravel, $legacy);
            })
            ->values();

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

    /**
     * Toggle newsletter subscription (utente.invia) for a user.
     */
    public function updateNewsletter(Request $request, User $user)
    {
        $validated = $request->validate([
            'invia' => 'required|in:0,1',
        ]);

        if ($user->isAdmin()) {
            return back()->with('error', 'Non puoi modificare la newsletter di un amministratore.');
        }

        $user->invia = (int) $validated['invia'] === 1;
        $user->save();

        $label = $user->invia ? 'attivata' : 'disattivata';
        return back()->with('success', "Newsletter {$label} per {$user->nickname}.");
    }
}
