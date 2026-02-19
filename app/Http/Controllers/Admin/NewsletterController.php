<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewsletterMail;

class NewsletterController extends Controller
{
    public function create()
    {
        $usersCount = User::nonAdmin()->where('abilitato', 1)->count();
        $participantsCount = User::nonAdmin()
            ->where('abilitato', 1)
            ->has('events')
            ->count();

        $users = User::nonAdmin()
            ->orderBy('nome')
            ->get();

        return view('admin.newsletter.create', compact('usersCount', 'participantsCount', 'users'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
            'target' => 'required|in:all,approved,participants,pending,selected',
            'selected_users' => 'nullable|array',
            'selected_users.*' => 'exists:utente,userID'
        ]);

        $usersQuery = User::nonAdmin();

        switch ($request->target) {
            case 'approved':
                $usersQuery->where('abilitato', 1);
                break;
            case 'participants':
                $usersQuery->where('abilitato', 1)->has('events');
                break;
            case 'pending':
                $usersQuery->where('abilitato', 0);
                break;
            case 'selected':
                if (empty($request->selected_users)) {
                    return back()->withErrors(['selected_users' => 'Seleziona almeno un utente.']);
                }
                $usersQuery->whereIn('userID', $request->selected_users);
                break;
        }

        $users = $usersQuery->get();

        if ($users->isEmpty()) {
            return back()->withErrors(['target' => 'Nessun utente trovato per il target selezionato.']);
        }

        $sentCount = 0;
        $failedEmails = [];

        foreach ($users as $user) {
            try {
                Mail::to($user->email)->send(new NewsletterMail(
                    $request->subject,
                    $request->message,
                    $user
                ));
                $sentCount++;
            } catch (\Exception $e) {
                $failedEmails[] = $user->email;
                \Log::error("Errore invio newsletter a {$user->email}: " . $e->getMessage());
            }
        }

        $message = "Newsletter inviata con successo a {$sentCount} utenti!";

        if (!empty($failedEmails)) {
            $message .= " Invio fallito per " . count($failedEmails) . " indirizzi.";
        }

        return redirect()->route('admin.newsletter.create')
            ->with('success', $message)
            ->with('failed_emails', $failedEmails);
    }

    public function getUsers(Request $request)
    {
        $search = $request->input('search');

        $users = User::nonAdmin()
            ->when($search, function($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('nome', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->orderBy('nome')
            ->limit(50)
            ->get(['userID', 'nome', 'email', 'username', 'abilitato']);

        // Map to expected field names for the frontend
        $mapped = $users->map(function ($user) {
            return [
                'id' => $user->userID,
                'name' => $user->nome,
                'email' => $user->email,
                'nickname' => $user->username,
                'status' => $user->status,
            ];
        });

        return response()->json($mapped);
    }

    public function stats()
    {
        $totalUsers = User::nonAdmin()->count();
        $approvedUsers = User::nonAdmin()->where('abilitato', 1)->count();
        $pendingUsers = User::nonAdmin()->where('abilitato', 0)->count();
        $participants = User::nonAdmin()
            ->where('abilitato', 1)
            ->has('events')
            ->count();

        return view('admin.newsletter.stats', compact(
            'totalUsers', 'approvedUsers', 'pendingUsers', 'participants'
        ));
    }
}
