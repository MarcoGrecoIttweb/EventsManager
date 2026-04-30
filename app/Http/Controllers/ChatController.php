<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use App\Support\SafeRichText;
use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    private const REMOVED_NOTICE = 'Messaggio rimosso: è vietato inviare Email e numero di telefono.';

    public function index()
    {
        $messages = ChatMessage::with('user')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->reverse();

        // Moderazione soft: se per qualche motivo esistono già messaggi con contatti,
        // rimuovi il contenuto e sostituiscilo con un avviso (così non viene mai mostrato).
        foreach ($messages as $msg) {
            $raw = (string) ($msg->content ?? '');
            $checkText = strip_tags($raw);
            if ($raw !== '' && $raw !== self::REMOVED_NOTICE && $this->containsForbiddenContacts($checkText)) {
                try {
                    $msg->content = self::REMOVED_NOTICE;
                    $msg->save();
                } catch (\Throwable $e) {
                    \Log::warning('Chat moderation scrub failed: ' . $e->getMessage(), ['message_id' => $msg->id]);
                }
            }
        }

        $mentionAlerts = collect();
        if (Auth::check()) {
            $nick = trim((string) (Auth::user()->nickname ?? Auth::user()->username ?? ''));
            if ($nick !== '') {
                $needle = '@' . mb_strtolower($nick, 'UTF-8');
                $mentionAlerts = $messages
                    ->filter(function (ChatMessage $msg) use ($needle) {
                        if ((int) $msg->user_id === (int) Auth::id()) {
                            return false;
                        }
                        $content = mb_strtolower((string) ($msg->content ?? ''), 'UTF-8');
                        return Str::contains($content, $needle);
                    })
                    ->values();
            }
        }

        // Trova un'immagine header se presente (qualunque estensione)
        $headerImage = null;
        $basePath = public_path('upload_immagini');
        if (is_dir($basePath)) {
            $matches = glob($basePath . DIRECTORY_SEPARATOR . 'chat_header.*');
            if ($matches && isset($matches[0])) {
                $headerImage = 'upload_immagini/' . basename($matches[0]);
            }
        }

        return view('chat.index', [
            'messages' => $messages,
            'headerImage' => $headerImage,
            'mentionAlerts' => $mentionAlerts,
        ]);
    }

    public function store(Request $request)
    {
        $this->middleware('auth');

        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Devi essere registrato per scrivere in chat.');
        }

        $isAdmin = Auth::user()->isAdmin();
        $validated = $request->validate([
            'content' => $isAdmin
                ? 'required|string|max:10000'
                : 'required|string|max:1000',
            'reply_to_nickname' => 'nullable|string|max:255',
            'reply_to_when' => 'nullable|string|max:64',
        ]);

        $originalContent = (string) $validated['content'];
        $content = $originalContent;

        $replyNick = trim((string) ($validated['reply_to_nickname'] ?? ''));
        $replyWhen = trim((string) ($validated['reply_to_when'] ?? ''));
        if ($replyNick !== '') {
            $suffix = $replyWhen !== '' ? (' (' . $replyWhen . ')') : '';
            $prefix = '@ Risponde a ' . $replyNick . $suffix . ' ';
            $content = $prefix . $content;
        }

        // Blocca con rimozione se contiene email o numeri di telefono.
        $checkText = strip_tags($content);
        if ($this->containsForbiddenContacts($checkText)) {
            $content = self::REMOVED_NOTICE;
        } elseif ($isAdmin) {
            $content = SafeRichText::sanitize($content, true);
        }

        $created = ChatMessage::create([
            'user_id' => Auth::id(),
            'content' => $content,
        ]);

        // Se è una risposta, invia email al destinatario (se presente).
        if ($replyNick !== '' && $content !== self::REMOVED_NOTICE) {
            $this->notifyChatReply(Auth::user(), $replyNick, $originalContent, $replyWhen, (int) $created->id);
        }

        // Evita di auto-notificare l'admin su propri messaggi ricchi.
        if (!$isAdmin && $content !== self::REMOVED_NOTICE) {
            $this->notifyAdminChatMessage(Auth::user(), $content);
        }

        return redirect()->route('chat.index');
    }

    public function update(Request $request, ChatMessage $message)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if ((int) $message->user_id !== (int) Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $isAdmin = Auth::user()->isAdmin();
        $validated = $request->validate([
            'content' => $isAdmin
                ? 'required|string|max:10000'
                : 'required|string|max:1000',
        ]);

        $content = (string) $validated['content'];
        $checkText = strip_tags($content);
        if ($this->containsForbiddenContacts($checkText)) {
            $content = self::REMOVED_NOTICE;
        } elseif ($isAdmin) {
            $content = SafeRichText::sanitize($content, true);
        }

        $message->content = $content;
        $message->save();

        return redirect()->route('chat.index')->with('success', 'Messaggio modificato.');
    }

    public function destroy(ChatMessage $message)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if ((int) $message->user_id !== (int) Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $message->delete();

        return redirect()->route('chat.index')->with('success', 'Messaggio eliminato.');
    }

    public function updateHeaderImage(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            // Accetta qualsiasi formato immagine fino a ~10MB
            'header_image' => 'required|image|max:10240',
        ]);

        $file = $request->file('header_image');
        $dest = public_path('upload_immagini');
        if (!is_dir($dest)) {
            @mkdir($dest, 0755, true);
        }

        $filename = 'chat_header.' . strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $file->move($dest, $filename);

        return redirect()->route('chat.index')
            ->with('success', 'Immagine del salotto aggiornata.');
    }

    /**
     * Email all'amministratore (stesso destinatario configurato per eventi/commenti: ADMIN_NOTIFY_ADMIN_ID).
     */
    private function notifyAdminChatMessage(User $author, string $content): void
    {
        try {
            $notifyEmail = $this->resolveAdminNotifyEmail();
            if ($notifyEmail === '') {
                return;
            }

            $when = now()->timezone(config('app.timezone'))->format('d/m/Y H:i');
            $username = $author->nickname ?? (string) $author->getKey();
            $emailUtente = trim((string) ($author->email ?? ''));
            if ($emailUtente === '') {
                $emailUtente = '(non indicata)';
            }

            $nomeCompleto = trim(($author->nome ?? '') . ' ' . ($author->cognome ?? ''));
            if ($nomeCompleto === '') {
                $nomeCompleto = '(non indicato)';
            }

            $chatUrl = route('chat.index');
            $subject = 'Excursio - Nuovo messaggio nella chat';

            $body =
                "Notifica dalla CHAT di Excursio (Il salottino)\n\n" .
                "Chi ha scritto (nome): {$nomeCompleto}\n" .
                "Username: {$username}\n" .
                "Email: {$emailUtente}\n" .
                "Orario messaggio: {$when}\n\n" .
                "Testo del messaggio:\n" .
                $content . "\n\n" .
                "Apri la chat: {$chatUrl}\n";

            Mail::raw($body, function ($message) use ($notifyEmail, $subject) {
                $message->to($notifyEmail)->subject($subject);
            });
        } catch (\Throwable $e) {
            \Log::warning('Notify admin chat message failed: ' . $e->getMessage());
        }
    }

    /**
     * Restituisce l'email admin da notificare (stesse regole usate per eventi/commenti/chat).
     */
    private function resolveAdminNotifyEmail(): string
    {
        try {
            $adminId = (int) env('ADMIN_NOTIFY_ADMIN_ID', 0);
            $adminUsername = trim((string) env('ADMIN_NOTIFY_ADMIN_USERNAME', ''));

            $admin = User::query()
                ->where('ruolo', 0)
                ->when($adminId > 0, fn ($q) => $q->whereKey($adminId))
                ->when($adminId <= 0 && $adminUsername !== '', fn ($q) => $q->where('username', $adminUsername))
                ->when($adminId <= 0 && $adminUsername === '', fn ($q) => $q->where('username', 'scintilla'))
                ->first();

            return trim((string) ($admin?->email ?? ''));
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Notifica via email l'utente destinatario di una risposta in chat.
     * Il destinatario viene risolto partendo dal nickname (o username) mostrato in chat.
     */
    private function notifyChatReply(User $author, string $replyToNick, string $messageBody, string $replyWhen, int $messageId): void
    {
        try {
            $usersEmailEnabled = SiteSettings::getBool('feature.chat_reply_email_users', true);

            $replyToNick = trim($replyToNick);
            if ($replyToNick === '') {
                return;
            }

            $authorNick = trim((string) ($author->nickname ?? $author->username ?? ''));
            if ($authorNick === '') {
                $authorNick = (string) $author->getKey();
            }

            // Nel DB legacy non esiste una colonna "nickname": nickname è un accessor su username.
            // Quindi risolviamo il destinatario usando username (case-insensitive).
            $needle = mb_strtolower($replyToNick, 'UTF-8');
            $recipient = User::query()
                ->whereRaw('LOWER(username) = ?', [$needle])
                ->first();

            // Email destinatario (se risolto e presente).
            $toEmail = '';
            $destNick = $replyToNick;
            if ($recipient && (int) $recipient->getKey() !== (int) $author->getKey()) {
                if ($usersEmailEnabled) {
                    $toEmail = trim((string) ($recipient->email ?? ''));
                }
                $tmpNick = trim((string) ($recipient->nickname ?? $recipient->username ?? ''));
                if ($tmpNick !== '') {
                    $destNick = $tmpNick;
                }
            }

            // Testo pulito "senza" prefissi/HTML.
            $plainBody = strip_tags((string) $messageBody);
            $plainBody = preg_replace('/\s+/', ' ', trim($plainBody)) ?? trim($plainBody);

            $whenSuffix = $replyWhen !== '' ? " ({$replyWhen})" : '';
            $whenNow = now()->timezone(config('app.timezone'))->format('d/m/Y H:i');
            $chatUrl = route('chat.index') . '#msg-' . $messageId;

            $subject = 'Excursio - Nuova risposta nel Salottino';
            $body =
                "Ciao {$destNick},\n\n" .
                "{$authorNick} ti ha risposto nel Salottino{$whenSuffix}.\n" .
                "Orario: {$whenNow}\n\n" .
                "Messaggio:\n" .
                $plainBody . "\n\n" .
                "Apri la chat: {$chatUrl}\n";

            $adminEmail = $this->resolveAdminNotifyEmail();

            // Invia al destinatario (se ha email) e in BCC all'admin.
            if ($toEmail !== '') {
                Mail::raw($body, function ($message) use ($toEmail, $adminEmail, $subject) {
                    $message->to($toEmail)->subject($subject);
                    if (is_string($adminEmail) && trim($adminEmail) !== '' && trim($adminEmail) !== trim($toEmail)) {
                        $message->bcc(trim($adminEmail));
                    }
                });
            } elseif (is_string($adminEmail) && trim($adminEmail) !== '') {
                // Se il destinatario non è risolvibile o non ha email, invia comunque all'admin.
                Mail::raw($body, function ($message) use ($adminEmail, $subject) {
                    $message->to(trim($adminEmail))->subject($subject);
                });
            }
        } catch (\Throwable $e) {
            \Log::warning('Notify chat reply failed: ' . $e->getMessage());
        }
    }

    /**
     * Rileva email o numeri di telefono nel testo (best-effort).
     */
    private function containsForbiddenContacts(string $text): bool
    {
        $text = trim((string) $text);
        if ($text === '') {
            return false;
        }

        // Email (case-insensitive)
        if (preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $text) === 1) {
            return true;
        }

        // Telefono: cerca sequenze con almeno 8 cifre totali.
        // Accetta +39, spazi, trattini, punti, parentesi.
        if (preg_match('/(?:\+?\s*\d[\d\s().\-]{6,}\d)/', $text) === 1) {
            // Conta cifre complessive della prima occorrenza.
            if (preg_match('/(?:\+?\s*\d[\d\s().\-]{6,}\d)/', $text, $m) === 1) {
                $digits = preg_replace('/\D+/', '', $m[0]);
                if (is_string($digits) && strlen($digits) >= 8) {
                    return true;
                }
            }
        }

        return false;
    }
}

