<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use App\Mail\NewsletterMail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NewsletterController extends Controller
{
    /** Dimensione predefinita gruppo newsletter (iscritti News attiva). */
    public const NEWS_GROUP_SIZE_DEFAULT = 80;

    /** Target destinatari ammessi in invio / anteprima / gruppi. */
    private const NEWSLETTER_TARGET_VALUES = [
        'all', 'approved', 'approved_newsletter_off', 'participants',
        'never_participated', 'pending', 'low_participation', 'news', 'newsletter_on',
    ];

    /** Elenco utenti per box statistiche pagina newsletter. */
    private const STAT_RECIPIENT_LIST_KEYS = [
        'all_non_admin', 'approved', 'newsletter_on', 'news_active', 'approved_newsletter_off',
        'participants', 'never_participated', 'pending', 'low_participation',
    ];

    /**
     * Filtro utenti non-admin in base al target (escluso "all": nessun filtro aggiuntivo).
     */
    private static function applyNewsletterTargetFilter($query, string $target): void
    {
        switch ($target) {
            case 'approved':
                $query->where('abilitato', 1);
                break;
            case 'approved_newsletter_off':
                $query->where('abilitato', 1)->where('invia', false);
                break;
            case 'participants':
                $query->where('abilitato', 1)->has('events');
                break;
            case 'never_participated':
                $query->where('abilitato', 1)->doesntHave('events');
                break;
            case 'pending':
                $query->where('abilitato', 3);
                break;
            case 'low_participation':
                $query->where('abilitato', 1)
                    ->withCount('events')
                    ->having('events_count', '<', 2);
                break;
            case 'news':
                $query->where('abilitato', 1)->where('invia', true);
                break;
            case 'newsletter_on':
                $query->where('invia', true);
                break;
        }
    }

    /**
     * Query base per l’elenco utenti collegato a un box «Statistiche destinatari».
     */
    private static function statRecipientsBaseQuery(string $list)
    {
        $query = User::nonAdmin();

        switch ($list) {
            case 'all_non_admin':
                break;
            case 'approved':
                $query->where('abilitato', 1);
                break;
            case 'newsletter_on':
                $query->where('invia', true)
                    ->whereNotNull('email')
                    ->where('email', '!=', '');
                break;
            case 'news_active':
                $query->where('abilitato', 1)
                    ->where('invia', true)
                    ->whereNotNull('email')
                    ->where('email', '!=', '');
                break;
            case 'approved_newsletter_off':
                $query->where('abilitato', 1)
                    ->where('invia', false)
                    ->whereNotNull('email')
                    ->where('email', '!=', '');
                break;
            case 'participants':
                $query->where('abilitato', 1)->has('events');
                break;
            case 'never_participated':
                $query->where('abilitato', 1)->doesntHave('events');
                break;
            case 'low_participation':
                $query->where('abilitato', 1)
                    ->withCount('events')
                    ->having('events_count', '<', 2);
                break;
            case 'pending':
                $query->where('abilitato', 3);
                break;
        }

        return $query;
    }

    public function create()
    {
        $totalUsersCount = User::nonAdmin()->count();
        $usersCount = User::nonAdmin()->where('abilitato', 1)->count();

        // Conteggi con email valida (servono per l'invio a gruppi, per QUALSIASI target).
        $allEmailCount = User::nonAdmin()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->count();
        // Attivati con email valida (tutti, indipendentemente da News)
        $approvedEmailCount = User::nonAdmin()
            ->where('abilitato', 1)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->count();
        // Attivati con email ma newsletter disattivata (invia = false)
        $approvedNewsletterOffCount = User::nonAdmin()
            ->where('abilitato', 1)
            ->where('invia', false)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->count();
        $pendingEmailCount = User::nonAdmin()
            ->where('abilitato', 3)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->count();
        $participantsCount = User::nonAdmin()
            ->where('abilitato', 1)
            ->has('events')
            ->count();
        $neverParticipatedCount = User::nonAdmin()
            ->where('abilitato', 1)
            ->doesntHave('events')
            ->count();

        $lowParticipationSub = DB::table('utente')
            ->where('ruolo', '!=', 0)
            ->where('abilitato', 1)
            ->leftJoin('partecipa', 'utente.userID', '=', 'partecipa.id_utente')
            ->groupBy('utente.userID')
            ->havingRaw('COUNT(partecipa.id_evento) < 2')
            ->select('utente.userID', DB::raw('COUNT(partecipa.id_evento) as ev_count'));
        $lowParticipationStats = DB::query()
            ->fromSub($lowParticipationSub, 't')
            ->selectRaw('COUNT(*) as user_cnt, COALESCE(SUM(ev_count), 0) as participations_sum')
            ->first();
        $lowEventParticipationUsersCount = (int) ($lowParticipationStats->user_cnt ?? 0);
        $lowEventParticipationTotalEvents = (int) ($lowParticipationStats->participations_sum ?? 0);

        $lowParticipationEmailSub = DB::table('utente')
            ->where('ruolo', '!=', 0)
            ->where('abilitato', 1)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->leftJoin('partecipa', 'utente.userID', '=', 'partecipa.id_utente')
            ->groupBy('utente.userID')
            ->havingRaw('COUNT(partecipa.id_evento) < 2')
            ->select('utente.userID');
        $lowParticipationEmailCount = (int) DB::query()->fromSub($lowParticipationEmailSub, 'x')->count();

        $participantsEmailCount = User::nonAdmin()
            ->where('abilitato', 1)
            ->has('events')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->count();
        $neverParticipatedEmailCount = User::nonAdmin()
            ->where('abilitato', 1)
            ->doesntHave('events')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->count();
        $newsSubscribersCount = User::nonAdmin()
            ->where('abilitato', 1)
            ->where('invia', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->count();
        // Newsletter abilitata (invia), indipendentemente da attivato / sospeso / in attesa
        $newsletterOnAnyStatusCount = User::nonAdmin()
            ->where('invia', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->count();

        $targetEmailTotals = [
            'all' => $allEmailCount,
            'approved' => $approvedEmailCount,
            'approved_newsletter_off' => $approvedNewsletterOffCount,
            'participants' => $participantsEmailCount,
            'never_participated' => $neverParticipatedEmailCount,
            'pending' => $pendingEmailCount,
            'low_participation' => $lowParticipationEmailCount,
            'news' => $newsSubscribersCount,
            'newsletter_on' => $newsletterOnAnyStatusCount,
        ];

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

        $newsletterActiveUsers = User::nonAdmin()
            ->where('abilitato', 1)
            ->where('invia', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
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
            'neverParticipatedCount',
            'lowEventParticipationUsersCount',
            'lowEventParticipationTotalEvents',
            'newsSubscribersCount',
            'approvedNewsletterOffCount',
            'newsGroupSizePreview',
            'newsBatchCount',
            'newsBatchesMeta',
            'users',
            'newsletterActiveUsers',
            'newsletterReceiptAdmins',
            'targetEmailTotals',
            'newsletterOnAnyStatusCount'
        ));
    }

    public function send(Request $request)
    {
        $rules = [
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
            'target' => ['required', Rule::in(self::NEWSLETTER_TARGET_VALUES)],
            'news_send' => 'nullable|in:all,groups',
            'news_group_size' => 'nullable|integer|min:20|max:300',
            'news_groups' => 'nullable|array|max:5',
            'news_groups.*' => 'integer|min:1',
            'exclude_newsletter_users' => 'nullable|array|max:500',
            'exclude_newsletter_users.*' => 'integer|exists:utente,userID',
            'newsletter_test_send_to_receipt_admin' => 'nullable|in:0,1',
        ];

        if ($request->input('news_send') === 'groups') {
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

        if ($request->input('news_send') === 'groups') {
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

                $baseQ = User::nonAdmin();
                self::applyNewsletterTargetFilter($baseQ, (string) $request->target);

                $allBase = $baseQ
                    ->whereNotNull('email')
                    ->where('email', '!=', '')
                    ->orderBy('userID')
                    ->get();

                if ($allBase->isEmpty()) {
                    return back()->withErrors(['target' => 'Nessun destinatario con email valida per il filtro scelto.']);
                }

                $chunks = $allBase->chunk($chunkSize)->values();
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
        } elseif ($request->input('news_send') === 'groups') {
            // Se per qualsiasi motivo "groups" non è entrato nel ramo sopra, evita fallback silenzioso.
            return back()->withErrors(['news_send' => 'Seleziona i gruppi da inviare.']);
        } else {
            $usersQuery = User::nonAdmin();
            self::applyNewsletterTargetFilter($usersQuery, (string) $request->target);

            $users = $usersQuery
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->get();
        }

        // Esclusioni manuali (valgono per QUALSIASI target, inclusi selected/approved/news all).
        $excludeIds = array_values(array_unique(array_filter(array_map('intval', $request->input('exclude_newsletter_users', [])))));
        if ($excludeIds !== []) {
            $excludeSet = array_fill_keys($excludeIds, true);
            $users = $users->reject(function ($u) use ($excludeSet) {
                return isset($excludeSet[(int) $u->userID]);
            })->values();
        }

        // Copia obbligatoria all'amministratore Scintilla (stesso username usato altrove nel progetto).
        $scintillaAdmin = User::query()
            ->where('ruolo', 0)
            ->whereRaw('LOWER(username) = ?', ['scintilla'])
            ->first();
        if ($scintillaAdmin && trim((string) $scintillaAdmin->email) !== '') {
            $scintillaId = (int) $scintillaAdmin->userID;
            $alreadyHasScintilla = $users->contains(function ($u) use ($scintillaId) {
                return (int) $u->userID === $scintillaId;
            });
            if (! $alreadyHasScintilla) {
                $users->push($scintillaAdmin);
            }
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
            'target' => ['required', Rule::in(self::NEWSLETTER_TARGET_VALUES)],
            'group' => 'required|integer|min:1',
            'news_group_size' => 'required|integer|min:20|max:300',
        ]);

        $chunkSize = max(20, min(300, (int) $request->input('news_group_size')));
        $groupNum = (int) $request->input('group');
        $target = (string) $request->input('target');

        $q = User::nonAdmin();
        self::applyNewsletterTargetFilter($q, $target);

        $allBase = $q
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('userID')
            ->get(['userID', 'nome', 'username', 'email']);

        if ($allBase->isEmpty()) {
            return response()->json([
                'error' => 'Nessun destinatario con email valida per il filtro scelto.',
                'recipients' => [],
            ], 404);
        }

        $chunks = $allBase->chunk($chunkSize)->values();
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

    /**
     * Anteprima elenco destinatari (per controllo prima dell'invio).
     */
    public function previewRecipients(Request $request)
    {
        $validated = $request->validate([
            'target' => ['required', Rule::in(self::NEWSLETTER_TARGET_VALUES)],
            // Invio a gruppi (per qualunque target)
            'news_send' => ['nullable', 'in:all,groups'],
            'news_group_size' => ['nullable', 'integer', 'min:20', 'max:300'],
            'news_groups' => ['nullable', 'array'],
            'news_groups.*' => ['integer', 'min:1'],
            'exclude_newsletter_users' => ['nullable', 'array'],
            'exclude_newsletter_users.*' => ['integer', Rule::exists('utente', 'userID')],
        ]);

        $target = $validated['target'];
        $users = collect();

        if (($validated['news_send'] ?? 'all') === 'groups') {
            $chunkSize = (int) ($validated['news_group_size'] ?? self::NEWS_GROUP_SIZE_DEFAULT);
            $chunkSize = max(20, min(300, $chunkSize));
            $groupNums = array_values(array_unique(array_filter(array_map('intval', $validated['news_groups'] ?? []))));
            sort($groupNums);

            $q = User::nonAdmin();
            self::applyNewsletterTargetFilter($q, $target);

            $allBase = $q
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->orderBy('userID')
                ->get(['userID', 'nome', 'username', 'email']);

            $chunks = $allBase->chunk($chunkSize)->values();
            $maxGroup = $chunks->count();

            foreach ($groupNums as $num) {
                if ($num < 1 || $num > $maxGroup) {
                    continue;
                }
                $users = $users->concat($chunks[$num - 1]);
            }

            $users = $users->unique('userID')->values();

            $excludeIds = array_values(array_unique(array_filter(array_map('intval', $validated['exclude_newsletter_users'] ?? []))));
            if ($excludeIds !== []) {
                $excludeSet = array_fill_keys($excludeIds, true);
                $users = $users->reject(function ($u) use ($excludeSet) {
                    return isset($excludeSet[(int) $u->userID]);
                })->values();
            }
        } else {
            $q = User::nonAdmin();

            switch ($target) {
                case 'selected':
                    $ids = $validated['selected_users'] ?? [];
                    $q->whereIn('userID', $ids);
                    break;
                case 'selected_news':
                    $ids = $validated['selected_users'] ?? [];
                    $q->where('abilitato', 1)->where('invia', true)->whereIn('userID', $ids);
                    break;
                default:
                    self::applyNewsletterTargetFilter($q, $target);
                    break;
            }

            $users = $q->whereNotNull('email')
                ->where('email', '!=', '')
                ->orderBy('nome')
                ->get(['userID', 'nome', 'username', 'email']);
        }

        // Applica esclusioni manuali anche in anteprima.
        $excludeIds = array_values(array_unique(array_filter(array_map('intval', $validated['exclude_newsletter_users'] ?? []))));
        if ($excludeIds !== []) {
            $excludeSet = array_fill_keys($excludeIds, true);
            $users = $users->reject(function ($u) use ($excludeSet) {
                return isset($excludeSet[(int) $u->userID]);
            })->values();
        }

        // Limite visualizzazione (non influisce sull'invio)
        $total = $users->count();
        $maxShow = 300;
        $shown = $users->take($maxShow)->values()->map(function ($u) {
            return [
                'id' => (int) $u->userID,
                'name' => (string) ($u->nome ?? ''),
                'nickname' => (string) ($u->username ?? ''),
                'email' => (string) ($u->email ?? ''),
            ];
        });

        return response()->json([
            'target_label' => match ($target) {
                'all' => 'Tutti gli utenti',
                'approved' => 'Solo utenti (Attivi)',
                'approved_newsletter_off' => 'Attivi con News (disattiva)',
                'newsletter_on' => 'Solo con News Attiva',
                'participants' => 'Solo utenti che partecipano ad eventi',
                'never_participated' => 'Solo utenti che non hanno mai partecipato ad eventi',
                'pending' => 'Solo utenti in attesa di approvazione',
                'low_participation' => 'Solo utenti attivati con meno di 2 eventi',
                'news' => 'Attivi con News (attiva)',
                'selected' => 'Seleziona utenti specifici',
                'selected_news' => 'Seleziona Utenti Newsletter Attiva',
                default => 'Destinatari newsletter',
            },
            'total' => $total,
            'shown' => $shown,
            'max_show' => $maxShow,
        ]);
    }

    /**
     * Elenco utenti per una voce del box «Statistiche destinatari» (solo admin).
     */
    public function statRecipients(Request $request)
    {
        $validated = $request->validate([
            'list' => ['required', 'string', Rule::in(self::STAT_RECIPIENT_LIST_KEYS)],
        ]);

        $list = $validated['list'];
        $query = self::statRecipientsBaseQuery($list);

        $titles = [
            'all_non_admin' => 'Tutti gli utenti',
            'approved' => 'Solo utenti (Attivi)',
            'newsletter_on' => 'Solo con News Attiva',
            'news_active' => 'Attivi con News (attiva)',
            'approved_newsletter_off' => 'Attivi con News (disattiva)',
            'participants' => 'Solo utenti che partecipano ad eventi',
            'never_participated' => 'Solo utenti che non hanno mai partecipato ad eventi',
            'pending' => 'Solo utenti in attesa di approvazione',
            'low_participation' => 'Attivati con meno di 2 eventi',
        ];

        $total = $query->count();
        $maxRows = 2000;
        $listQuery = self::statRecipientsBaseQuery($list)
            ->orderBy('nome')
            ->orderBy('username')
            ->limit($maxRows);
        if ($list === 'low_participation') {
            $users = $listQuery->get();
        } else {
            $users = $listQuery->get(['userID', 'nome', 'username', 'email', 'abilitato', 'invia']);
        }

        $rows = $users->map(function (User $u) use ($list) {
            $row = [
                'id' => (int) $u->userID,
                'name' => (string) ($u->nome ?? ''),
                'nickname' => (string) ($u->username ?? ''),
                'email' => (string) ($u->email ?? ''),
                'newsletter' => (bool) $u->invia,
                'status' => (string) ($u->status ?? ''),
            ];
            if ($list === 'low_participation') {
                $row['events_count'] = (int) ($u->events_count ?? 0);
            }

            return $row;
        })->values();

        return response()->json([
            'title' => $titles[$list] ?? $list,
            'list' => $list,
            'total' => $total,
            'shown' => $rows->count(),
            'max_rows' => $maxRows,
            'truncated' => $total > $maxRows,
            'users' => $rows,
        ]);
    }

}
