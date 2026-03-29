<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Event;
use App\Support\SafeRichText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'content' => 'required|string|min:5|max:2000',
        ]);

        try {
            $cleanContent = $this->sanitizeHtml($request->content);

            $comment = Comment::create([
                'testo' => $cleanContent,
                'id_evento' => $event->getKey(),
                'id_utente' => Auth::id(),
            ]);

            return redirect()->route('events.show', $event)
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

        if (Auth::id() !== $comment->id_utente) {
            return back()->with('error', 'Non autorizzato a modificare questo commento.');
        }

        return view('comments.edit', compact('comment'));
    }

    public function update(Request $request, Comment $comment)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::id() !== $comment->id_utente) {
            return back()->with('error', 'Non autorizzato a modificare questo commento.');
        }

        $request->validate([
            'content' => 'required|string|min:5|max:2000',
        ]);

        try {
            $cleanContent = $this->sanitizeHtml($request->content);

            $comment->update([
                'testo' => $cleanContent,
                'edited_at' => now(),
            ]);

            return redirect()->route('events.show', $comment->event)
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
            $comment->delete();
            return redirect()->route('events.show', $event)
                ->with('success', 'Commento eliminato con successo!');
        } catch (\Exception $e) {
            \Log::error('Errore eliminazione commento: ' . $e->getMessage());
            return back()->with('error', 'Errore durante l\'eliminazione del commento.');
        }
    }

    private function sanitizeHtml(string $content): string
    {
        return SafeRichText::sanitize($content, true);
    }
}
