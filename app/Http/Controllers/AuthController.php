<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // Dual-hash: try bcrypt first, then MD5 with gradual migration
        $authenticated = false;

        if (Hash::check($credentials['password'], $user->password)) {
            // Password is already bcrypt
            $authenticated = true;
        } elseif (md5($credentials['password']) === $user->password) {
            // Password is still MD5 - re-hash to bcrypt for gradual migration
            $user->password = Hash::make($credentials['password']);
            $user->save();
            $authenticated = true;
        }

        if (!$authenticated) {
            return back()->withErrors([
                'username' => 'Credenziali non valide.'
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:20',
            'cognome' => 'required|string|max:20',
            'nickname' => 'required|string|max:20|unique:utente,username',
            'email' => 'required|email|unique:utente,email',
            'password' => 'required|min:8|confirmed',
            'sesso' => 'required|in:m,f',
            'residenza' => 'nullable|string|max:30',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'nome' => $request->name,
            'cognome' => $request->cognome,
            'username' => $request->nickname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'sesso' => $request->sesso,
            'residenza' => $request->residenza ?? '',
            'abilitato' => 0,  // pending
            'ruolo' => 2,      // regular user
        ]);

        return redirect()->route('login')
            ->with('success', 'Registrazione completata! Il tuo account è in attesa di approvazione.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
