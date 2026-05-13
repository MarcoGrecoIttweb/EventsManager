<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Mail\NewRegistrationAdminMail;
use App\Mail\RegistrationPendingUserMail;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required'
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if (!$user) {
            return back()->withErrors([
                'username' => 'Credenziali non valide.'
            ]);
        }

        // Dual-hash: controlla password_laravel (bcrypt), poi password (bcrypt legacy o MD5)
        $authenticated = false;

        if ($user->password_laravel && Hash::check($credentials['password'], $user->password_laravel)) {
            // Utente già migrato: bcrypt in password_laravel
            $authenticated = true;
        } elseif (Hash::check($credentials['password'], $user->password)) {
            // Bcrypt già in password (migrati dalla vecchia logica): sposta in password_laravel
            // e ripristina MD5 in password per il sito legacy
            $user->password = md5($credentials['password']);
            $user->password_laravel = Hash::make($credentials['password']);
            $user->save();
            $authenticated = true;
        } elseif (md5($credentials['password']) === $user->password) {
            // Password legacy MD5: salva bcrypt in password_laravel senza toccare password
            $user->password_laravel = Hash::make($credentials['password']);
            $user->save();
            $authenticated = true;
        }

        if (!$authenticated) {
            return back()->withErrors([
                'username' => 'Credenziali non valide.'
            ]);
        }

        // Blocco accesso per account bannati/disattivati
        if ($user->isBanned()) {
            return back()->withErrors([
                'username' => 'Il tuo account è stato disattivato. Contatta un amministratore.'
            ]);
        }

        if ($user->isAwaitingApproval()) {
            return back()->withErrors([
                'username' => 'Il tuo account è in attesa di approvazione da un amministratore.',
            ]);
        }

        if ($user->isSuspended()) {
            return back()->withErrors([
                'username' => 'Il tuo account è stato sospeso. Contatta un amministratore.',
            ]);
        }

        if (!$user->isApproved()) {
            return back()->withErrors([
                'username' => 'Non puoi accedere con questo account. Contatta un amministratore.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('last_activity_time', time());

        // Update last access
        $now = now();
        $user->ultimo_accesso = $now;
        $user->save();

        // Storico accessi: registra ogni ingresso (anche multipli nello stesso giorno)
        try {
            \App\Models\UserLoginEvent::create([
                'user_id' => $user->getKey(),
                'logged_in_at' => $now,
                'ip_address' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            // In caso di errore sul log, non bloccare il login utente
        }

        return redirect()->route('home');
    }

    public function register(Request $request)
    {
        $request->merge([
            'name' => trim((string) $request->input('name', '')),
            'cognome' => trim((string) $request->input('cognome', '')),
            'nickname' => trim((string) $request->input('nickname', '')),
            'email' => mb_strtolower(trim((string) $request->input('email', '')), 'UTF-8'),
            'residenza' => trim((string) $request->input('residenza', '')),
            'telefono' => trim((string) $request->input('telefono', '')),
        ]);

        $adultDate = now()->subYears(18)->toDateString();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:20',
            'cognome' => 'required|string|max:20',
            'nickname' => 'required|string|max:20|unique:utente,username',
            'email' => ['required', 'email', 'max:255', Rule::unique('utente', 'email')],
            'password' => 'required|min:4|confirmed',
            'sesso' => 'required|in:m,f',
            'residenza' => 'nullable|string|max:30',
            'datanascita' => 'required|date|before_or_equal:' . $adultDate,
            'telefono' => 'required|string|max:30',
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
            'description' => 'nullable|string|max:65535',
            'privacy_consent' => 'accepted',
        ], [
            'nickname.unique' => 'Questo nickname è già usato nel database (anche da un account ancora in attesa di approvazione o non abilitato). Scegline un altro o chiedi a un amministratore di controllare in «Gestione utenti» le iscrizioni in attesa.',
            'email.unique' => 'Questa email è già registrata (anche se il profilo non è ancora stato abilitato). Se sei tu, prova ad accedere o usa il recupero password; altrimenti usa un altro indirizzo email.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $avatarFilename = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $safeNick = preg_replace('/[^a-zA-Z0-9_-]+/', '', (string) $request->nickname) ?: 'user';
            $avatarFilename = $safeNick . '_' . now()->format('Ymd_His_u') . '.' . $ext;
            $dest = public_path('upload_avatar');
            if (!is_dir($dest)) {
                @mkdir($dest, 0755, true);
            }
            $file->move($dest, $avatarFilename);
        }

        $user = User::create([
            'nome' => $request->name,
            'cognome' => $request->cognome,
            'username' => $request->nickname,
            'email' => $request->email,
            'password' => '',
            'password_laravel' => Hash::make($request->password),
            'sesso' => $request->sesso,
            'residenza' => $request->residenza ?? '',
            'datanascita' => $request->datanascita,
            'telefono' => $request->telefono ?? '',
            'avatar' => $avatarFilename,
            'descr' => $request->description ?? '',
            'abilitato' => 3, // in attesa di approvazione (diverso da sospeso = 0)
            'ruolo' => 2,      // regular user
        ]);

        $userMailOk = false;
        $email = trim((string) $user->email);
        if ($email !== '') {
            try {
                Mail::to($email)->send(new RegistrationPendingUserMail($user));
                $userMailOk = true;
            } catch (\Throwable $e) {
                \Log::error('Email registrazione (utente) non inviata: '.$e->getMessage(), [
                    'user_id' => $user->getKey(),
                    'exception' => $e,
                ]);
            }
        }

        $usersAdminUrl = URL::route('admin.users.index', [], true);
        $admins = User::query()
            ->where('ruolo', 0)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        foreach ($admins as $admin) {
            try {
                Mail::to($admin->email)->send(new NewRegistrationAdminMail($user, $usersAdminUrl));
            } catch (\Throwable $e) {
                \Log::error('Email registrazione (admin) non inviata: '.$e->getMessage(), [
                    'new_user_id' => $user->getKey(),
                    'admin_id' => $admin->getKey(),
                    'exception' => $e,
                ]);
            }
        }

        $redirect = redirect()->route('login')
            ->with(
                'success',
                'Registrazione completata! Il tuo account resta in attesa di approvazione da un amministratore: '
                .'non potrai accedere finché non sarà stato abilitato. '
                .($userMailOk
                    ? 'Ti abbiamo inviato un\'email di riepilogo all\'indirizzo indicato.'
                    : 'Se non ricevi un\'email di conferma, controlla lo spam o riprova più tardi: la registrazione è comunque stata salvata.')
            );

        return $redirect;
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();
        if ($userId) {
            DB::table('utentionline')->where('id_utente', $userId)->delete();
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
