<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewsletterMail;

class NewsletterController extends Controller
{
    /**
     * Show the newsletter form.
     */
    public function create()
    {
        $usersCount = User::where('is_admin', false)->where('status', 'approved')->count();
        $participantsCount = User::where('is_admin', false)
            ->where('status', 'approved')
            ->has('events')
            ->count();

        // Ottieni tutti gli utenti per la selezione individuale
        $users = User::where('is_admin', false)
            ->orderBy('name')
            ->get();

        return view('admin.newsletter.create', compact('usersCount', 'participantsCount', 'users'));
    }

    /**
     * Send the newsletter.
     */
    public function send(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
            'target' => 'required|in:all,approved,participants,pending,selected',
            'selected_users' => 'nullable|array',
            'selected_users.*' => 'exists:users,id'
        ]);

        // Seleziona gli utenti in base al target
        $usersQuery = User::where('is_admin', false);

        switch ($request->target) {
            case 'approved':
                $usersQuery->where('status', 'approved');
                break;
            case 'participants':
                $usersQuery->where('status', 'approved')->has('events');
                break;
            case 'pending':
                $usersQuery->where('status', 'pending');
                break;
            case 'selected':
                if (empty($request->selected_users)) {
                    return back()->withErrors(['selected_users' => 'Seleziona almeno un utente.']);
                }
                $usersQuery->whereIn('id', $request->selected_users);
                break;
            // 'all' include tutti gli utenti non admin
        }

        $users = $usersQuery->get();

        if ($users->isEmpty()) {
            return back()->withErrors(['target' => 'Nessun utente trovato per il target selezionato.']);
        }

        $sentCount = 0;
        $failedEmails = [];

        // Invia l'email a ogni utente
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

    /**
     * Get users for selection (AJAX).
     */
    public function getUsers(Request $request)
    {
        $search = $request->input('search');

        $users = User::where('is_admin', false)
            ->when($search, function($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nickname', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'email', 'nickname', 'status']);

        return response()->json($users);
    }

    /**
     * Get newsletter statistics.
     */
    public function stats()
    {
        $totalUsers = User::where('is_admin', false)->count();
        $approvedUsers = User::where('is_admin', false)->where('status', 'approved')->count();
        $pendingUsers = User::where('is_admin', false)->where('status', 'pending')->count();
        $participants = User::where('is_admin', false)
            ->where('status', 'approved')
            ->has('events')
            ->count();

        return view('admin.newsletter.stats', compact(
            'totalUsers', 'approvedUsers', 'pendingUsers', 'participants'
        ));
    }
}
