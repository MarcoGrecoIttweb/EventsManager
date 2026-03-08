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
            $users = User::where(function ($q) use ($query) {
                    $q->where('username', 'like', "%{$query}%")
                      ->orWhere('nome', 'like', "%{$query}%")
                      ->orWhere('cognome', 'like', "%{$query}%");
                })
                ->where('abilitato', 1)
                ->orderBy('username')
                ->get();
        }

        return view('search.users', compact('users', 'query'));
    }
}
