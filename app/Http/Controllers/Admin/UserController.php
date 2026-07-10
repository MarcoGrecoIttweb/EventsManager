<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLoginEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Evita open-redirect: solo stesso sito o path locale.
     */
    private function safeInternalReturnUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        $root = rtrim(request()->root(), '/');

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $urlNorm = rtrim($url, '/');
            $rootNorm = rtrim($root, '/');
            if ($urlNorm === $rootNorm || str_starts_with($url, $root . '/')) {
                return $url;
            }

            return null;
        }

        if (isset($url[0]) && $url[0] === '/' && ! str_starts_with($url, '//')) {
            return $root . $url;
        }

        return null;
    }

    /**
     * Torna alla lista utenti (vista completa o filtrata) mantenendo scroll e riga.
     */
    private function backToUsersList(
        User $user,
        string $message,
        string $flashKey = 'success',
        ?Request $request = null,
        ?int $highlightUserId = null
    ): RedirectResponse {
        $request = $request ?? request();
        $scrollTop = $request->input('_list_scroll');
        $windowScroll = $request->input('_list_win_scroll');
        $userId = $highlightUserId ?? $user->getKey();

        $target = $this->safeInternalReturnUrl($request->input('_list_return'));
        if ($target === null) {
            $target = route('admin.users.index', $request->only(['status', 'registrations']));
        }

        // Rimuovi eventuale hash: il browser lo interpreta e riporta la pagina all'inizio.
        $target = preg_replace('/#.*$/', '', $target) ?: $target;

        if (is_numeric($scrollTop)) {
            $query = array_filter([
                '_rs' => max(0, (int) $scrollTop),
                '_rw' => is_numeric($windowScroll) ? max(0, (int) $windowScroll) : 0,
                '_ru' => $userId,
            ], static fn ($v) => $v !== null && $v !== '');
            $target .= (str_contains($target, '?') ? '&' : '?') . http_build_query($query);
        }

        return redirect()
            ->to($target)
            ->with($flashKey, $message);
    }

    /**
     * Risposta JSON (AJAX, senza ricaricare pagina) oppure redirect alla lista.
     */
    private function usersListActionResponse(
        Request $request,
        User $user,
        string $message,
        string $flashKey = 'success',
        ?int $highlightUserId = null
    ): JsonResponse|RedirectResponse {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => $flashKey === 'success',
                'message' => $message,
                'status' => $user->status,
                'invia' => (bool) $user->invia,
                'user_id' => $highlightUserId ?? $user->getKey(),
            ]);
        }

        return $this->backToUsersList($user, $message, $flashKey, $request, $highlightUserId);
    }

    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = User::withCount('events');

        if ($request->query('registrations') === 'pending') {
            $query->where('abilitato', 3)->where('ruolo', '!=', 0)
                ->orderBy('userID');
        } elseif ($request->query('status') === 'approved') {
            $query->where('abilitato', 1)
                ->orderBy('userID');
        } elseif ($request->query('status') === 'suspended') {
            $query->where('abilitato', 0)->where('ruolo', '!=', 0)
                ->orderBy('userID');
        } else {
            $query->orderBy('ruolo')
                ->orderBy('abilitato')
                ->orderBy('iscrittodal', 'desc');
        }

        $users = $query->get();

        $awaitingCount  = User::where('abilitato', 3)->count();
        $suspendedCount = User::where('abilitato', 0)->count();
        $approvedCount = User::where('abilitato', 1)->count();
        $bannedCount   = User::where('abilitato', 2)->count();

        return view('admin.users.index', compact(
            'users',
            'awaitingCount',
            'suspendedCount',
            'approvedCount',
            'bannedCount'
        ));
    }

    /**
     * Suggerimenti per il campo «Trova utente» (datalist), in base a nome / cognome / nickname (username).
     */
    public function finderSuggestions(Request $request)
    {
        $field = (string) $request->query('field', 'nome');
        if (!in_array($field, ['nome', 'cognome', 'nickname'], true)) {
            $field = 'nome';
        }

        $term = trim((string) $request->query('q', ''));
        if (mb_strlen($term, 'UTF-8') < 2) {
            return response()->json([]);
        }

        $escaped = addcslashes($term, '%_\\');
        $like = '%' . $escaped . '%';

        if ($field === 'cognome') {
            $column = 'cognome';
        } elseif ($field === 'nickname') {
            $column = 'username';
        } else {
            $column = 'nome';
        }

        $values = User::query()
            ->where($column, 'like', $like)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->orderBy($column)
            ->limit(40)
            ->pluck($column)
            ->map(function ($v) {
                return trim((string) $v);
            })
            ->filter(function ($v) {
                return $v !== '';
            })
            ->unique()
            ->values()
            ->take(15);

        return response()->json($values->all());
    }

    /**
     * Elenco ingressi giornalieri (default 1 giorno, fino a 10).
     */
    public function logins(Request $request)
    {
        $days = (int) $request->query('days', 1);
        if ($days < 1) {
            $days = 1;
        }
        if ($days > 10) {
            $days = 10;
        }

        $since = now()->subDays($days);

        $distinctLaravel = UserLoginEvent::query()
            ->where('logged_in_at', '>=', $since)
            ->where('source', UserLoginEvent::SOURCE_LARAVEL)
            ->distinct()
            ->count('user_id');

        $distinctLegacy = UserLoginEvent::query()
            ->where('logged_in_at', '>=', $since)
            ->where('source', UserLoginEvent::SOURCE_LEGACY)
            ->distinct()
            ->count('user_id');

        $loginByUser = UserLoginEvent::query()
            ->where('logged_in_at', '>=', $since)
            ->selectRaw('user_id, source, MAX(logged_in_at) as last_at')
            ->groupBy('user_id', 'source')
            ->get()
            ->groupBy('user_id');

        $users = User::query()
            ->whereIn('userID', $loginByUser->keys())
            ->get()
            ->map(function (User $user) use ($loginByUser) {
                $rows = $loginByUser->get($user->userID, collect());
                $laravel = $rows->firstWhere('source', UserLoginEvent::SOURCE_LARAVEL);
                $legacy = $rows->firstWhere('source', UserLoginEvent::SOURCE_LEGACY);

                $user->last_login_laravel = $laravel?->last_at;
                $user->last_login_legacy = $legacy?->last_at;

                return $user;
            })
            ->sortByDesc(function (User $user) {
                $laravel = $user->last_login_laravel ? strtotime($user->last_login_laravel) : 0;
                $legacy = $user->last_login_legacy ? strtotime($user->last_login_legacy) : 0;

                return max($laravel, $legacy);
            })
            ->values();

        return view('admin.users.logins', compact(
            'users',
            'days',
            'distinctLaravel',
            'distinctLegacy'
        ));
    }

    /**
     * Display registered users gallery with profile links.
     */
    public function gallery()
    {
        $users = User::orderBy('nome')
            ->orderBy('cognome')
            ->get();

        return view('admin.users.gallery', compact('users'));
    }

    /**
     * Approve a user.
     */
    public function approve(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            return $this->usersListActionResponse($request, $user, 'Non puoi modificare lo stato di un amministratore.', 'error');
        }

        $user->status = 'approved';
        $user->note_utente = null;
        $user->save();

        return $this->usersListActionResponse($request, $user, "Utente {$user->nickname} approvato con successo!");
    }

    /**
     * Ban a user.
     */
    public function ban(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            return $this->backToUsersList($user, 'Non puoi bannare un amministratore.', 'error', $request);
        }

        $user->status = 'banned';
        $user->save();

        return $this->backToUsersList($user, "Utente {$user->nickname} bannato con successo!", 'success', $request);
    }

    /**
     * Unban a user.
     */
    public function unban(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            return $this->backToUsersList($user, 'Non puoi modificare lo stato di un amministratore.', 'error', $request);
        }

        $user->status = 'approved';
        $user->note_utente = null;
        $user->save();

        return $this->backToUsersList($user, "Utente {$user->nickname} sbannato con successo!", 'success', $request);
    }

    /**
     * Suspend an approved user (abilitato 0, distinto dall'iscrizione in attesa = 3).
     */
    public function suspend(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            return $this->usersListActionResponse($request, $user, 'Non puoi modificare lo stato di un amministratore.', 'error');
        }

        $user->status = 'suspended';
        $user->save();

        return $this->usersListActionResponse($request, $user, "Utente {$user->nickname} sospeso con successo!");
    }

    /**
     * Delete a user.
     */
    public function destroy(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            return $this->backToUsersList($user, 'Non puoi eliminare un amministratore.', 'error', $request);
        }

        $userId = (int) $user->getKey();
        $nickname = $user->nickname;
        $user->delete();

        return $this->backToUsersList($user, "Utente {$nickname} eliminato con successo!", 'success', $request, $userId);
    }

    /**
     * Update user role (admin/organizzatore/utente).
     */
    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'ruolo' => 'required|in:0,1,2',
        ]);

        // Non permettere di cambiare il ruolo del proprio account admin a ruoli più bassi per sicurezza minima
        if ($user->isAdmin() && (int) $validated['ruolo'] !== 0) {
            return $this->backToUsersList($user, 'Non puoi modificare il ruolo di un amministratore.', 'error', $request);
        }

        $user->ruolo = (int) $validated['ruolo'];
        $user->save();

        return $this->backToUsersList($user, "Ruolo di {$user->nickname} aggiornato a {$user->role_name}.", 'success', $request);
    }

    /**
     * Mostra una tabella semplice con tutti gli utenti (username, nome, cognome, email, stato attivo).
     */
    public function simpleList()
    {
        $users = User::orderBy('username')->get();

        return view('admin.users.simple-list', compact('users'));
    }

    /**
     * Toggle newsletter subscription (utente.invia) for a user.
     */
    public function updateNewsletter(Request $request, User $user)
    {
        $validated = $request->validate([
            'invia' => 'required|in:0,1',
        ]);

        if ($user->isAdmin()) {
            return $this->usersListActionResponse($request, $user, 'Non puoi modificare la newsletter di un amministratore.', 'error');
        }

        $user->invia = (int) $validated['invia'] === 1;
        $user->save();

        $label = $user->invia ? 'attivata' : 'disattivata';
        return $this->usersListActionResponse($request, $user, "Newsletter {$label} per {$user->nickname}.");
    }
}
