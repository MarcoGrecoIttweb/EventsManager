<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailTestController extends Controller
{
    public function show()
    {
        $user = auth()->user();

        return view('admin.mail-test', [
            'defaultTo' => $user && $user->email ? $user->email : '',
            'mailDriver' => config('mail.default'),
            'mailHost' => config('mail.mailers.smtp.host'),
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'to' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'body' => 'nullable|string|max:5000',
        ]);

        $subject = trim((string) $request->input('subject')) !== ''
            ? $request->input('subject')
            : 'Prova email — '.config('app.name');

        $defaultBody = "Questa è un'email di prova inviata il ".now()->format('d/m/Y H:i:s')." da ".url('/').".\n\n"
            .'Driver configurato: '.config('mail.default').".\n\n"
            ."Se ricevi questo messaggio, l'invio tramite la configurazione attuale (es. SMTP) funziona.";

        $body = trim((string) $request->input('body')) !== ''
            ? $request->input('body')
            : $defaultBody;

        try {
            Mail::raw($body, function ($message) use ($request, $subject) {
                $message->to($request->input('to'))
                    ->subject($subject);
            });

            return redirect()->route('admin.mail-test')
                ->with('success', 'Email di prova inviata a '.$request->input('to').'. Controlla la casella in arrivo e la cartella spam.');
        } catch (\Throwable $e) {
            \Log::error('Mail test fallito: '.$e->getMessage(), ['exception' => $e]);

            return back()
                ->withInput()
                ->withErrors(['mail' => 'Invio non riuscito: '.$e->getMessage()]);
        }
    }
}
