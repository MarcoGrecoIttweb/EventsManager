<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Event;
use App\Models\EventWaitlistEntry;
use App\Support\SafeRichText;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Admin\HomePendingBannerController;
use App\Models\User;
use App\Models\UserLoginEvent;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with(['user', 'participants'])
            ->active()
            ->upcoming()
            ->ordered()
            ->paginate(12);

        $waitlistedEventIds = [];
        $waitlistByEventId = [];
        if (Auth::check() && Auth::user()->isApproved()) {
            $eventIds = $events->getCollection()->pluck('IDevento')->filter()->values();
            if ($eventIds->count() > 0) {
                $waitlistedEventIds = EventWaitlistEntry::query()
                    ->where('user_id', Auth::id())
                    ->whereIn('event_id', $eventIds->all())
                    ->pluck('event_id')
                    ->all();

                $waitlistByEventId = EventWaitlistEntry::query()
                    ->with(['user'])
                    ->whereIn('event_id', $eventIds->all())
                    ->orderBy('created_at')
                    ->get()
                    ->groupBy('event_id')
                    ->all();
            }
        }

        // Box statistiche in home: solo per admin (non esporre numeri a guest/utenti).
        $activeUsersCount = 0;
        $todayVisitsCount = 0;
        $visitVsActivePct = 0.0;
        if (Auth::check() && Auth::user()->isAdmin()) {
            $activeUsersCount = User::query()
                ->nonAdmin()
                ->where('abilitato', 1)
                ->count();

            $todayStart = now()->startOfDay();
            $todayEnd = now()->endOfDay();
            $todayVisitsCount = UserLoginEvent::query()
                ->whereBetween('logged_in_at', [$todayStart, $todayEnd])
                ->count();

            $visitVsActivePct = $activeUsersCount > 0
                ? round(($todayVisitsCount / $activeUsersCount) * 100, 2)
                : 0.0;
        }

        $adminPendingRegistrationBanner = null;
        if (Auth::check() && Auth::user()->isAdmin()) {
            $dismissed = session(HomePendingBannerController::SESSION_DISMISSED_IDS, []);
            if (!is_array($dismissed)) {
                $dismissed = [];
            }
            $dismissed = array_map('intval', $dismissed);
            $pendingForBanner = User::nonAdmin()
                ->where('abilitato', 3)
                ->when(count($dismissed) > 0, static function ($q) use ($dismissed) {
                    $q->whereNotIn('userID', $dismissed);
                })
                ->orderByDesc('userID')
                ->get(['userID', 'username']);
            if ($pendingForBanner->isNotEmpty()) {
                $latest = $pendingForBanner->first();
                $adminPendingRegistrationBanner = [
                    'count' => $pendingForBanner->count(),
                    'latest_username' => (string) ($latest?->username ?? ''),
                    'dismiss_user_ids' => $pendingForBanner->pluck('userID')->implode(','),
                ];
            }
        }

        return view('events.index', compact(
            'events',
            'waitlistedEventIds',
            'waitlistByEventId',
            'activeUsersCount',
            'todayVisitsCount',
            'visitVsActivePct',
            'adminPendingRegistrationBanner'
        ));
    }

    public function pastEvents(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $field = strtolower(trim((string) $request->query('field', 'title')));
        if (! in_array($field, ['title', 'description'], true)) {
            $field = 'title';
        }
        $mine = (string) $request->query('mine', '') === '1';

        $eventsQuery = Event::with(['user', 'participants'])
            ->active()
            ->past()
            ->ordered('desc');

        if ($mine && Auth::check()) {
            $eventsQuery->whereHas('participants', function ($q) {
                $q->where('utente.userID', Auth::id());
            });
        }

        if ($q !== '') {
            $column = $field === 'description' ? 'descrizione' : 'nome';
            $eventsQuery->where($column, 'like', '%' . $q . '%');
        }

        $events = $eventsQuery
            ->paginate(12)
            ->appends($request->query());

        return view('events.past', compact('events', 'q', 'field', 'mine'));
    }

    /**
     * Suggerimenti autocomplete per la ricerca testo in «Eventi passati» (solo admin).
     */
    public function pastSearchSuggestions(Request $request)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403);
        }

        $term = trim((string) $request->query('q', ''));
        $field = strtolower(trim((string) $request->query('field', 'title')));
        if (! in_array($field, ['title', 'description'], true)) {
            $field = 'title';
        }

        if ($term === '' || mb_strlen($term, 'UTF-8') < 2) {
            return response()->json([]);
        }

        $like = '%' . $term . '%';
        $column = $field === 'description' ? 'descrizione' : 'nome';

        $items = Event::query()
            ->active()
            ->past()
            ->select($column)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->where($column, 'like', $like)
            ->distinct()
            ->orderBy($column)
            ->limit(15)
            ->pluck($column)
            ->map(function ($v) {
                $raw = html_entity_decode((string) $v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $t = trim(preg_replace('/\s+/', ' ', strip_tags($raw)) ?? '');

                return $t !== '' ? mb_substr($t, 0, 160) : '';
            })
            ->filter()
            ->unique()
            ->values();

        return response()->json($items);
    }

    public function show(Event $event)
    {
        // Gli admin possono vedere anche eventi disattivati dalla lista gestione eventi.
        if (!$event->is_active && (!Auth::check() || !Auth::user()->isAdmin())) {
            abort(404);
        }

        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Devi essere loggato per vedere i dettagli dell\'evento.');
        }

        // Contatore visite: traccia quante volte la pagina evento è stata aperta dagli utenti,
        // escluse le visite dell'organizzatore dell'evento stesso.
        try {
            $isOrganizerViewing = (int) Auth::id() === (int) $event->id_organizzatore;
            if (!$isOrganizerViewing) {
                $event->increment('visite');
            }
        } catch (\Throwable $e) {
            // Non bloccare la visualizzazione dell'evento se il conteggio fallisce
        }

        $userParticipating = false;
        $comments = collect();
        $isWaitlisted = false;
        $waitlistCount = 0;
        $waitlistEntries = collect();

        // Waitlist: visibile nella pagina evento (se presente), anche se l'utente non è ancora approvato.
        $waitlistEntries = EventWaitlistEntry::query()
            ->with(['user'])
            ->where('event_id', $event->getKey())
            ->orderBy('created_at')
            ->get();
        $waitlistCount = $waitlistEntries->count();

        if (Auth::check() && Auth::user()->isApproved()) {
            // Inserimento automatico immagine "Iscritti-Evento" nel forum:
            // alla prima apertura da parte di un admin, se il forum è vuoto, crea un primo post con l'immagine.
            try {
                if (Auth::user()->isAdmin()) {
                    $hasAnyComments = $event->comments()->exists();
                    if (! $hasAnyComments) {
                        $publicRelPath = 'upload_immagini/ImmagineIscritti-Evento.jpg';
                        $fullPath = public_path($publicRelPath);
                        if (is_file($fullPath)) {
                            $imgUrl = asset($publicRelPath);
                            $html = '<p><img src="' . e($imgUrl) . '" alt="Immagine Iscritti Evento"></p>';
                            $clean = SafeRichText::sanitize($html, true);
                            Comment::create([
                                'testo' => $clean,
                                'id_evento' => $event->getKey(),
                                'id_utente' => Auth::id(),
                            ]);
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Non bloccare la visualizzazione dell'evento se il post automatico fallisce.
                \Log::warning('Auto forum image insert failed: ' . $e->getMessage());
            }

            $userParticipating = $event->participants()
                ->where('utente.userID', Auth::id())
                ->exists();

            $comments = $event->comments()
                ->with('user')
                ->latest('data')
                ->get();

            $isWaitlisted = EventWaitlistEntry::query()
                ->where('event_id', $event->getKey())
                ->where('user_id', Auth::id())
                ->exists();
        }

        return view('events.show', compact('event', 'userParticipating', 'comments', 'isWaitlisted', 'waitlistCount', 'waitlistEntries'));
    }

    public function photoAlbums(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        if (!Auth::user()->isAdmin() && !Auth::user()->isApproved()) {
            return redirect()->route('home')->with('error', 'Devi essere un utente approvato per vedere gli album foto.');
        }

        // Allinea elenco_visibile al valore usato da Laravel su eventi con link galleria (DB condiviso con legacy).
        Event::query()
            ->whereNotNull('url_galleria')
            ->where('url_galleria', '!=', '')
            ->update(['elenco_visibile' => 0]);

        $q = trim((string) $request->query('q', ''));

        $eventsQuery = Event::query()
            ->active()
            ->whereNotNull('url_galleria')
            ->where('url_galleria', '!=', '')
            ->where(function ($q) {
                $q->where('url_galleria', 'like', 'http://%')
                    ->orWhere('url_galleria', 'like', 'https://%');
            })
            ->when($q !== '', function ($qry) use ($q) {
                $qry->where('nome', 'like', '%' . $q . '%');
            })
            ->ordered('desc');

        $events = $eventsQuery
            ->paginate(20)
            ->appends($request->query());

        // L’accessor `google_album_url` filtra gli URL non validi: rimuovi record “sporchi”.
        $events->setCollection(
            $events->getCollection()->filter(function ($e) {
                return (string) ($e->google_album_url ?? '') !== '';
            })->values()
        );

        return view('events.photo-albums', compact('events', 'q'));
    }

    public function destroyPhotoAlbumLink(Event $event)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return redirect()->route('home');
        }

        // Il DB può avere url_galleria NOT NULL: usa stringa vuota per “rimuovere” il link.
        $event->url_galleria = '';
        $event->save();

        return redirect()
            ->route('photo-albums.index')
            ->with('success', 'Link album foto rimosso per: ' . $event->title);
    }

    public function participate(Event $event)
    {
        if (!Auth::check() || !Auth::user()->isApproved()) {
            return redirect()->route('login')
                ->with('error', 'Devi essere un utente approvato per partecipare agli eventi');
        }

        if (!$event->isRegistrationOpen()) {
            return back()->with('error', 'Le iscrizioni a questo evento sono chiuse.');
        }

        if ($event->isFull()) {
            return back()->with('error', 'Evento al completo');
        }

        if ($event->participants()->where('utente.userID', Auth::id())->exists()) {
            return back()->with('error', 'Sei già iscritto a questo evento');
        }

        Auth::user()->events()->attach($event->getKey(), ['amici' => 0]);

        $this->notifyAdminsEventSubscriptionChange($event, Auth::user(), 'iscritto');

        return back()->with('success', 'Iscrizione effettuata con successo');
    }

    public function cancelParticipation(Event $event)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if ($event->is_past_event) {
            return back()->with('error', 'Non puoi annullare l\'adesione a un evento concluso.');
        }

        Auth::user()->events()->detach($event->getKey());

        $this->notifyAdminsEventSubscriptionChange($event, Auth::user(), 'cancellato');

        $this->notifyNextFromWaitlistIfAny($event);

        return back()->with('success', 'Iscrizione annullata con successo');
    }

    /**
     * Invia una comunicazione via email a tutti gli iscritti all'evento.
     * Permessi: admin oppure organizzatore dell'evento.
     */
    public function sendCommunication(Request $request, Event $event)
    {
        if (!Auth::check() || !Auth::user()->isApproved()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $isAllowed = $user->isAdmin() || ((int) $event->id_organizzatore === (int) $user->getKey());
        if (!$isAllowed) {
            abort(403);
        }

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:140'],
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $subject = trim((string) $validated['subject']);
        $body = trim((string) $validated['message']);
        $senderIsAdmin = $user->isAdmin();

        // Destinatari: tutti i partecipanti con email valida.
        $participants = $event->participants()
            ->whereNotNull('utente.email')
            ->where('utente.email', '!=', '')
            ->get(['utente.userID', 'utente.username', 'utente.nome', 'utente.cognome', 'utente.email']);

        $sent = 0;
        $skipped = 0;

        foreach ($participants as $p) {
            $to = trim((string) ($p->email ?? ''));
            if ($to === '') {
                $skipped++;
                continue;
            }

            // Saluto: se invia l'amministratore, non mostrare il cognome (solo nome o nickname).
            if ($senderIsAdmin) {
                $recipientName = trim((string) ($p->nome ?? ''));
                if ($recipientName === '') {
                    $recipientName = (string) ($p->nickname ?? $p->username ?? 'utente');
                }
            } else {
                $recipientName = trim((string) (($p->nome ?? '') . ' ' . ($p->cognome ?? '')));
                if ($recipientName === '') {
                    $recipientName = (string) ($p->nickname ?? $p->username ?? 'utente');
                }
            }

            $eventUrl = route('events.show', $event);
            $eventWhenFormatted = $event->date
                ? $event->date->locale('it')->translatedFormat('l j F') . ' ore ' . $event->date->format('H:i')
                : '';

            $htmlBody = view('emails.event-communication', [
                'subject'       => $subject,
                'eventTitle'    => $event->title,
                'eventWhen'     => $eventWhenFormatted,
                'eventUrl'      => $eventUrl,
                'body'          => $body,
                'recipientName' => $recipientName,
            ])->render();

            try {
                Mail::html($htmlBody, function ($message) use ($to, $subject) {
                    $message->to($to)->subject($subject);
                });
                $sent++;
            } catch (\Throwable $e) {
                $skipped++;
                \Log::warning('Event communication email failed: ' . $e->getMessage(), [
                    'event_id' => $event->getKey(),
                    'to' => $to,
                ]);
            }
        }

        if ($sent === 0) {
            return back()->with('error', 'Nessuna email inviata: nessun iscritto con email valida oppure invio fallito.');
        }

        $msg = "Comunicazione inviata: {$sent} email.";
        if ($skipped > 0) {
            $msg .= " {$skipped} destinatari saltati (email mancante o errore invio).";
        }

        return back()->with('success', $msg);
    }

    public function joinWaitlist(Event $event)
    {
        if (!Auth::check() || !Auth::user()->isApproved()) {
            return redirect()->route('login')
                ->with('error', 'Devi essere un utente approvato per iscriverti alla lista d’attesa.');
        }

        if ($event->is_past_event) {
            return back()->with([
                'error' => 'Non puoi iscriverti alla lista d’attesa di un evento concluso.',
                'waitlist_flash_event_id' => $event->getKey(),
            ]);
        }

        $alreadyParticipant = $event->participants()->where('utente.userID', Auth::id())->exists();
        if ($alreadyParticipant) {
            return back()->with([
                'error' => 'Sei già iscritto a questo evento.',
                'waitlist_flash_event_id' => $event->getKey(),
            ]);
        }

        $alreadyWaitlisted = EventWaitlistEntry::query()
            ->where('event_id', $event->getKey())
            ->where('user_id', Auth::id())
            ->exists();

        if ($alreadyWaitlisted) {
            return back()->with([
                'success' => 'Sei già nella lista d’attesa.',
                'waitlist_flash_event_id' => $event->getKey(),
            ]);
        }

        EventWaitlistEntry::query()->create([
            'event_id' => $event->getKey(),
            'user_id' => Auth::id(),
            'email' => Auth::user()->email ?? null,
            'display_name' => Auth::user()->nickname ?? trim((Auth::user()->nome ?? '') . ' ' . (Auth::user()->cognome ?? '')) ?: null,
            'status' => 'pending',
            'notified_at' => null,
        ]);

        return back()->with([
            'success' => 'Perfetto: ti ho messo in lista d’attesa. Se si libera un posto, ti avvisiamo via email.',
            'waitlist_flash_event_id' => $event->getKey(),
        ]);
    }

    public function leaveWaitlist(Event $event)
    {
        if (!Auth::check() || !Auth::user()->isApproved()) {
            return redirect()->route('login');
        }

        EventWaitlistEntry::query()
            ->where('event_id', $event->getKey())
            ->where('user_id', Auth::id())
            ->delete();

        return back()->with([
            'success' => 'Ok: sei stato rimosso dalla lista d’attesa.',
            'waitlist_flash_event_id' => $event->getKey(),
        ]);
    }

    private function notifyAdminsEventSubscriptionChange(Event $event, User $actor, string $action): void
    {
        try {
            $adminId = (int) env('ADMIN_NOTIFY_ADMIN_ID', 0);
            $adminUsername = trim((string) env('ADMIN_NOTIFY_ADMIN_USERNAME', ''));

            $admin = User::query()
                ->where('ruolo', 0)
                ->when($adminId > 0, fn ($q) => $q->whereKey($adminId))
                ->when($adminId <= 0 && $adminUsername !== '', fn ($q) => $q->where('username', $adminUsername))
                ->when($adminId <= 0 && $adminUsername === '', fn ($q) => $q->where('username', 'scintilla'))
                ->first();

            $notifyEmail = trim((string) ($admin?->email ?? ''));
            if ($notifyEmail === '') {
                return;
            }

            $eventUrl = route('events.show', $event);
            $when = optional($event->date)->timezone(config('app.timezone'))->format('d/m/Y H:i');

            if ($action === 'iscritto' || $action === 'cancellato') {
                $adminGreeting = trim((string) ($admin->username ?? 'admin'));
                $userUsername = trim((string) ($actor->username ?? 'utente'));
                $userName = trim(trim((string) ($actor->nome ?? '')) . ' ' . trim((string) ($actor->cognome ?? '')));
                if ($userName === '') {
                    $userName = trim((string) ($actor->nickname ?? $userUsername));
                }

                $actionText = $action === 'iscritto'
                    ? 'si è iscritto ad evento'
                    : 'si è cancellato dall\'evento';

                $subject = 'Notifica iscrizioni evento';
                $body =
                    "Ciao {$adminGreeting}\n\n" .
                    "{$userUsername} - {$userName} {$actionText} {$event->title} {$when} {$eventUrl}\n";

                Mail::raw($body, function ($message) use ($notifyEmail, $subject) {
                    $message->to($notifyEmail)->subject($subject);
                });

                return;
            }

            $actorLabel = trim(($actor->nome ?? '') . ' ' . ($actor->cognome ?? ''));
            if ($actorLabel === '') {
                $actorLabel = $actor->nickname ?? ('ID ' . $actor->getKey());
            } else {
                $actorLabel .= ' (' . ($actor->nickname ?? $actor->getKey()) . ')';
            }

            $subject = "Excursio - Evento: utente {$action}";
            $body =
                "Notifica iscrizioni evento\n\n" .
                "Utente: {$actorLabel}\n" .
                "Azione: {$action}\n" .
                "Evento: {$event->title}\n" .
                "Quando: {$when}\n" .
                "Link evento: {$eventUrl}\n";

            Mail::raw($body, function ($message) use ($notifyEmail, $subject) {
                $message->to($notifyEmail)->subject($subject);
            });
        } catch (\Throwable $e) {
            // Non bloccare il flusso di iscrizione/cancellazione se la mail fallisce.
            \Log::warning('Notify admins subscription change failed: ' . $e->getMessage());
        }
    }

    private function notifyNextFromWaitlistIfAny(Event $event): void
    {
        try {
            if (!$event->isRegistrationOpen()) {
                return;
            }
            if ($event->isFull()) {
                return;
            }

            $entry = EventWaitlistEntry::query()
                ->where('event_id', $event->getKey())
                ->where('status', 'pending')
                ->orderBy('created_at')
                ->first();

            if (!$entry) {
                return;
            }

            $user = User::query()->whereKey($entry->user_id)->first();
            $to = trim((string) ($user?->email ?? $entry->email ?? ''));
            if ($to === '') {
                return;
            }

            $eventUrl = route('events.show', $event);
            $when = optional($event->date)->timezone(config('app.timezone'))->format('d/m/Y H:i');
            $subject = 'Excursio - Si è liberato un posto!';
            $body =
                "Ciao!\n\n" .
                "Buone notizie: si è liberato un posto per questo evento:\n" .
                "{$event->title}\n" .
                "Quando: {$when}\n\n" .
                "Vai alla pagina evento e iscriviti appena puoi:\n" .
                "{$eventUrl}\n\n" .
                "A presto,\nExcursio\n";

            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });

            $entry->status = 'notified';
            $entry->notified_at = now();
            $entry->save();
        } catch (\Throwable $e) {
            \Log::warning('Notify waitlist failed: ' . $e->getMessage());
        }
    }

    public function printParticipants(Event $event)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $participants = $event->participants()
            ->orderBy('cognome')
            ->orderBy('nome')
            ->get();

        $printDescriptionHtml = $event->print_description_html;
        if ($printDescriptionHtml === '') {
            $fallback = trim((string) $event->safe_description_no_images);
            if ($fallback !== '') {
                $printDescriptionHtml = $fallback;
            } elseif (trim((string) ($event->incipit ?? '')) !== '') {
                $printDescriptionHtml = '<p>' . e($event->incipit) . '</p>';
            }
        }

        return view('events.print', compact('event', 'participants', 'printDescriptionHtml'));
    }
}
