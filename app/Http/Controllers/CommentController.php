<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Event;
use App\Models\User;
use App\Support\SafeRichText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CommentController extends Controller
{
    public function store(Request $request, Event $event)
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Devi essere loggato per commentare.');
        }

        if (!Auth::user()->isApproved()) {
            return redirect()->route('home')
                ->with('error', 'Il tuo account deve essere approvato per commentare.');
        }

        $request->validate([
            // Con CKEditor il contenuto include markup HTML, quindi 2000 caratteri risultano spesso troppo pochi.
            'content' => 'required|string|min:5|max:10000',
        ]);

        try {
            $cleanContent = $this->sanitizeHtml($request->content);

            $comment = Comment::create([
                'testo' => $cleanContent,
                'id_evento' => $event->getKey(),
                'id_utente' => Auth::id(),
            ]);

            $this->notifyAdminCommentChange($event, Auth::user(), $comment, 'aggiunto');

            return redirect()->to(url('events/' . $event->getKey()))
                ->with('success', 'Commento aggiunto con successo!')
                ->with('scrollTo', 'comment-' . $comment->id);

        } catch (\Exception $e) {
            \Log::error('Errore creazione commento: ' . $e->getMessage());
            return back()->with('error', 'Errore durante la creazione del commento.');
        }
    }

    /**
     * Risposta admin/organizzatore a un commento nel forum evento (+ email a utente e admin).
     */
    public function reply(Request $request, Comment $comment)
    {
        if (! Auth::check() || ! $this->userCanReplyForumComment(Auth::user())) {
            return back()->with('error', 'Solo amministratori e organizzatori possono inviare risposte dal forum.');
        }

        $comment->loadMissing(['user', 'event']);

        $request->validate([
            'content' => 'required|string|min:2|max:10000',
        ]);

        $event = $comment->event;
        if (! $event) {
            return back()->with('error', 'Evento non trovato per questo commento.');
        }

        try {
            $replyBody = $this->buildAdminReplyHtml($comment, $request->input('content'));
            $cleanContent = $this->sanitizeHtml($replyBody);

            $reply = Comment::create([
                'testo' => $cleanContent,
                'id_evento' => $event->getKey(),
                'id_utente' => Auth::id(),
                'data' => now(),
            ]);

            $this->notifyAdminReply($event, Auth::user(), $comment, $reply);

            return redirect()->to(url('events/' . $event->getKey()))
                ->with('success', 'Risposta pubblicata nel forum. Email inviata all\'utente.')
                ->with('scrollTo', 'comment-' . $reply->getKey());

        } catch (\Throwable $e) {
            \Log::error('Errore risposta admin commento: ' . $e->getMessage());

            return back()->with('error', 'Errore durante l\'invio della risposta.');
        }
    }

    public function edit(Comment $comment)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!$this->userCanEditComment($comment)) {
            return back()->with('error', 'Non autorizzato a modificare questo commento.');
        }

        // In alcuni casi CKEditor salva <img src="upload_immagini/..."> senza slash iniziale.
        // Nella pagina /comments/{id}/edit quel path relativo non si risolve correttamente.
        $editorContent = (string) ($comment->content ?? '');
        if ($editorContent !== '') {
            $editorContent = preg_replace_callback(
                '/\bsrc\s*=\s*(["\'])(?!\/|https?:\/\/|\/\/)([^"\']+)\1/i',
                function (array $m): string {
                    $q = $m[1];
                    $src = ltrim($m[2] ?? '', '/');
                    return 'src=' . $q . '/' . $src . $q;
                },
                $editorContent
            ) ?? $editorContent;
        }

        return view('comments.edit', compact('comment', 'editorContent'));
    }

    public function update(Request $request, Comment $comment)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!$this->userCanEditComment($comment)) {
            return back()->with('error', 'Non autorizzato a modificare questo commento.');
        }

        $request->validate([
            // Con CKEditor il contenuto include markup HTML, quindi 2000 caratteri risultano spesso troppo pochi.
            'content' => 'required|string|min:5|max:10000',
        ]);

        try {
            $cleanContent = $this->sanitizeHtml($request->content);

            $comment->update([
                'testo' => $cleanContent,
                'data' => now(),
                'edited_at' => now(),
            ]);

            return redirect()->to(url('events/' . $comment->event->getKey()))
                ->with('success', 'Commento modificato con successo!')
                ->with('scrollTo', 'comment-' . $comment->id);

        } catch (\Exception $e) {
            \Log::error('Errore modifica commento: ' . $e->getMessage());
            return back()->with('error', 'Errore durante la modifica del commento.');
        }
    }

    public function destroy(Comment $comment)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::id() !== $comment->id_utente && !Auth::user()->isAdmin()) {
            return back()->with('error', 'Non autorizzato a eliminare questo commento.');
        }

        try {
            $event = $comment->event;
            $actor = Auth::user();

            // Se la relazione evento non è risolta per qualche ragione, non bloccare l'eliminazione.
            if ($event) {
                $this->notifyAdminCommentChange($event, $actor, $comment, 'eliminato');
            }

            $comment->delete();

            if ($event) {
                return redirect()->to(url('events/' . $event->getKey()))
                    ->with('success', 'Commento eliminato con successo!');
            }

            return back()->with('success', 'Commento eliminato con successo!');
        } catch (\Throwable $e) {
            \Log::error('Errore eliminazione commento: ' . $e->getMessage());
            return back()->with('error', 'Errore durante l\'eliminazione del commento.');
        }
    }

    /**
     * Autore del commento o amministratore.
     */
    private function userCanEditComment(Comment $comment): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return (int) $user->getKey() === (int) $comment->id_utente || $user->isAdmin();
    }

    private function userCanReplyForumComment(?User $user): bool
    {
        return $user && $user->canManageEvents();
    }

    private function sanitizeHtml(string $content): string
    {
        return SafeRichText::sanitize($content, true);
    }

    private function buildAdminReplyHtml(Comment $parentComment, string $rawReply): string
    {
        $nickname = e($parentComment->user?->nickname ?? 'utente');
        $intro = '<p><em>Risposta a <strong>' . $nickname . '</strong>:</em></p>';
        $text = trim($rawReply);

        if ($text === '') {
            return $intro;
        }

        if (strpos($text, '<') !== false) {
            return $intro . SafeRichText::sanitize($text, true);
        }

        return $intro . '<p>' . nl2br(e($text), false) . '</p>';
    }

    private function resolveNotifyAdmin(): ?User
    {
        $adminId = (int) env('ADMIN_NOTIFY_ADMIN_ID', 0);

        return User::query()
            ->where('ruolo', 0)
            ->when($adminId > 0, fn ($q) => $q->whereKey($adminId))
            ->when($adminId <= 0, fn ($q) => $q->where('username', 'scintilla'))
            ->first();
    }

    private function plainCommentText(Comment $comment): string
    {
        $testo = trim(html_entity_decode(strip_tags($comment->content), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return $testo !== '' ? $testo : '(vuoto o solo formattazione)';
    }

    private function userDisplayLabel(?User $user): string
    {
        if (! $user) {
            return 'Utente';
        }

        $label = trim(($user->nome ?? '') . ' ' . ($user->cognome ?? ''));
        if ($label === '') {
            return $user->nickname ?? ('ID ' . $user->getKey());
        }

        return $label . ' (' . ($user->nickname ?? $user->getKey()) . ')';
    }

    private function notifyAdminReply(Event $event, User $responder, Comment $parentComment, Comment $reply): void
    {
        try {
            $eventUrl = route('events.show', $event);
            $parentAnchor = $eventUrl . '#comment-' . $parentComment->getKey();
            $replyAnchor = $eventUrl . '#comment-' . $reply->getKey();
            $when = optional($event->date)->timezone(config('app.timezone'))->format('d/m/Y H:i');

            $parentAuthor = $parentComment->user;
            $parentText = $this->plainCommentText($parentComment);
            $replyText = $this->plainCommentText($reply);
            $responderUsername = trim((string) ($responder->username ?? ''));
            if ($responderUsername === '') {
                $responderUsername = 'utente';
            }

            $notifyAdmin = $this->resolveNotifyAdmin();
            $adminEmail = trim((string) ($notifyAdmin?->email ?? ''));

            if ($parentAuthor) {
                $userEmail = trim((string) ($parentAuthor->email ?? ''));
                if ($userEmail !== '') {
                    $recipientNick = $parentAuthor->nickname ?? 'utente';

                    $subjectUser = 'Excursio - Risposta al tuo commento (' . $event->title . ')';
                    $bodyUser =
                        "Ciao {$recipientNick},\n\n" .
                        "\"{$responderUsername}\" ha risposto al tuo commento nel forum dell'evento.\n\n" .
                        "Evento: {$event->title}\n" .
                        "Data evento: {$when}\n" .
                        "Link risposta: {$replyAnchor}\n\n" .
                        "Il tuo messaggio:\n" .
                        $parentText . "\n\n" .
                        "Risposta di {$responderUsername}\n" .
                        $replyText . "\n";

                    Mail::raw($bodyUser, function ($message) use ($userEmail, $subjectUser) {
                        $message->to($userEmail)->subject($subjectUser);
                    });
                }
            }

            if ($adminEmail !== '') {
                $subjectAdmin = 'Excursio - Risposta forum evento (' . $responderUsername . ')';
                $bodyAdmin =
                    "Notifica risposta forum evento\n\n" .
                    "Risposta di: {$responderUsername}\n" .
                    "Evento: {$event->title}\n" .
                    "Data evento: {$when}\n" .
                    "Utente: " . $this->userDisplayLabel($parentAuthor) . "\n" .
                    "Link messaggio originale: {$parentAnchor}\n" .
                    "Link risposta: {$replyAnchor}\n\n" .
                    "Messaggio utente:\n" .
                    $parentText . "\n\n" .
                    "Risposta inviata:\n" .
                    $replyText . "\n";

                Mail::raw($bodyAdmin, function ($message) use ($adminEmail, $subjectAdmin) {
                    $message->to($adminEmail)->subject($subjectAdmin);
                });
            }
        } catch (\Throwable $e) {
            \Log::warning('Notify admin reply failed: ' . $e->getMessage());
        }
    }

    private function notifyAdminCommentChange(Event $event, User $actor, Comment $comment, string $action): void
    {
        try {
            $admin = $this->resolveNotifyAdmin();
            $notifyEmail = trim((string) ($admin?->email ?? ''));
            if ($notifyEmail === '') {
                return;
            }

            $eventUrl = route('events.show', $event);
            $commentAnchor = $eventUrl . '#comment-' . $comment->getKey();
            $when = optional($event->date)->timezone(config('app.timezone'))->format('d/m/Y H:i');
            $testoCommento = $this->plainCommentText($comment);

            if ($action === 'aggiunto' || $action === 'eliminato') {
                $adminGreeting = trim((string) ($admin->username ?? 'admin'));
                $userUsername = trim((string) ($actor->username ?? 'utente'));
                $userName = trim(trim((string) ($actor->nome ?? '')) . ' ' . trim((string) ($actor->cognome ?? '')));
                if ($userName === '') {
                    $userName = trim((string) ($actor->nickname ?? $userUsername));
                }

                $actionText = $action === 'aggiunto'
                    ? 'ha scritto in'
                    : 'ha cancellato un commento in';

                $link = $action === 'aggiunto' ? $commentAnchor : $eventUrl;

                $subject = 'Notifica commento evento';
                $body =
                    "Ciao {$adminGreeting}\n\n" .
                    // Inviato solo all'amministratore
                    "{$userUsername} - {$userName}\n" .
                    "{$actionText} {$event->title} {$when}\n" .
                    "{$testoCommento}\n" .
                    "{$link}\n";

                Mail::raw($body, function ($message) use ($notifyEmail, $subject) {
                    $message->to($notifyEmail)->subject($subject);
                });

                return;
            }

            $actorLabel = $this->userDisplayLabel($actor);

            $subject = "Excursio - Commento {$action} (evento)";
            $body =
                "Notifica commenti evento\n\n" .
                "Utente: {$actorLabel}\n" .
                "Azione: {$action}\n" .
                "Evento: {$event->title}\n" .
                "Quando evento: {$when}\n" .
                "ID commento: {$comment->getKey()}\n" .
                "Link commento: {$commentAnchor}\n\n" .
                "Testo del commento:\n" .
                $testoCommento . "\n";

            Mail::raw($body, function ($message) use ($notifyEmail, $subject) {
                $message->to($notifyEmail)->subject($subject);
            });
        } catch (\Throwable $e) {
            \Log::warning('Notify admin comment change failed: ' . $e->getMessage());
        }
    }
}
