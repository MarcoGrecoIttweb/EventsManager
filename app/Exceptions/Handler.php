<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Il token CSRF di una pagina lasciata aperta a lungo non corrisponde più a
        // quello della sessione dopo il timeout per inattività: senza questo handler
        // l'utente vede la pagina grezza "419 | Page Expired" invece di essere
        // reindirizzato al login come avviene già per il timeout rilevato su richieste GET
        // (vedi App\Http\Middleware\SessionIdleTimeout, che qui viene replicato perché
        // VerifyCsrfToken intercetta la richiesta prima che quel middleware possa agire).
        // Nota: il framework converte TokenMismatchException in HttpException(419) dentro
        // prepareException() prima di eseguire questi callback renderable, quindi va
        // intercettata qui come HttpException con status 419, non come TokenMismatchException.
        $this->renderable(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'La sessione è scaduta per inattività. Ricarica la pagina e riprova.',
                ], 419);
            }

            if (Auth::check()) {
                try {
                    DB::table('utentionline')->where('id_utente', Auth::id())->delete();
                } catch (Throwable $ex) {
                    // no-op
                }
                Auth::logout();
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('warning', 'Sei stato disconnesso per inattività. Accedi di nuovo per continuare.');
        });
    }
}
