<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FriendController extends Controller
{
    public function index()
    {
        $friends = Auth::user()->friends()->orderBy('nome')->get();
        return view('friends.index', compact('friends'));
    }

    public function add(User $user)
    {
        $me = Auth::user();

        if ($me->getKey() === $user->getKey()) {
            return back()->with('error', 'Non puoi aggiungerti come amico.');
        }

        if ($me->isFriendOf($user)) {
            return back()->with('error', 'Questo utente è già tra i tuoi amici.');
        }

        // Insert friendship (one-directional as per legacy)
        DB::table('amici')->insert([
            'id_di_chi' => $me->getKey(),
            'id_amico' => $user->getKey(),
        ]);

        return back()->with('success', $user->nome . ' è stato aggiunto ai tuoi amici.');
    }

    public function remove(User $user)
    {
        DB::table('amici')
            ->where('id_di_chi', Auth::user()->getKey())
            ->where('id_amico', $user->getKey())
            ->delete();

        return back()->with('success', $user->nome . ' è stato rimosso dai tuoi amici.');
    }

}
