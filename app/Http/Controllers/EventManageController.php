<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventImage;
use App\Services\ImageService;
use App\Support\AdminNotifier;
use App\Support\EventGreetingSettings;
use App\Support\EventoTableSchema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EventManageController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index()
    {
        $user = Auth::user();

        $query = Event::with(['user', 'participants']);

        // Organizers see only their own events, admins see all
        if (!$user->isAdmin()) {
            $query->where('id_organizzatore', $user->getKey());
        }

        // In "Gestione eventi" non mostrare eventi scaduti: solo eventi futuri.
        $query->whereNotNull('dataevento')->where('dataevento', '>', now());

        $events = $query->orderBy('dataevento', 'desc')->paginate(12);

        return view('manage.events.index', compact('events'));
    }

    public function create()
    {
        return view('manage.events.create');
    }

    public function store(Request $request)
    {
        // Supporto UI con campi separati data/ora: compone `date` (datetime) che finirà in `dataevento`.
        if (!$request->filled('date') && $request->filled('date_only') && $request->filled('time_only')) {
            $request->merge([
                'date' => trim((string) $request->input('date_only')) . ' ' . trim((string) $request->input('time_only')),
            ]);
        }

        $request->merge([
            'google_album_url' => ($g = trim((string) $request->input('google_album_url', ''))) !== '' ? $g : null,
        ]);

        $user = Auth::user();
        if ($user->isAdmin() && ($migrationError = EventGreetingSettings::migrationGuardError($request))) {
            return back()->withErrors($migrationError)->withInput();
        }

        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'incipit' => 'nullable|string|max:500',
            'description' => 'required|string|min:10',
            'date' => 'required|date|after:now',
            'city' => 'required|string|max:35',
            'venue' => 'nullable|string|max:35',
            'address' => 'required|string|max:35',
            'civico' => 'nullable|string|max:10',
            'cost' => 'nullable|numeric|min:0',
            'deadline' => 'nullable|date',
            'is_active' => 'sometimes|boolean',
            'max_participants' => 'nullable|integer|min:1',
            'allow_guests' => 'sometimes|boolean',
            'max_guests_per_user' => 'nullable|integer|min:1|max:10',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,heic,heif|max:4096',
            'google_album_url' => 'nullable|string|max:2048|url',
        ] + ($user->isAdmin() ? EventGreetingSettings::validationRules() : []));

        $allowGuests = $request->has('allow_guests');
        $isActive = $request->has('is_active') ? 1 : 0;

        // Forza il titolo evento in MAIUSCOLO
        $validated['title'] = Str::upper((string) $validated['title']);

        $event = Event::create(EventoTableSchema::filter([
            'nome' => $validated['title'],
            'incipit' => $validated['incipit'] ?? null,
            'descrizione' => $validated['description'],
            'dataevento' => $validated['date'],
            'citta' => $validated['city'],
            'dove' => $validated['venue'] ?? '',
            'via' => $validated['address'],
            'civico' => (string) ($validated['civico'] ?? ''),
            'costo' => $validated['cost'] ?? null,
            'numeromax' => $validated['max_participants'] ?? null,
            'id_organizzatore' => Auth::id(),
            'pubblicato' => $isActive,
            // Valore fisso per DB condiviso con sito legacy (non c’è toggle in form Laravel)
            'elenco_visibile' => 0,
            'sondaggio' => '',
            'url_galleria' => (string) ($validated['google_album_url'] ?? ''),
            'datascadenza' => $validated['deadline'] ?? $validated['date'],
            'allow_guests' => $allowGuests,
            'max_guests_per_user' => $allowGuests ? ($validated['max_guests_per_user'] ?? 3) : 0,
        ] + ($user->isAdmin() ? EventGreetingSettings::payloadFromRequest($request) : [])));

        if ($request->hasFile('cover_image')) {
            $coverResult = $this->imageService->uploadCoverImage(
                $request->file('cover_image'),
                $event->getKey()
            );
            if ($coverResult['success']) {
                $event->update(['immagine' => $coverResult['filename']]);
            }
        }

        return redirect()->route('manage.events.index')
            ->with('success', 'Evento creato con successo!');
    }

    public function edit(Event $event)
    {
        $this->authorizeEvent($event);
        $event->load('images');
        return view('manage.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        // Supporto UI con campi separati data/ora: compone `date` (datetime) che finirà in `dataevento`.
        if (!$request->filled('date') && $request->filled('date_only') && $request->filled('time_only')) {
            $request->merge([
                'date' => trim((string) $request->input('date_only')) . ' ' . trim((string) $request->input('time_only')),
            ]);
        }

        if ($request->input('cover_image_selected') == '1' && !$request->hasFile('cover_image')) {
            return back()->with('error', 'La nuova copertina non è stata ricevuta dal server. Probabile file troppo grande o limite PHP (upload_max_filesize / post_max_size). Prova con un file più piccolo.');
        }

        $request->merge([
            'google_album_url' => ($g = trim((string) $request->input('google_album_url', ''))) !== '' ? $g : null,
        ]);

        if (Auth::user()->isAdmin() && ($migrationError = EventGreetingSettings::migrationGuardError($request))) {
            return back()->withErrors($migrationError)->withInput();
        }

        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'incipit' => 'nullable|string|max:500',
            'description' => 'required|string|min:10',
            'date' => 'required|date',
            'city' => 'required|string|max:35',
            'venue' => 'nullable|string|max:35',
            'address' => 'required|string|max:35',
            'civico' => 'nullable|string|max:10',
            'cost' => 'nullable|numeric|min:0',
            'deadline' => 'nullable|date',
            'max_participants' => 'nullable|integer|min:1',
            'allow_guests' => 'sometimes|boolean',
            'max_guests_per_user' => 'nullable|integer|min:1|max:10',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'cover_image_selected' => 'nullable|in:0,1',
            'google_album_url' => 'nullable|string|max:2048|url',
        ] + (Auth::user()->isAdmin() ? EventGreetingSettings::validationRules() : []));

        $user = Auth::user();
        $allowGuests = $request->has('allow_guests');
        $wasPastEvent = $event->is_past_event;

        // Forza il titolo evento in MAIUSCOLO
        $validated['title'] = Str::upper((string) $validated['title']);

        $updateData = [
            'nome' => $validated['title'],
            'incipit' => $validated['incipit'] ?? null,
            'descrizione' => $validated['description'],
            'dataevento' => $validated['date'],
            'citta' => $validated['city'],
            'dove' => $validated['venue'] ?? '',
            'via' => $validated['address'],
            'civico' => (string) ($validated['civico'] ?? ''),
            'costo' => $validated['cost'] ?? null,
            'datascadenza' => $validated['deadline'] ?? $validated['date'],
            'elenco_visibile' => 0,
            'numeromax' => $validated['max_participants'] ?? null,
            'allow_guests' => $allowGuests,
            'max_guests_per_user' => $allowGuests ? ($validated['max_guests_per_user'] ?? 3) : 0,
            'url_galleria' => (string) ($validated['google_album_url'] ?? ''),
        ] + (Auth::user()->isAdmin() ? EventGreetingSettings::payloadFromRequest($request) : []);

        // Pubblicazione / disattivazione evento gestita dallo switch "Evento attivo",
        // ma un evento con data passata/non futura non può risultare "attivo/pubblicato".
        $newDate = \Carbon\Carbon::parse($validated['date']);
        $updateData['pubblicato'] = ($newDate->gt(now()) && $request->has('is_active')) ? 1 : 0;

        if ($request->has('remove_cover') && $event->immagine) {
            // Supporta sia copertine legacy (public/upload_immagini) sia copertine in storage/events/{id}
            $this->imageService->deleteCoverImage($event->immagine);
            $disk = \Illuminate\Support\Facades\Storage::disk('public');
            if (str_contains($event->immagine, '/')) {
                $disk->delete($event->immagine);
            } else {
                $disk->delete('events/' . $event->getKey() . '/' . $event->immagine);
            }
            $updateData['immagine'] = null;
            $event->immagine = null;
        }

        if ($request->hasFile('cover_image')) {
            if ($event->immagine) {
                $this->imageService->deleteCoverImage($event->immagine);
                $disk = \Illuminate\Support\Facades\Storage::disk('public');
                if (str_contains($event->immagine, '/')) {
                    $disk->delete($event->immagine);
                } else {
                    $disk->delete('events/' . $event->getKey() . '/' . $event->immagine);
                }
            }
            $coverResult = $this->imageService->uploadCoverImage(
                $request->file('cover_image'),
                $event->getKey()
            );
            if (!$coverResult['success']) {
                return back()->with('error', 'Caricamento copertina fallito: ' . ($coverResult['error'] ?? 'errore sconosciuto'));
            }
            $updateData['immagine'] = $coverResult['filename'];
        }

        if ($wasPastEvent && $newDate->gt(now())) {
            try {
                $deadlineAt = \Carbon\Carbon::parse($updateData['datascadenza']);
                if ($deadlineAt->lte(now())) {
                    $updateData['datascadenza'] = $newDate->format('Y-m-d H:i:s');
                }
            } catch (\Throwable $e) {
                $updateData['datascadenza'] = $newDate->format('Y-m-d H:i:s');
            }
        }

        try {
            $event->update(EventoTableSchema::filter($updateData));
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->with('error', 'Salvataggio non riuscito. Se il problema persiste, sul server esegui: php artisan excursio:sync-database')
                ->withInput();
        }

        $success = 'Evento aggiornato con successo!';
        if ($wasPastEvent && $newDate->gt(now())) {
            $success = 'Evento aggiornato: con la nuova data futura è di nuovo visibile in homepage (se pubblicato).';
        }

        // Usa la root della request (es. "http://localhost/excursio/public") per evitare 404 Apache
        // quando l'app è servita da una sottocartella.
        $publicUrl = rtrim($request->root(), '/') . route('events.show', $event, false);

        return redirect()->to($publicUrl)
            ->with('success', $success);
    }

    public function destroy(Event $event)
    {
        $this->authorizeEvent($event);

        $actor = Auth::user();
        if ($actor) {
            AdminNotifier::notifyEventDeleted($event, $actor);
        }

        $this->imageService->deleteEventFolder($event->getKey());
        $event->delete();

        return redirect()->route('manage.events.index')
            ->with('success', 'Evento eliminato con successo!');
    }

    /**
     * Attiva/disattiva un evento nella lista gestione eventi.
     */
    public function toggleStatus(Event $event)
    {
        $this->authorizeEvent($event);

        // Non attivare eventi non futuri (in teoria non visibili in gestione, ma per sicurezza).
        try {
            if ($event->date && $event->date->lte(now())) {
                return back()->with('error', 'Non puoi attivare/disattivare un evento con data non futura.');
            }
        } catch (\Throwable $e) {
            // no-op
        }

        $event->pubblicato = $event->pubblicato ? 0 : 1;
        $event->save();

        $label = $event->pubblicato ? 'attivato (pubblicato)' : 'disattivato';

        return back()->with('success', "Evento {$label} con successo!");
    }

    private function authorizeEvent(Event $event): void
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $event->id_organizzatore !== $user->getKey()) {
            abort(403, 'Non puoi modificare eventi di altri organizzatori.');
        }
    }
}
