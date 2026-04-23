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
}

