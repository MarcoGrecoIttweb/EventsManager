<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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

    public function inviteToEvent(Request $request, Event $event)
    {
        $request->validate([
            'friend_id' => 'required|integer|exists:utente,userID',
        ]);

        $friend = User::findOrFail($request->friend_id);
        $me = Auth::user();

        if (!$me->isFriendOf($friend)) {
            return back()->with('error', 'Puoi invitare solo i tuoi amici.');
        }

        // Check if already participating
        if ($event->participants()->where('utente.userID', $friend->getKey())->exists()) {
            return back()->with('error', $friend->nome . ' è già iscritto a questo evento.');
        }

        // Send invitation email
        if ($friend->email) {
            $eventUrl = route('events.show', $event);
            Mail::raw(
                "Ciao {$friend->nome},\n\n" .
                "{$me->nome} ti ha invitato all'evento \"{$event->title}\" " .
                "del {$event->date->format('d/m/Y H:i')}.\n\n" .
                "Vai alla pagina dell'evento per iscriverti: {$eventUrl}\n\n" .
                "A presto!\nIl team di Excursio",
                function ($message) use ($friend, $event, $me) {
                    $message->to($friend->email)
                        ->subject("Excursio - {$me->nome} ti invita a: {$event->title}");
                }
            );
        }

        return back()->with('success', 'Invito inviato a ' . $friend->nome . '!');
    }
}
