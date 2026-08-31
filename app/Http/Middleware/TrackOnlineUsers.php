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

                // Heartbeat per il "tempo di permanenza" (pagina admin Ingressi giornalieri):
                // aggiorna last_seen_at sull'ultima sessione di login aperta, non più di una
                // volta al minuto per evitare una scrittura ad ogni singola richiesta.
                DB::table('user_login_events')
                    ->where('user_id', $userId)
                    ->whereNull('ended_at')
                    ->where(function ($q) use ($now) {
                        $q->whereNull('last_seen_at')
                            ->orWhere('last_seen_at', '<', date('Y-m-d H:i:s', $now - 60));
                    })
                    ->orderByDesc('logged_in_at')
                    ->limit(1)
                    ->update(['last_seen_at' => date('Y-m-d H:i:s', $now)]);
            }
        } catch (QueryException $e) {
            // MySQL spento, rifiuto connessione, timeout: non bloccare l'intera richiesta
            Log::warning('TrackOnlineUsers: database non disponibile', [
                'message' => $e->getMessage(),
            ]);
        }

        return $next($request);
    }
}
