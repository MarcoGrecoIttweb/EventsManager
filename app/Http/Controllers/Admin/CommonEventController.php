<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;

class CommonEventController extends Controller
{
    public function showForm(Request $request)
    {
        return view('admin.common-event', [
            'username1' => (string) $request->query('u1', ''),
            'username2' => (string) $request->query('u2', ''),
            'user1' => null,
            'user2' => null,
            'commonEvents' => collect(),
        ]);
    }

    public function search(Request $request)
    {
        $data = $request->validate([
            'username1' => ['required', 'string', 'max:255'],
            'username2' => ['required', 'string', 'max:255'],
        ]);

        $u1 = trim($data['username1']);
        $u2 = trim($data['username2']);

        $user1 = User::query()->where('username', $u1)->first();
        $user2 = User::query()->where('username', $u2)->first();

        $commonEvents = collect();
        if ($user1 && $user2) {
            $commonEvents = Event::query()
                ->whereHas('participants', function ($q) use ($user1) {
                    $q->where('utente.userID', (int) $user1->getKey());
                })
                ->whereHas('participants', function ($q) use ($user2) {
                    $q->where('utente.userID', (int) $user2->getKey());
                })
                ->ordered('desc')
                ->limit(20)
                ->get();
        }

        return view('admin.common-event', [
            'username1' => $u1,
            'username2' => $u2,
            'user1' => $user1,
            'user2' => $user2,
            'commonEvents' => $commonEvents,
        ]);
    }

    public function usersSearch(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        // Limita la query: niente risultati troppo generici.
        if ($q === '' || mb_strlen($q, 'UTF-8') < 2) {
            return response()->json(['results' => []]);
        }

        $qLike = $q . '%';

        $results = User::query()
            ->select(['username', 'nome', 'cognome'])
            ->where('ruolo', '!=', 0) // esclude gli admin
            ->where(function ($query) use ($qLike, $q) {
                $query->where('username', 'like', $qLike)
                    ->orWhere('nome', 'like', $qLike)
                    ->orWhere('cognome', 'like', $qLike)
                    ->orWhere('email', 'like', '%' . $q . '%');
            })
            ->orderBy('username')
            ->limit(10)
            ->get();

        return response()->json([
            'results' => $results->map(function ($u) {
                $nome = trim((string) ($u->nome ?? ''));
                $cognome = trim((string) ($u->cognome ?? ''));

                return [
                    'username' => (string) $u->username,
                    'label' => trim($nome . ' ' . $cognome) !== ''
                        ? trim($nome . ' ' . $cognome) . ' (' . $u->username . ')'
                        : $u->username,
                ];
            }),
        ]);
    }
}

