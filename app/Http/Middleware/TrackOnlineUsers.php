<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrackOnlineUsers
{
    // Minuti di inattività prima di considerare un utente offline
    const TIMEOUT_MINUTES = 15;

    public function handle(Request $request, Closure $next)
    {
        $now = time();
        $cutoff = $now - (self::TIMEOUT_MINUTES * 60);

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
        }

        return $next($request);
    }
}
