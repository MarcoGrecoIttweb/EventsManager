<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrackOnlineUsers
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $timeoutMinutes = (int) config('session.online_timeout', 3);
            if ($timeoutMinutes < 1) {
                $timeoutMinutes = 3;
            }

            $now = time();
            $cutoff = $now - ($timeoutMinutes * 60);

            // Sempre: rimuovi record scaduti (anche per visitatori sulla home pubblica)
            DB::table('utentionline')->where('time', '<', $cutoff)->delete();

            if (Auth::check()) {
                $userId = Auth::id();
                $ip = $request->ip();

                // Upsert: aggiorna se esiste già un record per questo utente, altrimenti inserisci
                $exists = DB::table('utentionline')->where('id_utente', $userId)->exists();
                if ($exists) {
                    DB::table('utentionline')
                        ->where('id_utente', $userId)
                        ->update(['time' => $now, 'ip' => $ip]);
                } else {
                    DB::table('utentionline')->insert([
                        'time' => $now,
                        'ip' => $ip,
                        'id_utente' => $userId,
                    ]);
                }

                // Heartbeat + pagine visitate per il "tempo di permanenza" e "pagine visitate"
                // (pagina admin Ingressi giornalieri). Solo per vere pagine viste (GET, non
                // AJAX/JSON), per non gonfiare il conteggio con il polling della chat, ecc.
                $isPageView = $request->isMethod('get') && ! $request->ajax() && ! $request->wantsJson();

                $loginRow = DB::table('user_login_events')
                    ->where('user_id', $userId)
                    ->whereNull('ended_at')
                    ->orderByDesc('logged_in_at')
                    ->first(['id', 'last_seen_at', 'pages_visited']);

                if ($loginRow) {
                    $update = [];

                    // Aggiorna last_seen_at al massimo una volta al minuto (riduce le scritture).
                    $lastSeenTs = $loginRow->last_seen_at ? strtotime($loginRow->last_seen_at) : null;
                    if ($lastSeenTs === null || $lastSeenTs < $now - 60) {
                        $update['last_seen_at'] = date('Y-m-d H:i:s', $now);
                    }

                    if ($isPageView) {
                        $update['page_views_count'] = DB::raw('page_views_count + 1');

                        $label = self::pageLabel($request);
                        $existing = array_filter(array_map('trim', explode(',', (string) $loginRow->pages_visited)));
                        if ($label !== '' && ! in_array($label, $existing, true) && count($existing) < 15) {
                            $existing[] = $label;
                            $update['pages_visited'] = implode(', ', $existing);
                        }
                    }

                    if ($update !== []) {
                        DB::table('user_login_events')->where('id', $loginRow->id)->update($update);
                    }
                }
            }
        } catch (QueryException $e) {
            // MySQL spento, rifiuto connessione, timeout: non bloccare l'intera richiesta
            Log::warning('TrackOnlineUsers: database non disponibile', [
                'message' => $e->getMessage(),
            ]);
        }

        return $next($request);
    }

    /**
     * Etichetta breve e leggibile della pagina corrente, per la colonna "Pagine visitate"
     * della pagina admin Ingressi giornalieri.
     */
    private static function pageLabel(Request $request): string
    {
        $routeName = optional($request->route())->getName();

        $map = [
            'home' => 'Home',
            'events.index' => 'Home',
            'events.show' => 'Evento',
            'events.past' => 'Storico',
            'profile.show' => 'Profilo',
            'profile.edit' => 'Modifica profilo',
            'chat.index' => 'Chat',
            'mercatino.vetrina' => 'Mercatino',
            'mercatino.index' => 'Mercatino',
            'friends.index' => 'Amici',
            'users.index' => 'Community',
            'users.search' => 'Cerca amici',
            'my-events.active' => 'Eventi in programma',
            'photo-albums.index' => 'Galleria foto',
            'search.users' => 'Cerca amici',
            'login' => 'Login',
            'register' => 'Registrati',
        ];

        if ($routeName !== null) {
            if (isset($map[$routeName])) {
                return $map[$routeName];
            }
            if (str_starts_with($routeName, 'admin.')) {
                return 'Admin';
            }
        }

        $segment = trim($request->path(), '/');
        if ($segment === '' || $segment === '/') {
            return 'Home';
        }
        $first = explode('/', $segment)[0];

        return mb_substr(ucfirst($first), 0, 20);
    }
}
