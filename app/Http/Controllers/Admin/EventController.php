<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventImage;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index()
    {
        $request = request();
        $term = trim((string) $request->query('q', ''));
        $field = (string) $request->query('field', 'nome');
        if (!in_array($field, ['nome', 'locale', 'indirizzo'], true)) {
            $field = 'nome';
        }

        $events = Event::with(['user', 'participants'])
            ->when($term !== '', function ($q) use ($term, $field) {
                $like = '%' . $term . '%';

                if ($field === 'nome') {
                    $q->where('nome', 'like', $like);
                    return;
                }

                if ($field === 'locale') {
                    // "Locale" nel DB legacy è `dove` (nome del posto)
                    $q->where('dove', 'like', $like);
                    return;
                }

                // indirizzo: cerca su via / civico / città (utile quando l'utente scrive "Marostica 9" o "Milano")
                $q->where(function ($qq) use ($like) {
                    $qq->where('via', 'like', $like)
                        ->orWhere('civico', 'like', $like)
                        ->orWhere('citta', 'like', $like);
                });
            })
            ->orderBy('dataevento', 'desc')
            ->paginate(20);

        return view('admin.events.index', compact('events'));
    }

    /**
     * Suggerimenti autocomplete per il box ricerca Admin Events.
     */
    public function suggestions(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        $field = (string) $request->query('field', 'nome');
        if (!in_array($field, ['nome', 'locale', 'indirizzo'], true)) {
            $field = 'nome';
        }

        if ($term === '' || mb_strlen($term, 'UTF-8') < 2) {
            return response()->json([]);
        }

        $like = $term . '%';

        if ($field === 'nome') {
            $items = Event::query()
                ->select('nome')
                ->whereNotNull('nome')
                ->where('nome', 'like', $like)
                ->distinct()
                ->orderBy('nome')
                ->limit(12)
                ->pluck('nome')
                ->map(fn ($v) => trim((string) $v))
                ->filter()
                ->values();

            return response()->json($items);
        }

        if ($field === 'locale') {
            $items = Event::query()
                ->select('dove')
                ->whereNotNull('dove')
                ->where('dove', 'like', $like)
                ->distinct()
                ->orderBy('dove')
                ->limit(12)
                ->pluck('dove')
                ->map(fn ($v) => trim((string) $v))
                ->filter()
                ->values();

            return response()->json($items);
        }

        // indirizzo: suggerisci SOLO la via (non civico/città)
        $items = Event::query()
            ->select('via')
            ->whereNotNull('via')
            ->where('via', 'like', $like)
            ->distinct()
            ->orderBy('via')
            ->limit(12)
            ->pluck('via')
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->values();

        return response()->json($items);
    }

    public function create()
    {
        return view('admin.events.create');
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
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,heic,heif|max:10240',
            'google_album_url' => 'nullable|string|max:2048|url',
        ]);

        $allowGuests = $request->boolean('allow_guests');
        $isActive = $request->has('is_active') ? 1 : 0;

        // Create event with legacy column names
        $event = Event::create([
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
            'elenco_visibile' => 0,
            'sondaggio' => '',
            'url_galleria' => (string) ($validated['google_album_url'] ?? ''),
            'datascadenza' => $validated['deadline'] ?? $validated['date'],
            'allow_guests' => $allowGuests,
            'max_guests_per_user' => $allowGuests ? ($validated['max_guests_per_user'] ?? 3) : 0,
        ]);

        $event->enrollOrganizerAsParticipant();

        // Handle cover image
        if ($request->hasFile('cover_image')) {
            $coverResult = $this->imageService->uploadCoverImage(
                $request->file('cover_image'),
                $event->getKey()
            );

            if ($coverResult['success']) {
                $event->update(['immagine' => $coverResult['filename']]);
            }
        }

        // Handle gallery images
        if ($request->hasFile('gallery_images')) {
            $this->processGalleryImages($request->file('gallery_images'), $event);
        }

        return redirect()->route('home')
            ->with('success', 'Evento creato con successo!');
    }

    public function show(Event $event)
    {
        $event->load(['user', 'participants', 'comments.user', 'images']);

        return view('admin.events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        $event->load('images');
        return view('admin.events.edit', compact('event'));
    }

    /**
     * Crea una copia dell'evento (stessi dati e media). Le iscrizioni non vengono copiate.
     */
    public function duplicate(Event $event)
    {
        $event->load(['images' => fn ($q) => $q->orderBy('order')]);

        try {
            $newEvent = DB::transaction(function () use ($event) {
                // `datascadenza` in legacy può contenere "0000-00-00 00:00:00" che Carbon normalizza a year <= 1
                // (es. "-0001-11-30 00:00:00"), valore non inseribile in MySQL. Usiamo l'accessor `deadline` che
                // torna `null` in quei casi e facciamo fallback su `dataevento`.
                $safeDeadline = $event->deadline ?? $event->dataevento;

                return Event::create([
                    'nome' => Str::upper($event->nome) . ' (copia)',
                    'incipit' => $event->incipit,
                    'descrizione' => $event->descrizione,
                    'dataevento' => $event->dataevento,
                    'citta' => $event->citta,
                    'dove' => $event->dove ?? '',
                    'via' => $event->via,
                    'civico' => $event->civico ?? '',
                    'costo' => $event->costo,
                    'numeromax' => $event->numeromax,
                    'id_organizzatore' => $event->id_organizzatore,
                    'pubblicato' => $event->pubblicato,
                    'elenco_visibile' => 0,
                    'sondaggio' => $event->sondaggio ?? '',
                    'url_galleria' => $event->url_galleria ?? '',
                    'datascadenza' => $safeDeadline,
                    'allow_guests' => (bool) $event->allow_guests,
                    'max_guests_per_user' => (int) ($event->max_guests_per_user ?? 0),
                    'immagine' => null,
                ]);
            });
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Impossibile duplicare l\'evento: ' . $e->getMessage());
        }

        $newEvent->enrollOrganizerAsParticipant();

        $this->duplicateEventCoverFiles($event, $newEvent);
        $this->duplicateEventGalleryFiles($event, $newEvent);

        return redirect()
            ->route('admin.events.edit', $newEvent)
            ->with('success', 'Evento duplicato. Le iscrizioni non sono state copiate: controlla data e dettagli, poi salva.');
    }

    public function update(Request $request, Event $event)
    {
        // Supporto UI con campi separati data/ora: compone `date` (datetime) che finirà in `dataevento`.
        if (!$request->filled('date') && $request->filled('date_only') && $request->filled('time_only')) {
            $request->merge([
                'date' => trim((string) $request->input('date_only')) . ' ' . trim((string) $request->input('time_only')),
            ]);
        }

        // Se l'utente ha selezionato un file in UI ma PHP non lo ha ricevuto, spesso è un limite di upload.
        if ($request->input('cover_image_selected') == '1' && !$request->hasFile('cover_image')) {
            return back()->with('error', 'La nuova copertina non è stata ricevuta dal server. Probabile file troppo grande o limite PHP (upload_max_filesize / post_max_size). Prova con un file più piccolo.');
        }

        $debugCover = [
            'selected_flag' => (string) $request->input('cover_image_selected', ''),
            'has_file' => $request->hasFile('cover_image'),
            'is_valid' => $request->hasFile('cover_image') ? $request->file('cover_image')->isValid() : null,
            'orig_name' => $request->hasFile('cover_image') ? $request->file('cover_image')->getClientOriginalName() : null,
            'size' => $request->hasFile('cover_image') ? $request->file('cover_image')->getSize() : null,
            'mime' => $request->hasFile('cover_image') ? $request->file('cover_image')->getMimeType() : null,
            'before_immagine' => $event->immagine,
        ];

        $request->merge([
            'google_album_url' => ($g = trim((string) $request->input('google_album_url', ''))) !== '' ? $g : null,
        ]);

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
            'cover_image_selected' => 'nullable|in:0,1',
            'google_album_url' => 'nullable|string|max:2048|url',
        ]);

        if ($request->hasFile('cover_image')) {
            $request->validate([
                'cover_image' => 'file|image|mimes:jpeg,png,jpg,gif,webp,heic,heif|max:4096',
            ]);
        }

        if ($request->hasFile('gallery_images')) {
            $request->validate([
                'gallery_images.*' => 'file|image|mimes:jpeg,png,jpg,gif,webp,heic,heif|max:10240',
            ]);
        }

        $allowGuests = $request->boolean('allow_guests');
        $isActive = $request->has('is_active');

        $wasPastEvent = $event->is_past_event;
        $newDate = \Carbon\Carbon::parse($validated['date']);

        // Map to legacy columns
        $updateData = [
            'nome' => Str::upper($validated['title']),
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
            'pubblicato' => $isActive ? 1 : 0,
            'allow_guests' => $allowGuests,
            'max_guests_per_user' => $allowGuests ? ($validated['max_guests_per_user'] ?? 3) : 0,
            'url_galleria' => (string) ($validated['google_album_url'] ?? ''),
        ];

        // Handle cover image removal
        if ($request->has('remove_cover') && $event->immagine) {
            $this->deleteEventCover($event);
            $updateData['immagine'] = null;
            // Evita doppi tentativi di delete più avanti nello stesso request
            $event->immagine = null;
        }

        // Handle new cover image
        if ($request->hasFile('cover_image')) {
            if ($event->immagine) {
                $this->deleteEventCover($event);
            }

            $coverResult = $this->imageService->uploadCoverImage(
                $request->file('cover_image'),
                $event->getKey()
            );

            if (!$coverResult['success']) {
                return back()->with('error', 'Caricamento copertina fallito: ' . ($coverResult['error'] ?? 'errore sconosciuto'));
            }

            $updateData['immagine'] = $coverResult['filename'];
            $debugCover['uploaded_filename'] = $coverResult['filename'];
        }

        // Delete selected gallery images
        if ($request->has('delete_images')) {
            $this->deleteGalleryImages($request->delete_images);
        }

        // Add new gallery images
        if ($request->hasFile('gallery_images')) {
            $this->processGalleryImages($request->file('gallery_images'), $event);
        }

        // Homepage: pubblicato=1 e dataevento > adesso (scope upcoming).
        // Ripubblica se la nuova data è futura e: "Evento attivo" ON, oppure era concluso e ripassato al futuro,
        // oppure la data/ora è stata spostata in avanti (posticipato) rispetto a prima.
        $oldMoment = $event->dataevento ? $event->date->copy() : null;
        $datePostponed = $oldMoment && $newDate->gt($oldMoment);
        $republishOnHome = $newDate->gt(now()) && ($isActive || $wasPastEvent || $datePostponed);
        if ($republishOnHome) {
            $updateData['pubblicato'] = 1;
            try {
                $deadlineAt = \Carbon\Carbon::parse($updateData['datascadenza']);
                if ($deadlineAt->lte(now())) {
                    $updateData['datascadenza'] = $newDate->format('Y-m-d H:i:s');
                }
            } catch (\Throwable $e) {
                $updateData['datascadenza'] = $newDate->format('Y-m-d H:i:s');
            }
        }

        $event->update($updateData);
        $event->refresh();
        $debugCover['after_immagine'] = $event->immagine;
        try {
            \Log::info('Admin event cover update debug', ['event_id' => $event->getKey(), 'cover' => $debugCover]);
        } catch (\Throwable $e) {
            // no-op
        }

        $successMessage = 'Evento aggiornato con successo!';
        if ($republishOnHome) {
            $successMessage = 'Evento aggiornato: la nuova data è futura, l\'evento è di nuovo pubblicato e visibile in homepage (Prossimi eventi).';
        }
        if ($request->hasFile('cover_image') && !empty($updateData['immagine'])) {
            $successMessage .= ' Copertina aggiornata (' . $event->immagine . ').';
        } elseif ($request->input('cover_image_selected') == '1') {
            $successMessage .= ' (DEBUG: copertina selezionata ma non elaborata — vedi log)';
        }

        // Usa la root della request (es. "http://localhost/excursio/public") per evitare 404 Apache
        // quando l'app è servita da una sottocartella.
        $publicUrl = rtrim($request->root(), '/') . route('events.show', $event, false);

        return redirect()->to($publicUrl)
            ->with('success', $successMessage);
    }

    public function destroy(Event $event)
    {
        $this->imageService->deleteEventFolder($event->getKey());
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Evento eliminato con successo!');
    }

    public function toggleStatus(Event $event)
    {
        $event->update([
            'pubblicato' => $event->pubblicato ? 0 : 1
        ]);

        $status = $event->pubblicato ? 'attivato' : 'disattivato';

        return back()->with('success', "Evento {$status} con successo!");
    }

    private function processGalleryImages(array $images, Event $event): void
    {
        $uploadResults = $this->imageService->uploadEventImages($images, $event->getKey());

        foreach ($uploadResults as $result) {
            if ($result['success']) {
                EventImage::create([
                    'event_id' => $event->getKey(),
                    'filename' => $result['filename'],
                    'path' => $result['path'],
                    'original_name' => $result['original_name'],
                    'mime_type' => $result['mime_type'],
                    'size' => $result['size'],
                    'order' => $event->images()->count(),
                ]);
            }
        }
    }

    private function deleteGalleryImages(array $imageIds): void
    {
        $images = EventImage::whereIn('id', $imageIds)->get();

        foreach ($images as $image) {
            $this->imageService->deleteImage($image->path);
            $image->delete();
        }
    }

    private function deleteEventCover(Event $event): void
    {
        if (!$event->immagine) {
            return;
        }

        // 1) Legacy: public/upload_immagini/<filename>
        $this->imageService->deleteCoverImage($event->immagine);

        // 2) Storage: "events/<id>/<filename>" oppure path completo già salvato
        $disk = Storage::disk('public');
        if (str_contains($event->immagine, '/')) {
            $disk->delete($event->immagine);
        } else {
            $disk->delete('events/' . $event->getKey() . '/' . $event->immagine);
        }
    }

    private function duplicateEventCoverFiles(Event $source, Event $newEvent): void
    {
        if (!$source->immagine) {
            return;
        }

        $disk = Storage::disk('public');
        $uploadDir = public_path('upload_immagini');
        $legacySrc = $uploadDir . DIRECTORY_SEPARATOR . $source->immagine;

        if (!str_contains($source->immagine, '/') && is_file($legacySrc)) {
            $ext = pathinfo($source->immagine, PATHINFO_EXTENSION) ?: 'jpg';
            $newName = $newEvent->getKey() . '_' . time() . '.' . $ext;
            $legacyDest = $uploadDir . DIRECTORY_SEPARATOR . $newName;
            if (@copy($legacySrc, $legacyDest)) {
                $newEvent->update(['immagine' => $newName]);
            }

            return;
        }

        $srcRel = str_contains($source->immagine, '/')
            ? $source->immagine
            : ('events/' . $source->getKey() . '/' . $source->immagine);

        if (!$disk->exists($srcRel)) {
            return;
        }

        $disk->makeDirectory('events/' . $newEvent->getKey());
        $ext = pathinfo($srcRel, PATHINFO_EXTENSION) ?: 'jpg';
        $newFile = uniqid('', true) . '_' . time() . '.' . $ext;
        $destRel = 'events/' . $newEvent->getKey() . '/' . $newFile;

        if ($disk->copy($srcRel, $destRel)) {
            $newEvent->update(['immagine' => $newFile]);
        }
    }

    private function duplicateEventGalleryFiles(Event $source, Event $newEvent): void
    {
        $disk = Storage::disk('public');
        $disk->makeDirectory('events/' . $newEvent->getKey());

        foreach ($source->images as $index => $image) {
            if (!$disk->exists($image->path)) {
                continue;
            }

            $ext = pathinfo($image->path, PATHINFO_EXTENSION) ?: 'jpg';
            $newFilename = uniqid('', true) . '_' . time() . '.' . $ext;
            $newPath = 'events/' . $newEvent->getKey() . '/' . $newFilename;

            if (!$disk->copy($image->path, $newPath)) {
                continue;
            }

            EventImage::create([
                'event_id' => $newEvent->getKey(),
                'filename' => $newFilename,
                'path' => $newPath,
                'original_name' => $image->original_name,
                'mime_type' => $image->mime_type,
                'size' => $disk->size($newPath) ?: $image->size,
                'order' => (int) ($image->order ?? $index),
                'is_cover' => (bool) $image->is_cover,
            ]);
        }
    }
}
