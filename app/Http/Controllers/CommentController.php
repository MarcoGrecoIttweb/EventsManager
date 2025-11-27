<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Store a newly created comment.
     */
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
            'content' => 'required|string|min:5|max:2000',
        ]);

        try {
            $cleanContent = $this->sanitizeHtml($request->content);

            $comment = Comment::create([
                'content' => $cleanContent,
                'event_id' => $event->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('events.show', $event)
                ->with('success', 'Commento aggiunto con successo!')
                ->with('scrollTo', 'comment-' . $comment->id);

        } catch (\Exception $e) {
            \Log::error('Errore creazione commento: ' . $e->getMessage());
            return back()->with('error', 'Errore durante la creazione del commento.');
        }
    }

    /**
     * Show the form for editing the specified comment.
     */
    public function edit(Comment $comment)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Solo il proprietario del commento può modificarlo
        if (Auth::id() !== $comment->user_id) {
            return back()->with('error', 'Non autorizzato a modificare questo commento.');
        }

        return view('comments.edit', compact('comment'));
    }

    /**
     * Update the specified comment.
     */
    public function update(Request $request, Comment $comment)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Solo il proprietario del commento può modificarlo
        if (Auth::id() !== $comment->user_id) {
            return back()->with('error', 'Non autorizzato a modificare questo commento.');
        }

        $request->validate([
            'content' => 'required|string|min:5|max:2000',
        ]);

        try {
            $cleanContent = strip_tags($request->content, '<p><br><strong><em><u><a><ul><ol><li><code>');

            $comment->update([
                'content' => $cleanContent,
                'edited_at' => now(), // Imposta la data di modifica
            ]);

            return redirect()->route('events.show', $comment->event)
                ->with('success', 'Commento modificato con successo!')
                ->with('scrollTo', 'comment-' . $comment->id);

        } catch (\Exception $e) {
            \Log::error('Errore modifica commento: ' . $e->getMessage());
            return back()->with('error', 'Errore durante la modifica del commento.');
        }
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(Comment $comment)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::id() !== $comment->user_id && !Auth::user()->isAdmin()) {
            return back()->with('error', 'Non autorizzato a eliminare questo commento.');
        }

        try {
            $event = $comment->event;
            $comment->delete();
            return redirect()->route('events.show', $event)
                ->with('success', 'Commento eliminato con successo!');
        } catch (\Exception $e) {
            \Log::error('Errore eliminazione commento: ' . $e->getMessage());
            return back()->with('error', 'Errore durante l\'eliminazione del commento.');
        }
    }
    // Aggiungi questo metodo privato nella classe
    private function sanitizeHtml($content)
    {
        // Tag permessi
        $allowedTags = '<p><br><strong><b><em><i><u><a><ul><ol><li><code><pre><span><div>';

        // Rimuovi tag non permessi
        $cleanContent = strip_tags($content, $allowedTags);

        // Rimuovi attributi pericolosi
        $cleanContent = preg_replace('/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i', '<$1$2>', $cleanContent);

        // Sicurezza extra per i link
        $cleanContent = preg_replace_callback('/<a(.*?)>/i', function($matches) {
            // Estrai solo l'href se presente
            if (preg_match('/href=["\']([^"\'<>]*)["\']/i', $matches[1], $hrefMatch)) {
                $href = e($hrefMatch[1]);
                return '<a href="' . $href . '">';
            }
            return '<a>';
        }, $cleanContent);

        return $cleanContent;
    }
}
