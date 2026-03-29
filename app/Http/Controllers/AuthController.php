<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

        // Check if user is approved
        if (!$user->isApproved()) {
            return back()->withErrors([
                'username' => 'Il tuo account è in attesa di approvazione.'
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        // Update last access
        $user->ultimo_accesso = now();
        $user->save();

        return redirect()->intended(route('home'));
    }

    public function register(Request $request)
    {
        $adultDate = now()->subYears(18)->toDateString();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:20',
            'cognome' => 'required|string|max:20',
            'nickname' => 'required|string|max:20|unique:utente,username',
            'email' => 'required|email|unique:utente,email',
            'password' => 'required|min:8|confirmed',
            'sesso' => 'required|in:m,f',
            'residenza' => 'nullable|string|max:30',
            'datanascita' => 'required|date|before_or_equal:' . $adultDate,
            'telefono' => 'required|string|max:30',
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
            'description' => 'nullable|string|max:65535',
            'privacy_consent' => 'accepted',
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
            'abilitato' => 0,  // pending
            'ruolo' => 2,      // regular user
        ]);

        return redirect()->route('login')
            ->with('success', 'Registrazione completata! Il tuo account è in attesa di approvazione.');
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
