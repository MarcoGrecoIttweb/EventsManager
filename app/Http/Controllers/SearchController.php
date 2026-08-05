<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Elenco di tutti gli utenti abilitati (attivi) in forma di tabella.
     */
    public function index()
    {
        $users = User::query()
            ->where('abilitato', 1)
            ->orderBy('username')
            ->get();

        return view('users.index', compact('users'));
    }

    public function users(Request $request)
    {
        $query = $request->input('q', '');
        $type = $request->input('type', 'username');
        $users = collect();

        if (strlen(trim($query)) >= 2) {
            $users = User::query()
                ->where($type === 'nome' ? 'nome' : 'username', 'like', '%' . $query . '%')
                ->where('abilitato', 1)
                ->orderBy('username')
                ->get();
        }

        return view('search.users', compact('users', 'query', 'type'));
    }

    public function usersAutocomplete(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $type = $request->query('type', 'username');
        if ($q === '' || mb_strlen($q, 'UTF-8') < 2) {
            return response()->json(['results' => []]);
        }

        $users = User::query()
            ->select(['userID', 'username', 'nome'])
            ->where('abilitato', 1)
            ->where($type === 'nome' ? 'nome' : 'username', 'like', '%' . $q . '%')
            ->orderBy('username')
            ->limit(10)
            ->get();

        return response()->json([
            'results' => $users->map(function ($u) {
                $nome = trim((string) ($u->nome ?? ''));

                return [
                    'username' => (string) $u->username,
                    'label' => $nome !== '' ? ($nome . ' (' . $u->username . ')') : (string) $u->username,
                ];
            }),
        ]);
    }

    /**
     * Elenco JSON di tutti gli utenti abilitati con newsletter attiva (solo foto e username)
     * per il pulsante "Mostra tutti gli utenti" nella pagina di ricerca.
     */
    public function usersAll()
    {
        $users = User::query()
            ->where('abilitato', 1)
            ->where('invia', true)
            ->orderBy('username')
            ->get(['userID', 'username', 'avatar', 'nome']);

        return response()->json([
            'users' => $users->map(function ($u) {
                return [
                    'id' => (int) $u->userID,
                    'username' => (string) $u->username,
                    'photo_url' => $u->photo_url,
                    'profile_url' => route('profile.show', $u),
                ];
            }),
        ]);
    }
}
