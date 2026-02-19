<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function showForm()
    {
        return view('auth.forgot-password');
    }

    public function sendReset(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string|min:3',
        ]);

        $identifier = $request->input('identifier');

        $user = User::where('username', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (!$user) {
            return back()->with('error', 'Nessun utente trovato con questo username o email.');
        }

        if (!$user->email) {
            return back()->with('error', 'L\'utente non ha un indirizzo email associato.');
        }

        // Generate token and save to password_resets
        $token = Str::random(64);

        DB::table('password_resets')->where('email', $user->email)->delete();
        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Send email with reset link
        $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

        Mail::raw(
            "Ciao {$user->nome},\n\n" .
            "Hai richiesto il reset della password per Excursio.\n\n" .
            "Clicca il seguente link per scegliere una nuova password:\n" .
            "{$resetUrl}\n\n" .
            "Il link scade tra 60 minuti.\n\n" .
            "Se non hai richiesto il reset, ignora questa email.\n\n" .
            "Saluti,\nIl team di Excursio",
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Excursio - Reset password');
            }
        );

        return back()->with('success', 'Ti abbiamo inviato un link per reimpostare la password. Controlla la tua email.');
    }

    public function showResetForm(Request $request)
    {
        return view('auth.reset-password', [
            'token' => $request->token,
            'email' => $request->email,
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Find the reset record
        $record = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return back()->with('error', 'Link di reset non valido o già utilizzato.');
        }

        // Check token validity
        if (!Hash::check($request->token, $record->token)) {
            return back()->with('error', 'Link di reset non valido.');
        }

        // Check expiry (60 minutes)
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_resets')->where('email', $request->email)->delete();
            return back()->with('error', 'Il link di reset è scaduto. Richiedine uno nuovo.');
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->with('error', 'Utente non trovato.');
        }

        $user->update(['password' => Hash::make($request->password)]);

        // Delete used token
        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Password aggiornata con successo! Ora puoi accedere.');
    }
}
