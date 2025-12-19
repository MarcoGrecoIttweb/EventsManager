<?php

namespace App\Http\Controllers;

use App\Models\Decklist;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DecklistController extends Controller
{
    /**
     * Store a newly created decklist.
     */
    public function store(Request $request, Event $event)
    {
        // Solo utenti approvati che partecipano all'evento possono caricare decklist
        if (!Auth::user()->isApproved() || !$event->participants->contains(Auth::id())) {
            abort(403, 'Non autorizzato a caricare decklist per questo evento.');
        }

        $request->validate([
            'decklist' => 'required|file|mimes:pdf|max:10240', // Max 10MB, solo PDF
        ]);

        // Verifica se l'utente ha già una decklist per questo evento
        $existingDecklist = Auth::user()->getDecklistForEvent($event);

        if ($existingDecklist) {
            // Elimina la vecchia decklist
            Storage::disk('public')->delete($existingDecklist->path);
            $existingDecklist->delete();
        }

        // Carica la nuova decklist
        $file = $request->file('decklist');
        $filename = uniqid() . '_' . time() . '.pdf';
        $path = "decklists/{$event->id}/{$filename}";

        Storage::disk('public')->put($path, file_get_contents($file));

        // Crea il record della decklist
        $decklist = Decklist::create([
            'user_id' => Auth::id(),
            'event_id' => $event->id,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return back()->with('success', 'Decklist caricata con successo!');
    }

    /**
     * Display the specified decklist.
     */
    public function show(Decklist $decklist)
    {
        // Solo l'utente proprietario o l'admin possono vedere la decklist
        if (Auth::id() !== $decklist->user_id && !Auth::user()->isAdmin()) {
            abort(403, 'Non autorizzato a visualizzare questa decklist.');
        }

        return response()->file(Storage::disk('public')->path($decklist->path));
    }

    /**
     * Remove the specified decklist.
     */
    public function destroy(Decklist $decklist)
    {
        // Solo l'utente proprietario può eliminare la decklist
        if (Auth::id() !== $decklist->user_id) {
            abort(403, 'Non autorizzato a eliminare questa decklist.');
        }

        Storage::disk('public')->delete($decklist->path);
        $decklist->delete();

        return back()->with('success', 'Decklist eliminata con successo!');
    }
}
