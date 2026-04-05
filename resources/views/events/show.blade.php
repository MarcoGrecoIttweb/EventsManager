@extends('layouts.app')

@section('title', $event->title . ' - Excursio')

@section('content')
    <style>
        .event-description img {
            max-width: 100%;
            height: auto;
            max-height: 250px;
            width: auto;
            object-fit: contain;
            display: block;
            margin: 8px auto;
        }
    </style>
    @php
        $eventProfileBackQuery = http_build_query(['return' => route('events.show', $event)]);
    @endphp
    <div class="container">
        <div class="mb-3">
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Torna alla lista
                    </a>
                @else
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Torna alla lista
                    </a>
                @endif
            @else
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Torna alla lista
                </a>
            @endauth
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <h2 class="mb-0">{{ $event->title }}</h2>
                            @auth
                                <div class="d-flex flex-wrap gap-2">
                                    @if(auth()->user()->isAdmin() || auth()->id() === $event->id_organizzatore)
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Modifica evento
                                            </a>
                                        @else
                                            <a href="{{ route('manage.events.edit', $event) }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Modifica evento
                                            </a>
                                        @endif
                                    @endif
                                    @if(auth()->user()->isAdmin())
                                        <form action="{{ route('admin.events.destroy', $event) }}" method="POST"
                                              onsubmit="return confirm('Sei sicuro di voler cancellare definitivamente questo evento?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash-alt"></i> Cancella evento
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endauth
                        </div>
                    </div>
                    {{-- Cover Image --}}
                    @if($event->cover_image_url)
                        <div class="mb-4 event-cover-frame rounded shadow overflow-hidden">
                            <img
                                src="{{ $event->cover_image_url }}"
                                alt="{{ $event->title }}"
                                class="event-cover-img"
                            >
                        </div>
                    @endif

                    {{-- Gallery --}}
                    @if($event->images->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-images"></i> Gallery
                                    <span class="badge bg-primary">{{ $event->images->count() }}</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($event->images as $image)
                                        <div class="col-md-4 col-lg-3 mb-3">
                                            <a href="{{ Storage::disk('public')->url($image->path) }}" data-lightbox="event-gallery" data-title="{{ $event->title }}">
                                                <img src="{{ Storage::disk('public')->url($image->path) }}" alt="{{ $event->title }}"
                                                     class="img-fluid rounded shadow-sm" style="height: 200px; width: 100%; object-fit: cover;">
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    {{-- Banner evento al completo --}}
                    @if($event->isFull())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                                <div>
                                    <h4 class="alert-heading mb-1">Evento al completo!</h4>
                                    <p class="mb-0">Tutti i posti sono stati occupati. Non è più possibile iscriversi a questo evento.</p>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($event->is_past_event)
                        <div class="alert alert-secondary border-dark mb-0 rounded-0" role="alert">
                            <div class="d-flex flex-wrap align-items-center gap-2 justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-flag-checkered fa-lg"></i>
                                    <div>
                                        <strong>Evento concluso</strong>
                                        <span class="d-block small text-muted mb-0">La data dell'evento è nel passato.</span>
                                    </div>
                                </div>
                                @auth
                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-warning btn-sm flex-shrink-0">
                                            <i class="fas fa-edit"></i> Modifica evento
                                        </a>
                                        <span class="small text-muted d-none d-md-inline">Imposta una nuova data futura per ripristinarlo tra i prossimi eventi.</span>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    @endif

                    <div class="card-body">
                        <div class="mb-4">
                            @php
                                $iscrittiCount = $event->participants_count;
                                $postiTotali = $event->max_participants ? (int) $event->max_participants : null;
                                $postiLiberi = $postiTotali !== null ? max(0, $postiTotali - $iscrittiCount) : null;
                            @endphp
                            <div class="mb-2 d-flex align-items-center gap-2 flex-wrap">
                                <h5 class="mb-0">
                                    <i class="fas fa-user-circle"></i>
                                    <span>Organizzatore</span>
                                </h5>
                                <span class="mb-0 text-primary fw-semibold">
                                    {{ $event->user->nickname ?? $event->user->nome ?? '—' }}
                                </span>
                            </div>
                            <div class="event-meta-stack mb-3">
                                <div class="event-meta-date-box">
                                    <i class="fas fa-calendar"></i>
                                    <span>{{ $event->italian_event_date ?? $event->date->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="event-meta-posti-box">
                                    <i class="fas fa-users"></i>
                                    <span class="event-meta-posti-line">
                                        <span class="event-meta-label-iscritti">Iscritti:</span> <strong>{{ $iscrittiCount }}</strong>
                                        <span class="event-meta-posti-sep"> / </span>
                                        <span class="event-meta-label-liberi">Liberi:</span> <strong>{{ $postiLiberi !== null ? $postiLiberi : '—' }}</strong>
                                        <span class="event-meta-posti-sep"> / </span>
                                        <span class="event-meta-label-totali">Totali:</span> <strong>{{ $postiTotali !== null ? $postiTotali : '—' }}</strong>
                                        @if($postiTotali === null)
                                            <small class="event-meta-posti-hint"> (posti illimitati)</small>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            @if($event->formatted_cost)
                                <div class="mb-3">
                                    <span class="badge bg-success fs-6">
                                        <i class="fas fa-euro-sign"></i> {{ $event->formatted_cost }}
                                    </span>
                                </div>
                            @endif

                            @if($event->deadline)
                                <div class="mb-3">
                                    <span class="badge bg-{{ $event->isRegistrationOpen() ? 'info' : 'danger' }} fs-6">
                                        <i class="fas fa-clock"></i>
                                        Iscrizioni {{ $event->isRegistrationOpen() ? 'entro il' : 'chiuse il' }}
                                        {{ $event->deadline->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            @endif

                            <div class="mb-3">
                                <h5><i class="fas fa-map-marker-alt"></i> Località</h5>
                                @if($event->dove)
                                    <p class="mb-1"><strong>Luogo:</strong> {{ $event->dove }}</p>
                                @endif
                                <p class="mb-1"><strong>Città:</strong> {{ $event->city }}</p>
                                @auth
                                    <p class="mb-0"><strong>Indirizzo:</strong> {{ $event->address }}</p>
                                @else
                                    <p class="text-muted">
                                        <a href="{{ route('login') }}">Accedi</a> per vedere l'indirizzo completo
                                    </p>
                                @endauth

                                @php
                                    $mapWithAddress = auth()->check();
                                    $mapSrc = $event->googleMapsEmbedUrl($mapWithAddress);
                                    $mapOpen = $event->googleMapsExternalUrl($mapWithAddress);
                                @endphp
                                @if($mapSrc)
                                    <div class="mt-3">
                                        <button type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#eventMapCollapse"
                                                aria-expanded="false"
                                                aria-controls="eventMapCollapse"
                                                id="btnEventMapToggle">
                                            <i class="fas fa-map"></i> <span class="event-map-toggle-label">Mostra mappa</span>
                                        </button>
                                        <div class="collapse mt-2" id="eventMapCollapse">
                                            <p class="small text-muted mb-2">
                                                <i class="fas fa-map"></i> Google Maps
                                                @if(!$mapWithAddress)
                                                    <span class="d-block mt-1">Posizione approssimativa: effettua l’accesso per includere l’indirizzo nella ricerca.</span>
                                                @endif
                                            </p>
                                            <div class="ratio ratio-16x9 rounded overflow-hidden border shadow-sm">
                                                <iframe
                                                    id="eventMapIframe"
                                                    data-src="{{ $mapSrc }}"
                                                    src="about:blank"
                                                    style="border:0;"
                                                    allowfullscreen=""
                                                    loading="lazy"
                                                    referrerpolicy="no-referrer-when-downgrade"
                                                    title="Mappa: {{ $event->title }}"
                                                ></iframe>
                                            </div>
                                            @if($mapOpen)
                                                <a href="{{ $mapOpen }}" class="btn btn-outline-secondary btn-sm mt-2" target="_blank" rel="noopener noreferrer">
                                                    <i class="fas fa-external-link-alt"></i> Apri in Google Maps
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function () {
                                            var col = document.getElementById('eventMapCollapse');
                                            var iframe = document.getElementById('eventMapIframe');
                                            var btn = document.getElementById('btnEventMapToggle');
                                            var label = btn ? btn.querySelector('.event-map-toggle-label') : null;
                                            if (col) {
                                                col.addEventListener('shown.bs.collapse', function () {
                                                    if (iframe && iframe.dataset.src && (!iframe.src || iframe.src === 'about:blank')) {
                                                        iframe.src = iframe.dataset.src;
                                                    }
                                                    if (label) label.textContent = 'Nascondi mappa';
                                                });
                                                col.addEventListener('hidden.bs.collapse', function () {
                                                    if (label) label.textContent = 'Mostra mappa';
                                                });
                                            }
                                        });
                                    </script>
                                @endif
                            </div>

                            <div class="mb-4">
                                <h5><i class="fas fa-info-circle"></i> Dettagli</h5>
                                <div class="event-description">
                                    {!! ( (int) $event->id === 2204 ? $event->safe_description_no_images : $event->safe_description ) !!}
                                </div>
                            </div>
                        </div>

                        @auth
                            @auth
                                @auth
                                    @if(auth()->user()->isApproved())
                                        <div class="d-grid gap-2 d-md-flex">
                                            @if($userParticipating)
                                                @php
                                                    // Calcola il numero di ospiti dell'utente corrente
                                                    $currentUserGuestsCount = 0;
                                                    $currentUserParticipation = $event->participants()->where('utente.userID', auth()->id())->first();
                                                    if ($currentUserParticipation) {
                                                        $currentUserGuestsCount = $currentUserParticipation->pivot->amici ?? 0;
                                                    }
                                                @endphp

                                                <div class="d-flex flex-column gap-2">
                                                    <div class="d-flex flex-nowrap gap-2 align-items-stretch event-participation-btns">
                                                        <button type="button" class="btn btn-success" disabled aria-disabled="true">
                                                            <i class="fas fa-check-circle"></i> Partecipo
                                                        </button>
                                                        <form action="{{ route('events.cancel', $event) }}" method="POST" class="mb-0 d-flex align-items-stretch">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger w-100 h-100">
                                                                <i class="fas fa-times"></i> Toglimi
                                                            </button>
                                                        </form>
                                                    </div>
                                                    @if($event->allow_guests)
                                                        <form action="{{ route('events.add-guest', $event) }}" method="POST" class="mb-0">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-success btn-sm">
                                                                <i class="fas fa-user-plus"></i> Porta un amico
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if($currentUserGuestsCount > 0)
                                                        <small class="text-muted d-block">
                                                            Porti con te {{ $currentUserGuestsCount }} ospite{{ $currentUserGuestsCount > 1 ? 'i' : '' }}
                                                        </small>
                                                    @endif
                                                </div>
                                            @else
                                                @php
                                                    $cannotJoin = $event->isFull() || !$event->isRegistrationOpen();
                                                    $joinLabel = !$event->isRegistrationOpen() ? 'Iscrizioni chiuse' : ($event->isFull() ? 'Evento al completo' : 'Partecipa all\'evento');
                                                    $joinIcon = $cannotJoin ? 'lock' : 'check';
                                                @endphp
                                                <form action="{{ route('events.participate', $event) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-{{ $cannotJoin ? 'secondary' : 'success' }} btn-lg"
                                                        {{ $cannotJoin ? 'disabled' : '' }}>
                                                        <i class="fas fa-{{ $joinIcon }}"></i>
                                                        {{ $joinLabel }}
                                                    </button>
                                                </form>

                                                @if($event->isFull())
                                                    <div class="alert alert-warning ms-3 mb-0 py-2 d-flex align-items-center">
                                                        <i class="fas fa-users-slash fa-lg me-2"></i>
                                                        <div>
                                                            <strong>Posti esauriti</strong>
                                                            <br><small>Tutti i {{ $event->max_participants }} posti sono occupati</small>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <div class="alert alert-info">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-info-circle fa-lg me-3"></i>
                                            <div>
                                                <strong>Vuoi partecipare?</strong>
                                                <a href="{{ route('login') }}" class="btn btn-primary btn-sm ms-2">Accedi</a>
                                                per iscriverti a questo evento
                                                @if($event->isFull())
                                                    <br><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Attenzione: l'evento è al completo</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endauth
                            @else
                                <div class="alert alert-info">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle fa-lg me-3"></i>
                                        <div>
                                            <strong>Vuoi partecipare?</strong>
                                            <a href="{{ route('login') }}" class="btn btn-primary btn-sm ms-2">Accedi</a>
                                            per iscriverti a questo evento
                                            @if($event->isFull())
                                                <br><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Attenzione: l'evento è al completo</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endauth
                            @else
                            <div class="alert alert-info">
                                <a href="{{ route('login') }}" class="btn btn-primary">Accedi</a>
                                per partecipare a questo evento
                            </div>
                        @endauth
                    </div>
                </div>

                @if($event->google_album_url)
                    <div class="card mt-4 border-primary">
                        <div class="card-header bg-primary text-white py-2">
                            <h5 class="mb-0">
                                <i class="fab fa-google"></i> Album foto (Google Foto)
                            </h5>
                        </div>
                        <div class="card-body">
                            <label class="form-label small text-muted mb-1" for="event-google-album-url">Link album condiviso</label>
                            <div class="input-group input-group-sm mb-2">
                                <input type="url"
                                       class="form-control"
                                       id="event-google-album-url"
                                       value="{{ $event->google_album_url }}"
                                       readonly
                                       aria-readonly="true">
                                <a href="{{ $event->google_album_url }}"
                                   class="btn btn-primary"
                                   target="_blank"
                                   rel="noopener noreferrer">
                                    <i class="fas fa-external-link-alt"></i> Apri album
                                </a>
                            </div>
                            <p class="small text-muted mb-0">Raccolta foto dell’evento pubblicata dall’organizzatore.</p>
                        </div>
                    </div>
                @endif

                <!-- Sezione Commenti -->
                @auth
                    @if(auth()->user()->isApproved())
                        <div class="card mt-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-comments"></i> Commento</h5>
                                <button class="btn btn-outline-primary btn-sm" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#eventCommentCollapse"
                                        aria-expanded="false" aria-controls="eventCommentCollapse">
                                    Scrivi un commento
                                </button>
                            </div>
                            <div id="eventCommentCollapse" class="collapse">
                                <div class="card-body">
                                    <form action="{{ route('comments.store', $event) }}" method="POST" id="commentForm">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="commentContent" class="form-label">Il tuo commento</label>
                                            <textarea class="form-control" id="commentContent" name="content"
                                                      rows="5" placeholder="Scrivi il tuo commento..." required></textarea>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle"></i> Puoi usare la formattazione, inserire link e immagini.
                                            </small>
                                            <div class="d-flex gap-2">
                                                <button type="button"
                                                        class="btn btn-outline-secondary"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#eventCommentCollapse"
                                                        aria-expanded="true"
                                                        aria-controls="eventCommentCollapse">
                                                    Chiudi
                                                </button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-paper-plane"></i> Invia Commento
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                @endif
                @endauth

            <!-- Lista Commenti -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-comments"></i> Appunti
                            <span class="badge bg-primary">{{ $comments->count() }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($comments->count() > 0)
                            @foreach($comments as $comment)
                                <div class="mb-4 border-bottom pb-3" id="comment-{{ $comment->id }}">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center">
                                            {{-- Avatar utente --}}
                                            @if($comment->user && $comment->user->photo_url)
                                                <img src="{{ $comment->user->photo_url }}"
                                                     alt="{{ $comment->user->name ?? 'Utente' }}"
                                                     class="rounded-circle me-2"
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2"
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <strong>
                                                    @if($comment->user)
                                                        <a href="{{ route('profile.show', $comment->user) }}?{{ $eventProfileBackQuery }}" class="text-decoration-none">
                                                            {{ $comment->user->nickname }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">Utente cancellato</span>
                                                    @endif
                                                </strong>
                                                <br>
                                                <small class="text-muted">
                                                    {{ optional($comment->created_at)->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                                </small>
                                            </div>
                                        </div>

                                        {{-- Pulsanti azione --}}
                                        @auth
                                            <div class="btn-group" role="group">
                                                {{-- Pulsante modifica (autore o amministratore) --}}
                                                @if(auth()->id() === $comment->id_utente || auth()->user()->isAdmin())
                                                    <a href="{{ route('comments.edit', $comment) }}"
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="{{ auth()->user()->isAdmin() && auth()->id() !== $comment->id_utente ? 'Modifica commento (admin)' : 'Modifica commento' }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif

                                                {{-- Pulsante eliminazione (proprietario o admin) --}}
                                                @if(auth()->id() === $comment->id_utente || auth()->user()->isAdmin())
                                                    <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                onclick="return confirm('Sei sicuro di voler eliminare questo commento?')"
                                                                title="Elimina commento">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endauth
                                    </div>

                                    {{-- Contenuto del commento --}}
                                    <div class="comment-content">
                                        {!! $comment->safe_content !!}
                                    </div>
                                    @if($comment->edited_at)
                                        <div class="mt-2 small" style="color:#8B4513;">
                                            <i class="fas fa-edit"></i>
                                            Commento modificato il: {{ $comment->edited_at->timezone(config('app.timezone'))->format('d/m/Y') }}
                                            alle {{ $comment->edited_at->timezone(config('app.timezone'))->format('H:i') }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted">Nessun commento ancora. Sii il primo a commentare!</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Partecipanti -->
                <h5 class="mb-2">
                    <i class="fas fa-users"></i> Partecipanti
                    <span class="badge rounded-pill bg-dark text-white"
                          title="Iscritti all'evento più ospiti inseriti (amici)">
                        {{ $event->participants_count }}
                    </span>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('events.print', $event) }}" class="btn btn-sm btn-outline-secondary ms-1" target="_blank" title="Stampa lista (solo admin)">
                                <i class="fas fa-print"></i>
                            </a>
                        @endif
                    @endauth
                </h5>

                {{-- Lista partecipanti --}}
                @php
                    $canSeeList = $event->elenco_visibile ||
                        (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->getKey() === $event->id_organizzatore));
                @endphp
                @if(!$canSeeList)
                    <p class="text-muted"><i class="fas fa-eye-slash"></i> L'elenco dei partecipanti non è visibile per questo evento.</p>
                @elseif($event->participants->count() > 0)
                    <div class="list-group list-group-flush">
                                @foreach($event->participants as $participant)
                                    @php
                                        $currentUserIsParticipant = auth()->check() && auth()->id() === $participant->getKey();
                                        $canAddMoreGuests = $currentUserIsParticipant && $event->canAddMoreGuests($participant);
                                        $hasGuests = $participant->pivot->amici > 0;
                                        $ospitiEntries = \App\Support\OspitiGuestStore::decode($participant->pivot->ospiti_inseriti_il ?? null);
                                        $showGuestRows = $hasGuests && ($canSeeList || $currentUserIsParticipant);
                                    @endphp

                                    <div class="mb-2" id="participant-{{ $participant->getKey() }}">
                                        <div class="d-flex align-items-center flex-wrap gap-2">
                                            <div>
                                                <a href="{{ route('profile.show', $participant) }}?{{ $eventProfileBackQuery }}" class="text-decoration-none">
                                                    <i class="fas fa-user"></i> {{ $participant->nickname }}
                                                </a>
                                                {{-- rimosso il badge verde "+1" ospiti su richiesta --}}
                                                @if($currentUserIsParticipant)
                                                    <span class="badge bg-primary ms-1">Tu</span>
                                                @endif
                                            </div>
                                            @auth
                                                @if($currentUserIsParticipant)
                                                    <form action="{{ route('events.add-guest', $event) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-success btn-sm"
                                                                title="Con + aggiungi un amico, poi scrivi il nome nella riga sotto"
                                                                aria-label="Con + aggiungi un amico, poi scrivi il nome nella riga sotto">
                                                            <i class="fas fa-user-plus" aria-hidden="true"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endauth
                                        </div>

                                        @if($showGuestRows)
                                            @for($gi = 0; $gi < (int) $participant->pivot->amici; $gi++)
                                                @php
                                                    $gEntry = $ospitiEntries[$gi] ?? ['nome' => '', 'at' => ''];
                                                    $gNome = $gEntry['nome'] ?? '';
                                                    $giOld = old('guest_index');
                                                    $nomeFormError = $errors->has('nome') && $giOld !== null && (int) $giOld === $gi;
                                                    $showNomeForm = ($gNome === '' || $nomeFormError);
                                                @endphp
                                                <div class="border-0 border-top bg-light py-2">
                                                    @if($currentUserIsParticipant && $event->allow_guests)
                                                        @if($showNomeForm)
                                                            <div class="d-flex align-items-start gap-2 flex-wrap">
                                                                <div style="min-width: 12rem;">
                                                                    <div class="small text-muted mb-1">
                                                                        Amico di <strong>{{ $participant->nickname }}</strong>
                                                                    </div>
                                                                    <form action="{{ route('events.update-guest-name', $event) }}" method="POST"
                                                                          class="d-flex flex-wrap align-items-center gap-2">
                                                                        @csrf
                                                                        <input type="hidden" name="guest_index" value="{{ $gi }}">
                                                                        <input type="text" name="nome"
                                                                               class="form-control form-control-sm flex-grow-1 @error('nome') is-invalid @enderror"
                                                                               style="min-width: 10rem; max-width: 18rem;"
                                                                               placeholder="Nominativo"
                                                                               value="{{ $nomeFormError ? old('nome', '') : '' }}"
                                                                               maxlength="120"
                                                                               autocomplete="name">
                                                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                                                            Salva
                                                                        </button>
                                                                    </form>
                                                                    @error('nome')
                                                                        @if($nomeFormError)
                                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                                        @endif
                                                                    @enderror
                                                                </div>
                                                                <form action="{{ route('events.remove-guest', $event) }}" method="POST" class="d-inline flex-shrink-0 align-self-center"
                                                                      onsubmit="return confirm('Rimuovere questo amico dall\'elenco?');">
                                                                    @csrf
                                                                    <input type="hidden" name="guest_index" value="{{ $gi }}">
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                            title="Con il meno togli amico"
                                                                            aria-label="Con il meno togli amico">
                                                                        <i class="fas fa-user-minus" aria-hidden="true"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        @else
                                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                                <span class="small">
                                                                    <i class="fas fa-user" style="color:#8B4513;"></i>
                                                                    <span class="fw-semibold" style="color:#8B4513;">
                                                                        {{ $gNome !== '' ? $gNome : 'Ospite' }} /A. {{ $participant->nickname }}
                                                                    </span>
                                                                </span>
                                                                <form action="{{ route('events.remove-guest', $event) }}" method="POST" class="d-inline flex-shrink-0"
                                                                      onsubmit="return confirm('Rimuovere questo amico dall\'elenco?');">
                                                                    @csrf
                                                                    <input type="hidden" name="guest_index" value="{{ $gi }}">
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                            title="Con il meno togli amico"
                                                                            aria-label="Con il meno togli amico">
                                                                        <i class="fas fa-user-minus" aria-hidden="true"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        @endif
                                                    @else
                                                        <span class="small text-muted">
                                                            <i class="fas fa-user" style="color:#8B4513;"></i>
                                                            <span class="{{ $gNome !== '' ? 'fw-semibold' : '' }}" style="color:#8B4513;">
                                                                {{ $gNome !== '' ? $gNome : 'Ospite' }} /A. {{ $participant->nickname }}
                                                            </span>
                                                        </span>
                                                    @endif
                                                </div>
                                            @endfor
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">Nessun partecipante ancora.</p>
                        @endif

                        {{-- Invita un amico --}}
                        @auth
                            @if($userParticipating && auth()->user()->friends()->count() > 0)
                                <div class="mt-3 p-3 bg-light rounded">
                                    <form action="{{ route('events.invite', $event) }}" method="POST"
                                          class="d-flex flex-wrap align-items-center gap-2 mb-0">
                                        @csrf
                                        <h6 class="mb-0 text-nowrap flex-shrink-0">
                                            <i class="fas fa-envelope"></i> Invita un amico
                                        </h6>
                                        <select name="friend_id"
                                                class="form-select form-select-sm"
                                                style="width: auto; max-width: 11rem; min-width: 7rem;">
                                            @foreach(auth()->user()->friends()->orderBy('nome')->get() as $friend)
                                                @if(!$event->participants->contains('userID', $friend->userID))
                                                    <option value="{{ $friend->getKey() }}">{{ $friend->nickname }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary flex-shrink-0">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @endauth

                        {{-- Informazioni ospiti --}}
                        {{-- Informazioni ospiti (solo se si vuole riattivare un testo esplicativo) --}}
                    </div>
                </div>

                <!-- Informazioni Organizzatore -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Organizzatore</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">
                            <i class="fas fa-user"></i>
                            <a href="{{ route('profile.show', $event->user) }}?{{ $eventProfileBackQuery }}">
                                {{ $event->user->nickname }}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @parent
    @include('partials.ckeditor4-description', ['field' => 'commentContent', 'height' => 200])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Scroll al commento se specificato
            @if(session('scrollTo'))
            const commentId = '{{ session('scrollTo') }}';
            const commentElement = document.getElementById(commentId);
            if (commentElement) {
                setTimeout(() => {
                    commentElement.scrollIntoView({ behavior: 'smooth' });
                    commentElement.classList.add('highlight-comment');
                }, 500);
            }
            @endif

        });
    </script>

    <style>
        .event-cover-frame {
            position: relative;
            width: 100%;
            height: 0;
            padding-top: 56.25%; /* 16:9 */
            max-height: 400px;
            background: #f1f3f5;
            overflow: hidden;
        }

        .event-cover-img {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
        }

        @media (max-width: 767.98px) {
            .event-cover-frame {
                max-height: 240px;
            }
        }

        .event-meta-stack {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            max-width: 100%;
        }

        .event-meta-date-box,
        .event-meta-posti-box {
            display: flex;
            align-items: flex-start;
            gap: 0.35rem;
            width: 100%;
            box-sizing: border-box;
            border-radius: 0.375rem;
            padding: 0.65rem 0.9rem;
            font-size: 1rem;
            line-height: 1.45;
            border: 2px solid #000;
        }

        .event-meta-date-box > i,
        .event-meta-posti-box > i {
            margin-top: 0.15rem;
            flex-shrink: 0;
        }

        .event-meta-posti-line {
            flex: 1;
            min-width: 0;
        }

        .event-meta-date-box {
            background-color: #0d6efd;
            color: #fff;
        }

        .event-meta-posti-box {
            background-color: #dee2e6;
            color: #212529;
        }

        .event-meta-posti-box strong {
            color: #1a1d20;
        }

        .event-meta-posti-box > i {
            color: #495057;
        }

        .event-meta-label-iscritti {
            color: #e0113c;
            font-weight: 700;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.6);
        }

        .event-meta-label-liberi {
            color: #008f4a;
            font-weight: 700;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.6);
        }

        .event-meta-label-totali {
            color: #e65100;
            font-weight: 700;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.6);
        }

        .event-meta-posti-hint {
            color: #495057;
            font-weight: normal;
        }

        .event-meta-posti-sep {
            color: #6c757d;
        }

        .event-participation-btns > .btn,
        .event-participation-btns > form {
            flex: 1 1 0;
            min-width: 0;
        }

        .highlight-comment {
            background-color: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
            transition: background-color 2s ease;
        }

        .cke_chrome {
            border-radius: 8px !important;
            border: 2px solid #e9ecef !important;
        }
        .cke_focus .cke_chrome {
            border-color: #0d6efd !important;
        }
         .comment-content {
             line-height: 1.6;
             font-size: 14px;
         }

        .comment-content p {
            margin-bottom: 0.8rem;
        }

        .comment-content strong, .comment-content b {
            font-weight: 600;
        }

        .comment-content em, .comment-content i {
            font-style: italic;
        }

        .comment-content u {
            text-decoration: underline;
        }

        .comment-content a {
            color: #0d6efd;
            text-decoration: none;
        }

        .comment-content a:hover {
            text-decoration: underline;
        }

        .comment-content ul, .comment-content ol {
            margin-left: 1.5rem;
            margin-bottom: 0.8rem;
        }

        .comment-content li {
            margin-bottom: 0.3rem;
        }

        .comment-content code {
            background-color: #f8f9fa;
            padding: 0.1rem 0.3rem;
            border-radius: 0.25rem;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }

        .comment-content pre {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.375rem;
            overflow-x: auto;
            margin-bottom: 0.8rem;
        }

        .comment-content pre code {
            background: none;
            padding: 0;
        }
    </style>
@endsection
