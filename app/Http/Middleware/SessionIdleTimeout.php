<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SessionIdleTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        $minutes = (int) config('session.idle_timeout', 5);
        if ($minutes < 1) {
            $minutes = 5;
        }
        $maxIdleSeconds = $minutes * 60;

        if (Auth::check()) {
            $last = session('last_activity_time');
            $now = time();

            if (is_int($last) && ($now - $last) > $maxIdleSeconds) {
                try {
                    DB::table('utentionline')->where('id_utente', Auth::id())->delete();
                } catch (QueryException $e) {
                    Log::warning('SessionIdleTimeout: impossibile pulire utentionline', [
                        'message' => $e->getMessage(),
                    ]);
                }

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->with(
                        'warning',
                        'Sei stato disconnesso dopo '.$minutes.' minuti di inattività. Accedi di nuovo per continuare.'
                    );
            }
        }

        $response = $next($request);

        if (Auth::check()) {
            session(['last_activity_time' => time()]);
        }

        return $response;
    }
}
