<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use App\Mail\AccountDeletionRequestMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class ProfileController extends Controller
{
    public function myActiveEvents(Request $request)
    {
        $user = $request->user();

        $events = $user->participatingEvents()
            ->paginate(20)
            ->withQueryString();

        return view('profile.my-active-events', compact('events'));
    }

    public function show(Request $request, User $user)
    {
        $this->authorize('view', $user);

        $allParticipatedEvents = $user->events()
            ->where('pubblicato', 1)
            ->orderBy('dataevento', 'desc')
            ->get();

        $profileReturnUrl = $this->safeInternalReturnUrl($request->query('return'));

        return view('profile.show', compact('user', 'allParticipatedEvents', 'profileReturnUrl'));
    }

    /**
     * Evita open-redirect: solo stesso sito (root della richiesta) o path assoluto locale.
     */
    private function safeInternalReturnUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        $root = rtrim(request()->root(), '/');

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $urlNorm = rtrim($url, '/');
            $rootNorm = rtrim($root, '/');
            if ($urlNorm === $rootNorm || strpos($url, $root . '/') === 0) {
                return $url;
            }

            return null;
        }

        if (isset($url[0]) && $url[0] === '/' && strpos($url, '//') !== 0) {
            return $root . $url;
        }

        return null;
    }

    public function edit(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $profileReturnUrl = $this->safeInternalReturnUrl($request->query('return'));

        return view('profile.edit', compact('user', 'profileReturnUrl'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $isAdmin = $request->user() && $request->user()->isAdmin();

        if ($isAdmin) {
            $validated = $request->validate([
                'username' => 'required|string|max:20|unique:utente,username,' . $user->getKey() . ',userID',
                'nome' => 'required|string|max:20',
                'cognome' => 'required|string|max:20',
                // Permetti email duplicate: requisito richiesto (non bloccare se già usata).
                'email' => 'required|email|max:60',
                'telefono' => 'nullable|string|max:30',
                'sesso' => 'required|in:m,f',
                'residenza' => 'nullable|string|max:30',
                'datanascita' => 'nullable|date',
                'description' => 'nullable|string|max:65535',
                'foto_profilo' => ['nullable', 'image', 'max:4096', 'mimes:jpeg,jpg,png,webp,gif'],
            ], [
                'foto_profilo.image' => 'Il file della foto deve essere un’immagine valida.',
                'foto_profilo.max' => 'La foto non può superare 4 MB.',
                'foto_profilo.mimes' => 'Formati ammessi: JPEG, PNG, WebP, GIF.',
            ]);

            $updatePayload = [
                'username' => $validated['username'],
                'nome' => $validated['nome'],
                'cognome' => $validated['cognome'],
                'email' => $validated['email'],
                'telefono' => $validated['telefono'] ?? null,
                'sesso' => $validated['sesso'],
                'residenza' => $validated['residenza'] ?? '',
                'datanascita' => $validated['datanascita'] ?? $user->datanascita,
                'descr' => $validated['description'] ?? '',
            ];

            if ($request->hasFile('foto_profilo')) {
                $file = $request->file('foto_profilo');
                if ($file && $file->isValid()) {
                    $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
                    $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'jpg';
                    $safeNick = preg_replace('/[^a-zA-Z0-9_-]+/', '', (string) $validated['username']) ?: 'user';
                    $avatarFilename = $safeNick . '_' . now()->format('Ymd_His_u') . '.' . $ext;

                    $oldAvatar = $user->avatar;
                    if (is_string($oldAvatar) && $oldAvatar !== '') {
                        $base = basename($oldAvatar);
                        $pub = public_path('upload_avatar/' . $base);
                        if (is_file($pub)) {
                            @unlink($pub);
                        }
                        $stor = storage_path('app/public/photos/' . $base);
                        if (is_file($stor)) {
                            @unlink($stor);
                        }
                    }

                    $dest = public_path('upload_avatar');
                    if (! is_dir($dest)) {
                        @mkdir($dest, 0755, true);
                    }
                    $file->move($dest, $avatarFilename);
                    $updatePayload['avatar'] = $avatarFilename;
                }
            }

            $user->update($updatePayload);
        } else {
            $validated = $request->validate([
                // Permetti email duplicate: requisito richiesto (non bloccare se già usata).
                'email' => 'required|email|max:60',
                'telefono' => 'nullable|string|max:20',
            ]);

            $user->update([
                'email' => $validated['email'],
                'telefono' => $validated['telefono'] ?? null,
            ]);
        }

        $returnUrl = $this->safeInternalReturnUrl($request->input('return'));
        if ($isAdmin && $returnUrl) {
            return redirect($returnUrl)->with('success', 'Profilo aggiornato con successo!');
        }

        return redirect()->route('profile.show', $user)
            ->with('success', 'Profilo aggiornato con successo!');
    }

    /**
     * Imposta la password di un utente (solo amministratori, da pagina profilo).
     * Allineato a AuthController: hash Laravel + MD5 legacy per il sito vecchio.
     */
    public function updatePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:4|confirmed',
        ]);

        $plain = $request->input('password');
        $user->password = md5($plain);
        $user->password_laravel = Hash::make($plain);
        $user->save();

        return redirect()->route('profile.show', $user)
            ->with('success', 'Password aggiornata per l\'utente ' . ($user->username ?: '') . '.');
    }

    /**
     * Cambio password personale (solo proprietario profilo).
     */
    public function updateOwnPassword(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $actor = $request->user();
        $isAdminResettingOther = $actor && $actor->isAdmin() && $actor->getKey() !== $user->getKey();

        if ($isAdminResettingOther) {
            $request->validate([
                'password' => 'required|string|min:4|confirmed',
            ]);
        } else {
            $request->validate([
                'current_password' => 'required|current_password',
                'password' => 'required|string|min:4|confirmed',
            ]);
        }

        $plain = $request->input('password');
        $user->password = md5($plain);
        $user->password_laravel = Hash::make($plain);
        $user->save();

        return redirect()->route('profile.edit', $user)
            ->with('success', 'Password aggiornata.');
    }

    /**
     * Richiesta di cancellazione account da parte dell'utente stesso.
     * Invia una notifica email a tutti gli amministratori.
     */
    public function requestAccountDeletion(Request $request, User $user)
    {
        // Solo il proprietario dell'account può richiedere la propria cancellazione.
        if ((int) $request->user()->getKey() !== (int) $user->getKey()) {
            abort(403);
        }

        $this->authorize('update', $user);

        if ($user->isAdmin()) {
            return back()->with('error', 'Gli amministratori non possono richiedere la cancellazione del proprio account.');
        }

        $usersAdminUrl = URL::route('admin.users.index', [], true);
        $admins = User::query()
            ->where('ruolo', 0)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        $sentCount = 0;
        foreach ($admins as $admin) {
            try {
                Mail::to($admin->email)->send(new AccountDeletionRequestMail($user, $usersAdminUrl));
                $sentCount++;
            } catch (\Throwable $e) {
                \Log::error('Email richiesta cancellazione account (admin) non inviata: ' . $e->getMessage(), [
                    'user_id' => $user->getKey(),
                    'admin_id' => $admin->getKey(),
                    'exception' => $e,
                ]);
            }
        }

        if ($sentCount === 0) {
            return back()->with('error', 'Non è stato possibile inviare la richiesta di cancellazione. Riprova più tardi.');
        }

        return back()->with('success', 'Richiesta di cancellazione account inviata. Un amministratore provvederà al più presto.');
    }

    /**
     * Nota amministratore associata all'utente (solo admin).
     */
    public function updateAdminNote(Request $request, User $user)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validateWithBag('adminNote', [
            'note_utente' => ['nullable', 'string', 'max:2000'],
        ]);

        $note = trim((string) ($validated['note_utente'] ?? ''));
        $user->note_utente = $note !== '' ? $note : null;
        $user->save();

        return redirect()->route('profile.show', $user)
            ->with('success', 'Nota utente aggiornata.');
    }
}
