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
            ->active()
            ->upcoming()
            ->ordered()
            ->paginate(12);

        return view('events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'description' => 'required|string|min:10',
            'date' => 'required|date|after:now',
            'city' => 'required|string|max:15',
            'venue' => 'nullable|string|max:50',
            'address' => 'required|string|max:50',
            'cost' => 'nullable|numeric|min:0',
            'deadline' => 'nullable|date',
            'elenco_visibile' => 'sometimes|boolean',
            'max_participants' => 'nullable|integer|min:1',
            'allow_guests' => 'sometimes|boolean',
            'max_guests_per_user' => 'nullable|integer|min:1|max:10',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $allowGuests = $request->has('allow_guests');

        // Create event with legacy column names
        $event = Event::create([
            'nome' => $validated['title'],
            'descrizione' => $validated['description'],
            'dataevento' => $validated['date'],
            'citta' => $validated['city'],
            'dove' => $validated['venue'] ?? '',
            'via' => $validated['address'],
            'civico' => '',
            'costo' => $validated['cost'] ?? null,
            'numeromax' => $validated['max_participants'] ?? null,
            'id_organizzatore' => Auth::id(),
            'pubblicato' => 1,
            'elenco_visibile' => $request->has('elenco_visibile') ? 1 : 0,
            'sondaggio' => '',
            'url_galleria' => '',
            'datascadenza' => $validated['deadline'] ?? $validated['date'],
            'allow_guests' => $allowGuests,
            'max_guests_per_user' => $allowGuests ? ($validated['max_guests_per_user'] ?? 3) : 0,
        ]);

        // Handle cover image
        if ($request->hasFile('cover_image')) {
            $coverResult = $this->imageService->uploadImage(
                $request->file('cover_image'),
                "events/{$event->getKey()}"
            );

            if ($coverResult['success']) {
                $event->update(['immagine' => $coverResult['path']]);
            }
        }

        // Handle gallery images
        if ($request->hasFile('gallery_images')) {
            $this->processGalleryImages($request->file('gallery_images'), $event);
        }

        return redirect()->route('admin.events.index')
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

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'description' => 'required|string|min:10',
            'date' => 'required|date',
            'city' => 'required|string|max:15',
            'venue' => 'nullable|string|max:50',
            'address' => 'required|string|max:50',
            'cost' => 'nullable|numeric|min:0',
            'deadline' => 'nullable|date',
            'elenco_visibile' => 'sometimes|boolean',
            'max_participants' => 'nullable|integer|min:1',
            'allow_guests' => 'sometimes|boolean',
            'max_guests_per_user' => 'nullable|integer|min:1|max:10',
        ]);

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

        $allowGuests = $request->has('allow_guests');
        $isActive = $request->has('is_active');

        // Map to legacy columns
        $updateData = [
            'nome' => $validated['title'],
            'descrizione' => $validated['description'],
            'dataevento' => $validated['date'],
            'citta' => $validated['city'],
            'dove' => $validated['venue'] ?? '',
            'via' => $validated['address'],
            'costo' => $validated['cost'] ?? null,
            'datascadenza' => $validated['deadline'] ?? $validated['date'],
            'elenco_visibile' => $request->has('elenco_visibile') ? 1 : 0,
            'numeromax' => $validated['max_participants'] ?? null,
            'pubblicato' => $isActive ? 1 : 0,
            'allow_guests' => $allowGuests,
            'max_guests_per_user' => $allowGuests ? ($validated['max_guests_per_user'] ?? 3) : 0,
        ];

        // Handle cover image removal
        if ($request->has('remove_cover') && $event->immagine) {
            $this->imageService->deleteImage("events/{$event->getKey()}/{$event->immagine}");
            $updateData['immagine'] = null;
        }

        // Handle new cover image
        if ($request->hasFile('cover_image')) {
            if ($event->immagine) {
                $this->imageService->deleteImage("events/{$event->getKey()}/{$event->immagine}");
            }

            $coverResult = $this->imageService->uploadImage(
                $request->file('cover_image'),
                "events/{$event->getKey()}"
            );

            if ($coverResult['success']) {
                $updateData['immagine'] = $coverResult['path'];
            }
        }

        // Delete selected gallery images
        if ($request->has('delete_images')) {
            $this->deleteGalleryImages($request->delete_images);
        }

        // Add new gallery images
        if ($request->hasFile('gallery_images')) {
            $this->processGalleryImages($request->file('gallery_images'), $event);
        }

        $event->update($updateData);

        return redirect()->route('admin.events.index')
            ->with('success', 'Evento aggiornato con successo!');
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
}
