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

    private function sanitizeHtml(string $content): string
    {
        return SafeRichText::sanitize($content, true);
    }

    private function notifyAdminCommentChange(Event $event, User $actor, Comment $comment, string $action): void
    {
        try {
            $adminId = (int) env('ADMIN_NOTIFY_ADMIN_ID', 0);
            $admin = User::query()
                ->where('ruolo', 0)
                ->when($adminId > 0, fn ($q) => $q->whereKey($adminId))
                ->when($adminId <= 0, fn ($q) => $q->where('username', 'scintilla'))
                ->first();

            $notifyEmail = trim((string) ($admin?->email ?? ''));
            if ($notifyEmail === '') {
                return;
            }

            $eventUrl = route('events.show', $event);
            $commentAnchor = $eventUrl . '#comment-' . $comment->getKey();

            $when = optional($event->date)->timezone(config('app.timezone'))->format('d/m/Y H:i');

            $actorLabel = trim(($actor->nome ?? '') . ' ' . ($actor->cognome ?? ''));
            if ($actorLabel === '') {
                $actorLabel = $actor->nickname ?? ('ID ' . $actor->getKey());
            } else {
                $actorLabel .= ' (' . ($actor->nickname ?? $actor->getKey()) . ')';
            }

            $testoCommento = trim(html_entity_decode(strip_tags($comment->content), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($testoCommento === '') {
                $testoCommento = '(vuoto o solo formattazione)';
            }

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
