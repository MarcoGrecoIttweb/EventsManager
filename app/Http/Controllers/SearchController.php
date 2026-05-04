<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function users(Request $request)
    {
        $query = $request->input('q', '');
        $users = collect();

        if (strlen(trim($query)) >= 2) {
            $users = User::query()
                ->where('username', 'like', '%' . $query . '%')
                ->where('abilitato', 1)
                ->orderBy('username')
                ->get();
        }

        return view('search.users', compact('users', 'query'));
    }

    public function usersAutocomplete(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '' || mb_strlen($q, 'UTF-8') < 2) {
            return response()->json(['results' => []]);
        }

        $users = User::query()
            ->select(['userID', 'username', 'nome', 'cognome'])
            ->where('abilitato', 1)
            ->where('username', 'like', '%' . $q . '%')
            ->orderBy('username')
            ->limit(10)
            ->get();

        return response()->json([
            'results' => $users->map(function ($u) {
                $nome = trim((string) ($u->nome ?? ''));
                $cognome = trim((string) ($u->cognome ?? ''));
                $full = trim($nome . ' ' . $cognome);

                return [
                    'username' => (string) $u->username,
                    'label' => $full !== '' ? ($full . ' (' . $u->username . ')') : (string) $u->username,
                ];
            }),
        ]);
    }
}
