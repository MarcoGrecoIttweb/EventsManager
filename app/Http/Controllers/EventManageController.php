<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventImage;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $events = $query->orderBy('dataevento', 'desc')->paginate(12);

        return view('manage.events.index', compact('events'));
    }

    public function create()
    {
        return view('manage.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'incipit' => 'nullable|string|max:500',
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
        ]);

        $allowGuests = $request->has('allow_guests');

        $event = Event::create([
            'nome' => $validated['title'],
            'incipit' => $validated['incipit'] ?? null,
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

        if ($request->hasFile('cover_image')) {
            $coverResult = $this->imageService->uploadImage(
                $request->file('cover_image'),
                "events/{$event->getKey()}"
            );
            if ($coverResult['success']) {
                $event->update(['immagine' => $coverResult['path']]);
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

        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'incipit' => 'nullable|string|max:500',
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
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $allowGuests = $request->has('allow_guests');

        $updateData = [
            'nome' => $validated['title'],
            'incipit' => $validated['incipit'] ?? null,
            'descrizione' => $validated['description'],
            'dataevento' => $validated['date'],
            'citta' => $validated['city'],
            'dove' => $validated['venue'] ?? '',
            'via' => $validated['address'],
            'costo' => $validated['cost'] ?? null,
            'datascadenza' => $validated['deadline'] ?? $validated['date'],
            'elenco_visibile' => $request->has('elenco_visibile') ? 1 : 0,
            'numeromax' => $validated['max_participants'] ?? null,
            'allow_guests' => $allowGuests,
            'max_guests_per_user' => $allowGuests ? ($validated['max_guests_per_user'] ?? 3) : 0,
        ];

        if ($request->has('remove_cover') && $event->immagine) {
            $this->imageService->deleteImage("events/{$event->getKey()}/{$event->immagine}");
            $updateData['immagine'] = null;
        }

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

        $event->update($updateData);

        return redirect()->route('manage.events.index')
            ->with('success', 'Evento aggiornato con successo!');
    }

    public function destroy(Event $event)
    {
        $this->authorizeEvent($event);
        $this->imageService->deleteEventFolder($event->getKey());
        $event->delete();

        return redirect()->route('manage.events.index')
            ->with('success', 'Evento eliminato con successo!');
    }

    private function authorizeEvent(Event $event): void
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $event->id_organizzatore !== $user->getKey()) {
            abort(403, 'Non puoi modificare eventi di altri organizzatori.');
        }
    }
}
