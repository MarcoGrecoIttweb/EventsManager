<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventImage;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }
    public function index()
    {
        $events = Event::with(['user', 'participants'])
            ->where('is_active', true)
            ->where('date', '>', now())
            ->orderBy('date')
            ->paginate(12); // Aggiungi paginate invece di get()

        return view('events.index', compact('events'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create()
    {
        return view('admin.events.create');
    }

    /**
     * Store a newly created event.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'date' => 'required|date|after:now',
            'city' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'max_participants' => 'nullable|integer|min:1',
            'allow_guests' => 'sometimes|boolean',
            'max_guests_per_user' => 'required_if:allow_guests,true|integer|min:1|max:10',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        // Gestisci il campo allow_guests
        $validated['allow_guests'] = $request->has('allow_guests');

        if (!$validated['allow_guests']) {
            $validated['max_guests_per_user'] = 0;
        }

        // Crea l'evento
        $event = Event::create(array_merge($validated, [
            'user_id' => Auth::id(),
            'is_active' => true
        ]));

        // Gestisci cover image
        if ($request->hasFile('cover_image')) {
            $coverResult = $this->imageService->uploadImage(
                $request->file('cover_image'),
                "events/{$event->id}"
            );

            if ($coverResult['success']) {
                $event->update(['cover_image' => $coverResult['path']]); // Salva il path completo
            }
        }

        // Gestisci gallery images
        if ($request->hasFile('gallery_images')) {
            $this->processGalleryImages($request->file('gallery_images'), $event);
        }

        return redirect()->route('admin.events.index')
            ->with('success', 'Evento creato con successo!');
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event)
    {
        $event->load(['user', 'participants', 'comments.user', 'images']);

        return view('admin.events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event)
    {
        $event->load('images');
        return view('admin.events.edit', compact('event'));
    }

    /**
     * Update the specified event.
     */
    public function update(Request $request, Event $event)
    {
        // Validazione per i campi non-file
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'date' => 'required|date|after:now',
            'city' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'max_participants' => 'nullable|integer|min:1',
            'allow_guests' => 'sometimes|boolean',
            'max_guests_per_user' => 'required_if:allow_guests,true|integer|min:1|max:10',
        ]);

        // Validazione separata per i file
        if ($request->hasFile('cover_image')) {
            $request->validate([
                'cover_image' => 'file|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);
        }

        if ($request->hasFile('gallery_images')) {
            $request->validate([
                'gallery_images.*' => 'file|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ]);
        }

        // Gestisci il campo allow_guests
        $validated['allow_guests'] = $request->has('allow_guests');

        if (!$validated['allow_guests']) {
            $validated['max_guests_per_user'] = 0;
        }


        // Gestisci rimozione cover image
        if ($request->has('remove_cover') && $event->cover_image) {
            $this->imageService->deleteImage("events/{$event->id}/{$event->cover_image}");
            $validated['cover_image'] = null;
        }

        // Gestisci nuova cover image
        if ($request->hasFile('cover_image')) {
            // Elimina la vecchia cover se esiste
            if ($event->cover_image) {
                $this->imageService->deleteImage("events/{$event->id}/{$event->cover_image}");
            }

            $coverResult = $this->imageService->uploadImage(
                $request->file('cover_image'),
                "events/{$event->id}"
            );

            if ($coverResult['success']) {
                $validated['cover_image'] = $coverResult['path']; // Salva il path completo
            }
        }

        // Elimina immagini selezionate
        if ($request->has('delete_images')) {
            $this->deleteGalleryImages($request->delete_images);
        }

        // Aggiungi nuove immagini alla gallery
        if ($request->hasFile('gallery_images')) {
            $this->processGalleryImages($request->file('gallery_images'), $event);
        }

        $event->update($validated);

        return redirect()->route('admin.events.index')
            ->with('success', 'Evento aggiornato con successo!');
    }

    /**
     * Remove the specified event.
     */
    public function destroy(Event $event)
    {
        // Elimina tutte le immagini dell'evento
        $this->imageService->deleteEventFolder($event->id);

        // Elimina l'evento
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Evento eliminato con successo!');
    }

    /**
     * Toggle event status (active/inactive)
     */
    public function toggleStatus(Event $event)
    {
        $event->update([
            'is_active' => !$event->is_active
        ]);

        $status = $event->is_active ? 'attivato' : 'disattivato';

        return back()->with('success', "Evento {$status} con successo!");
    }

    /**
     * Process gallery images upload
     */
    private function processGalleryImages(array $images, Event $event): void
    {
        $uploadResults = $this->imageService->uploadEventImages($images, $event->id);

        foreach ($uploadResults as $result) {
            if ($result['success']) {
                EventImage::create([
                    'event_id' => $event->id,
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

    /**
     * Delete gallery images
     */
    private function deleteGalleryImages(array $imageIds): void
    {
        $images = EventImage::whereIn('id', $imageIds)->get();

        foreach ($images as $image) {
            $this->imageService->deleteImage($image->path);
            $image->delete();
        }
    }
}
