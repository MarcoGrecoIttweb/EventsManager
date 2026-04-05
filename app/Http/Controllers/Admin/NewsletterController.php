<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use App\Mail\NewsletterMail;
use Illuminate\Support\Collection;

class NewsletterController extends Controller
{
    /** Dimensione predefinita gruppo newsletter (iscritti News attiva). */
    public const NEWS_GROUP_SIZE_DEFAULT = 80;

    public function create()
    {
        $totalUsersCount = User::nonAdmin()->count();
        $usersCount = User::nonAdmin()->where('abilitato', 1)->count();
        $participantsCount = User::nonAdmin()
            ->where('abilitato', 1)
            ->has('events')
            ->count();
        $newsSubscribersCount = User::nonAdmin()
            ->where('abilitato', 1)
            ->where('invia', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->count();

        $newsGroupSizePreview = self::NEWS_GROUP_SIZE_DEFAULT;
        $newsBatchCount = $newsSubscribersCount > 0
            ? (int) ceil($newsSubscribersCount / $newsGroupSizePreview)
            : 0;
        $newsBatchesMeta = [];
        for ($i = 1; $i <= $newsBatchCount; $i++) {
            $from = ($i - 1) * $newsGroupSizePreview + 1;
            $to = min($i * $newsGroupSizePreview, $newsSubscribersCount);
            $newsBatchesMeta[] = [
                'index' => $i,
                'from' => $from,
                'to' => $to,
                'count' => $to - $from + 1,
            ];
        }

        $users = User::nonAdmin()
            ->orderBy('nome')
            ->get();

        $newsletterReceiptAdmins = User::query()
            ->where('ruolo', 0)
            ->orderBy('nome')
            ->get(['userID', 'nome', 'email', 'username']);

        return view('admin.newsletter.create', compact(
            'usersCount',
            'totalUsersCount',
            'participantsCount',
            'newsSubscribersCount',
            'newsGroupSizePreview',
            'newsBatchCount',
            'newsBatchesMeta',
            'users',
            'newsletterReceiptAdmins'
        ));
    }

    public function send(Request $request)
    {
        $rules = [
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
            'target' => 'required|in:all,approved,participants,pending,selected,news',
            'selected_users' => 'nullable|array',
            'selected_users.*' => 'exists:utente,userID',
            'news_send' => 'nullable|in:all,groups',
            'news_group_size' => 'nullable|integer|min:20|max:300',
            'news_groups' => 'nullable|array|max:5',
            'news_groups.*' => 'integer|min:1',
            'exclude_newsletter_users' => 'nullable|array|max:500',
            'exclude_newsletter_users.*' => 'integer|exists:utente,userID',
            'newsletter_test_send_to_receipt_admin' => 'nullable|in:0,1',
        ];

        if ($request->target === 'news' && $request->input('news_send') === 'groups') {
            $rules['news_receipt_admin_id'] = [
                'required',
                'integer',
                Rule::exists('utente', 'userID')->where('ruolo', 0),
            ];
        }

        $request->validate($rules);

        $testSendToReceiptAdmin = (int) $request->input('newsletter_test_send_to_receipt_admin', 0) === 1;

        $users = new Collection();
        $newsGroupsApplied = [];
        $newsChunkSizeApplied = null;
        $newsReceiptAdmin = null;

        if ($request->target === 'news' && $request->input('news_send') === 'groups') {
            $newsReceiptAdmin = User::query()
                ->where('userID', (int) $request->input('news_receipt_admin_id'))
                ->where('ruolo', 0)
                ->first();

            if ($testSendToReceiptAdmin) {
                if (! $newsReceiptAdmin || trim((string) $newsReceiptAdmin->email) === '') {
                    return back()->withErrors([
                        'news_receipt_admin_id' => 'Per la prova serve un amministratore con indirizzo email valido.',
                    ]);
                }
                $users = new Collection([$newsReceiptAdmin]);
            } else {
                $chunkSize = (int) $request->input('news_group_size', self::NEWS_GROUP_SIZE_DEFAULT);
                $chunkSize = max(20, min(300, $chunkSize));

                $rawGroups = $request->input('news_groups', []);
                $groupNums = array_values(array_unique(array_filter(array_map('intval', $rawGroups))));
                sort($groupNums);

                if ($groupNums === []) {
                    return back()->withErrors(['news_groups' => 'Seleziona almeno un gruppo da inviare.']);
                }

                $allNews = User::nonAdmin()
                    ->where('abilitato', 1)
                    ->where('invia', true)
                    ->whereNotNull('email')
                    ->where('email', '!=', '')
                    ->orderBy('userID')
                    ->get();

                if ($allNews->isEmpty()) {
                    return back()->withErrors(['target' => 'Nessun iscritto alla newsletter con email valida.']);
                }

                $chunks = $allNews->chunk($chunkSize)->values();
                $maxGroup = $chunks->count();

                foreach ($groupNums as $num) {
                    if ($num < 1 || $num > $maxGroup) {
                        return back()->withErrors([
                            'news_groups' => 'Il gruppo '.$num.' non esiste: sono disponibili i gruppi da 1 a '.$maxGroup.'.',
                        ]);
                    }
                    $users = $users->concat($chunks[$num - 1]);
                }

                $users = $users->unique('userID')->values();

                $excludeIds = array_values(array_unique(array_filter(array_map('intval', $request->input('exclude_newsletter_users', [])))));
                if ($excludeIds !== []) {
                    $excludeSet = array_fill_keys($excludeIds, true);
                    $users = $users->reject(function ($u) use ($excludeSet) {
                        return isset($excludeSet[(int) $u->userID]);
                    })->values();
                }

                $newsGroupsApplied = $groupNums;
                $newsChunkSizeApplied = $chunkSize;
            }
        } else {
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
                case 'news':
                    $usersQuery->where('abilitato', 1)
                        ->where('invia', true);
                    break;
                case 'selected':
                    if (empty($request->selected_users)) {
                        return back()->withErrors(['selected_users' => 'Seleziona almeno un utente.']);
                    }
                    $usersQuery->whereIn('userID', $request->selected_users);
                    break;
            }

            $users = $usersQuery
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->get();
        }

        if ($users->isEmpty()) {
            return back()->withErrors(['target' => 'Nessun utente trovato per il target selezionato.']);
        }

        $sentCount = 0;
        $failedEmails = [];

        $mailSubject = $request->subject;
        if ($testSendToReceiptAdmin) {
            $mailSubject = '[PROVA] '.$mailSubject;
        }

        foreach ($users as $user) {
            try {
                Mail::to($user->email)->send(new NewsletterMail(
                    $mailSubject,
                    $request->message,
                    $user
                ));
                $sentCount++;
            } catch (\Exception $e) {
                $failedEmails[] = $user->email;
                \Log::error("Errore invio newsletter a {$user->email}: " . $e->getMessage());
            }
        }

        if ($testSendToReceiptAdmin && $newsReceiptAdmin) {
            if ($failedEmails !== []) {
                $message = 'Invio di prova non riuscito: non è stata recapitata email a '.$newsReceiptAdmin->email.'. Controlla configurazione SMTP e i log.';
            } else {
                $message = 'Invio di prova: email inviata solo a '.$newsReceiptAdmin->nome.' ('.$newsReceiptAdmin->email.'). '
                    .'Controlla casella e spam; l’oggetto inizia con [PROVA].';
            }
        } else {
            $message = "Newsletter inviata con successo a {$sentCount} utenti!";
            if ($newsGroupsApplied !== [] && $newsChunkSizeApplied !== null) {
                $message .= ' Persone per gruppo usate sul server: '.$newsChunkSizeApplied.'. Gruppi inviati: '.implode(', ', $newsGroupsApplied).'.';
                if ($newsReceiptAdmin) {
                    $message .= ' Riscontro invio: '.$newsReceiptAdmin->nome.'.';
                }
            }
        }

        if ($newsGroupsApplied !== [] && $newsChunkSizeApplied !== null && $newsReceiptAdmin && ! $testSendToReceiptAdmin) {
            \Log::info('Newsletter invio a gruppi (riscontro)', [
                'receipt_admin_id' => $newsReceiptAdmin->userID,
                'receipt_admin_name' => $newsReceiptAdmin->nome,
                'groups' => $newsGroupsApplied,
                'chunk_size' => $newsChunkSizeApplied,
                'sent_count' => $sentCount,
                'failed_count' => count($failedEmails),
                'subject' => $request->subject,
                'operator_user_id' => auth()->id(),
            ]);
        }

        if ($testSendToReceiptAdmin && $newsReceiptAdmin) {
            \Log::info('Newsletter invio di prova al responsabile', [
                'receipt_admin_id' => $newsReceiptAdmin->userID,
                'subject' => $request->subject,
                'operator_user_id' => auth()->id(),
                'success' => $failedEmails === [],
            ]);
        }

        if ($failedEmails !== [] && ! $testSendToReceiptAdmin) {
            $message .= ' Invio fallito per '.count($failedEmails).' indirizzi.';
        }

        return redirect()->route('admin.newsletter.create')
            ->with('success', $message)
            ->with('failed_emails', $failedEmails);
    }

