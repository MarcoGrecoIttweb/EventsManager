<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use App\Support\SafeRichText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function index()
    {
        $messages = ChatMessage::with('user')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->reverse();

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

        $content = (string) $validated['content'];

        $replyNick = trim((string) ($validated['reply_to_nickname'] ?? ''));
        $replyWhen = trim((string) ($validated['reply_to_when'] ?? ''));
        if ($replyNick !== '') {
            $suffix = $replyWhen !== '' ? (' (' . $replyWhen . ')') : '';
            $prefix = '@ Risponde a ' . $replyNick . $suffix . ' ';
            $content = $prefix . $content;
        }

        if ($isAdmin) {
            $content = SafeRichText::sanitize($content, true);
        }

        ChatMessage::create([
            'user_id' => Auth::id(),
            'content' => $content,
        ]);

        // Evita di auto-notificare l'admin su propri messaggi ricchi.
        if (!$isAdmin) {
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
        if ($isAdmin) {
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
            $adminId = (int) env('ADMIN_NOTIFY_ADMIN_ID', 0);
            $adminUsername = trim((string) env('ADMIN_NOTIFY_ADMIN_USERNAME', ''));

            $admin = User::query()
                ->where('ruolo', 0)
                ->when($adminId > 0, fn ($q) => $q->whereKey($adminId))
                ->when($adminId <= 0 && $adminUsername !== '', fn ($q) => $q->where('username', $adminUsername))
                ->when($adminId <= 0 && $adminUsername === '', fn ($q) => $q->where('username', 'scintilla'))
                ->first();

            $notifyEmail = trim((string) ($admin?->email ?? ''));
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
}