    /**
     * Anteprima destinatari di un gruppo newsletter (stesso ordinamento e chunk dell'invio reale).
     */
    public function groupRecipients(Request $request)
    {
        $request->validate([
            'group' => 'required|integer|min:1',
            'news_group_size' => 'required|integer|min:20|max:300',
        ]);

        $chunkSize = max(20, min(300, (int) $request->input('news_group_size')));
        $groupNum = (int) $request->input('group');

        $allNews = User::nonAdmin()
            ->where('abilitato', 1)
            ->where('invia', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('userID')
            ->get(['userID', 'nome', 'username', 'email']);

        if ($allNews->isEmpty()) {
            return response()->json([
                'error' => 'Nessun iscritto alla newsletter con email valida.',
                'recipients' => [],
            ], 404);
        }

        $chunks = $allNews->chunk($chunkSize)->values();
        $maxGroup = $chunks->count();

        if ($groupNum < 1 || $groupNum > $maxGroup) {
            return response()->json([
                'error' => 'Il gruppo '.$groupNum.' non esiste: sono disponibili i gruppi da 1 a '.$maxGroup.'.',
            ], 422);
        }

        $recipients = $chunks[$groupNum - 1]->map(function ($u) {
            return [
                'id' => $u->userID,
                'name' => $u->nome,
                'email' => $u->email,
                'nickname' => $u->username,
            ];
        })->values();

        return response()->json([
            'group' => $groupNum,
            'news_group_size' => $chunkSize,
            'total_groups' => $maxGroup,
            'count' => $recipients->count(),
            'recipients' => $recipients,
        ]);
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
        $newsSubscribers = User::nonAdmin()
            ->where('abilitato', 1)
            ->where('invia', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->count();

        return view('admin.newsletter.stats', compact(
            'totalUsers', 'approvedUsers', 'pendingUsers', 'participants', 'newsSubscribers'
        ));
    }
}
