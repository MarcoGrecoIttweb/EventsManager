@extends('layouts.app')

@section('title', $event->title . ' - Excursio')

@section('suppress_global_flash', true)

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
        <div class="mb-3 d-flex flex-wrap align-items-stretch gap-2">
            <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center">
                <i class="fas fa-arrow-left"></i> Torna alla home
            </a>

            @if(session('success'))
                <div class="event-flash-success-sm alert alert-success mb-0 d-inline-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <span class="text-truncate">{{ session('success') }}</span>
                </div>
            @endif
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="card event-main-card">
                    <div class="card-header">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <h2 class="mb-0">{{ $event->title }}</h2>
                            @auth
                                <div class="d-flex flex-wrap gap-2">
                                    @if(auth()->user()->isAdmin() || auth()->id() === $event->id_organizzatore)
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-warning btn-sm btn-border-brown">
                                                <i class="fas fa-edit"></i> Modifica evento
                                            </a>
                                        @else
                                            <a href="{{ route('manage.events.edit', $event) }}" class="btn btn-warning btn-sm btn-border-brown">
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
                        <div class="mb-1 event-cover-frame rounded shadow overflow-hidden">
                            <img
                                src="{{ $event->cover_image_url }}"
                                alt="{{ $event->title }}"
                                class="event-cover-img"
                            >
                        </div>
                    @endif

                    {{-- Evento al completo: box subito sotto l'immagine --}}
                    @if($event->isFull())
                        <div class="event-closed-box mt-2">
                            CHIUSE ADESIONI
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
                                        <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-warning btn-sm flex-shrink-0 btn-border-brown">
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
                            <div class="mb-2 event-organizer-strip">
                                <div class="event-organizer-actions event-organizer-strip__cell">
                                    @auth
                                        @if(auth()->user()->isApproved())
                                            @if($userParticipating)
                                                @php
                                                    $currentUserGuestsCount = 0;
                                                    $currentUserParticipation = $event->participants()->where('utente.userID', auth()->id())->first();
                                                    if ($currentUserParticipation) {
                                                        $currentUserGuestsCount = $currentUserParticipation->pivot->amici ?? 0;
                                                    }
                                                @endphp
                                                <div class="d-flex flex-column align-items-end gap-1">
                                                    <div class="d-flex flex-nowrap gap-2 align-items-stretch event-participation-btns">
                                                        <form action="{{ route('events.cancel', $event) }}" method="POST" class="mb-0 d-flex align-items-stretch">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm w-100 h-100 event-btn-participate-map-height event-btn-meta-height btn-border-brown">
                                                                <i class="fas fa-times"></i> Toglimi
                                                            </button>
                                                        </form>
                                                    </div>
                                                    @if($currentUserGuestsCount > 0)
                                                        <small class="text-muted">
                                                            Porti con te {{ $currentUserGuestsCount }} ospite{{ $currentUserGuestsCount > 1 ? 'i' : '' }}
                                                        </small>
                                                    @endif
                                                </div>
                                            @else
                                                @php
                                                    $cannotJoin = $event->isFull() || !$event->isRegistrationOpen();
                                                    $joinLabel = !$event->isRegistrationOpen() ? 'Iscrizioni chiuse' : ($event->isFull() ? 'Evento al completo' : 'Iscrivimi all\'evento');
                                                    $joinIcon = $cannotJoin ? 'lock' : 'check';
                                                @endphp
                                                <div class="d-flex flex-wrap align-items-center gap-2 w-100">
                                                    <form action="{{ route('events.participate', $event) }}" method="POST" class="mb-0 flex-grow-1 w-100">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm w-100 event-btn-participate-map-height event-btn-iscrivimi-all-evento btn-iscrivimi-state-{{ $cannotJoin ? 'off' : 'on' }}"
                                                            {{ $cannotJoin ? 'disabled' : '' }}>
                                                            <i class="fas fa-{{ $joinIcon }}"></i>
                                                            {{ $joinLabel }}
                                                        </button>
                                                    </form>
                                                    @if($event->isFull())
                                                        <span class="small text-warning mb-0"><i class="fas fa-users-slash"></i> Posti esauriti</span>
                                                    @endif
                                                </div>
                                            @endif
                                        @else
                                            <div class="small text-end">
                                                <span class="d-block text-muted mb-1"><i class="fas fa-hourglass-half"></i> Profilo in attesa di approvazione: non puoi ancora iscriverti agli eventi.</span>
                                                @if($event->isFull())
                                                    <span class="d-block text-warning mt-1"><i class="fas fa-exclamation-triangle"></i> Evento al completo</span>
                                                @endif
                                            </div>
                                        @endif
                                    @else
                                        <a href="{{ route('login') }}" class="btn btn-primary btn-sm event-btn-participate-map-height w-100">Accedi per iscriverti</a>
                                    @endauth
                                </div>
                            </div>

                            <div class="event-meta-stack mb-1">
                                <div class="event-meta-date-box">
                                    <i class="fas fa-calendar"></i>
                                    <span class="event-meta-date-line">
                                        <span class="fw-semibold">Data Evento</span>
                                        <span class="ms-1">{{ $event->italian_event_date ?? $event->date->format('d/m/Y H:i') }}</span>
                                    </span>
                                </div>
                                <div class="event-meta-localita-inline-box">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span class="event-meta-localita-inline-text">
                                        <span><span class="fw-semibold">Città</span> {{ $event->city ?? '—' }}</span>
                                        <span class="event-meta-localita-sep" aria-hidden="true">,</span>
                                        <span class="event-meta-localita-addr-part">
                                            <span class="fw-semibold">Indirizzo</span>
                                            @auth
                                                {{ $event->address ?: '—' }}
                                            @else
                                                <a href="{{ route('login') }}" class="event-meta-localita-login-link">Accedi</a> per l’indirizzo
                                            @endauth
                                        </span>
                                    </span>
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
                                <div class="mb-1">
                                    <span class="badge bg-success fs-6">
                                        <i class="fas fa-euro-sign"></i> {{ $event->formatted_cost }}
                                    </span>
                                </div>
                            @endif

                            @php
                                $mapWithAddress = auth()->check();
                                $mapSrc = $event->googleMapsEmbedUrl($mapWithAddress);
                                $mapOpen = $event->googleMapsExternalUrl($mapWithAddress);
                            @endphp

                            <div class="mb-2 event-iscrizione-mappa-row d-flex align-items-stretch gap-2">
                                @if($event->deadline)
                                    <div class="event-registration-deadline-box rounded flex-shrink-0">
                                        <i class="fas fa-clock"></i>
                                        Iscrizioni {{ $event->isRegistrationOpen() ? 'Entro' : 'chiuse il' }}
                                        {{ $event->deadline->format('d/m/Y H:i') }}
                                    </div>
                                @endif
                                @if($mapSrc)
                                    <button type="button"
                                            class="btn btn-event-map-paired event-map-btn-fill"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#eventMapCollapse"
                                            aria-expanded="false"
                                            aria-controls="eventMapCollapse"
                                            id="btnEventMapToggle">
                                        <i class="fas fa-map"></i> Mappa
                                    </button>
                                @else
                                    <span class="text-muted small flex-shrink-0">Mappa non disponibile</span>
                                @endif
                            </div>

                            @if($mapSrc)
                                <div class="collapse mb-3" id="eventMapCollapse">
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
                                        <a href="{{ $mapOpen }}" class="btn btn-outline-secondary btn-sm event-localita-btn-compact mt-2" target="_blank" rel="noopener noreferrer">
                                            <i class="fas fa-external-link-alt"></i> Apri in Google Maps
                                        </a>
                                    @endif
                                </div>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function () {
                                        var col = document.getElementById('eventMapCollapse');
                                        var iframe = document.getElementById('eventMapIframe');
                                        if (col) {
                                            col.addEventListener('shown.bs.collapse', function () {
                                                if (iframe && iframe.dataset.src && (!iframe.src || iframe.src === 'about:blank')) {
                                                    iframe.src = iframe.dataset.src;
                                                }
                                            });
                                        }
                                    });
                                </script>
                            @endif

                            <div class="mb-4">
                                <h5><i class="fas fa-info-circle"></i> Dettagli</h5>
                                <div class="event-description">
                                    {!! ( (int) $event->id === 2204 ? $event->safe_description_no_images : $event->safe_description ) !!}
                                </div>
                            </div>
                        </div>
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

            <!-- Forum evento: titolo (maiuscolo) + badge + pulsante Forum Commenti; sotto form collassabile e lista -->
                <div class="card mt-4 event-forum-box">
                    <div class="card-header py-2 d-flex flex-nowrap align-items-center justify-content-between gap-2 gap-md-3">
                        <h5 class="mb-0 text-truncate min-w-0 flex-grow-1">
                            <i class="fas fa-comments"></i> FORUM DELL'EVENTO
                            <span class="badge bg-primary">{{ $comments->count() }}</span>
                        </h5>
                        @auth
                            @if(auth()->user()->isApproved())
                                <button class="btn btn-success btn-sm flex-shrink-0 text-nowrap" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#eventCommentCollapse"
                                        aria-expanded="false" aria-controls="eventCommentCollapse">
                                    <i class="fas fa-comment-dots me-1"></i> Inserisci Commento
                                </button>
                            @endif
                        @endauth
                    </div>
                    @auth
                        @if(auth()->user()->isApproved())
                            <div id="eventCommentCollapse" class="collapse">
                                <div class="card-body border-bottom bg-light">
                                    <form action="{{ route('comments.store', $event) }}" method="POST" id="commentForm">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="commentContent" class="form-label">Il tuo commento</label>
                                            <textarea class="form-control" id="commentContent" name="content"
                                                      rows="5" placeholder="Scrivi il tuo commento..." required></textarea>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
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
                        @endif
                    @endauth
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
                <!-- Organizzatore: in alto sulla destra, sopra Partecipanti -->
                <div class="card mb-3 event-organizer-box">
                    <div class="card-header py-2 event-organizer-box__title">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Organizzatore</h5>
                    </div>
                    <div class="card-body py-2">
                        <p class="mb-0">
                            <i class="fas fa-user"></i>
                            <a href="{{ route('profile.show', $event->user) }}?{{ $eventProfileBackQuery }}">
                                {{ $event->user->nickname }}
                            </a>
                        </p>
                    </div>
                </div>

                <!-- Partecipanti -->
                <div class="event-participants-box mb-3">
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
                </div>

                        {{-- Invita un amico --}}
                        @auth
                            @if($userParticipating && auth()->user()->friends()->count() > 0)
                                <div class="mt-3 p-3 bg-light rounded event-invite-box">
                                    <form action="{{ route('events.invite', $event) }}" method="POST"
                                          class="d-flex flex-wrap align-items-center gap-2 mb-0">
                                        @csrf
                                        <h6 class="mb-0 text-nowrap flex-shrink-0" title="Invia un invito a un tuo amico per partecipare a questo evento.">
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
                                    @if($event->allow_guests)
                                        <form action="{{ route('events.add-guest', $event) }}" method="POST" class="mt-2 mb-0">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm w-100">
                                                <i class="fas fa-user-plus"></i> Porta un amico
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        @endauth

                        {{-- Informazioni ospiti --}}
                        {{-- Informazioni ospiti (solo se si vuole riattivare un testo esplicativo) --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @parent
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
            gap: 0.28rem;
            max-width: 100%;
        }

        .event-meta-organizer-box,
        .event-meta-date-box,
        .event-meta-posti-box {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            width: 100%;
            box-sizing: border-box;
            border-radius: 0.375rem;
            padding: 0.38rem 0.65rem;
            font-size: 0.95rem;
            line-height: 1.2;
            border: 2px solid #000;
        }

        /* Box Organizzatore: sfondo grigio, testo verde */
        .event-meta-organizer-box {
            background-color: #dee2e6;
            color: #198754;
        }
        .event-meta-organizer-box .fas {
            color: #198754;
        }

        /* Due colonne uguali, allineate allo stack meta sotto (stesso gap) */
        .event-organizer-strip {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 0.28rem;
            align-items: stretch;
            width: 100%;
            box-sizing: border-box;
        }
        .event-organizer-strip__cell {
            min-width: 0;
        }
        .event-organizer-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.35rem;
            width: 100%;
        }

        /* Iscrivimi: sfondo verde, testo bianco, bordo blu */
        .btn.event-btn-iscrivimi-all-evento {
            border: 2px solid #0d6efd !important;
            font-weight: 600;
        }
        .btn.event-btn-iscrivimi-all-evento.btn-iscrivimi-state-on {
            background-color: #198754 !important;
            color: #fff !important;
        }
        .btn.event-btn-iscrivimi-all-evento.btn-iscrivimi-state-on:hover {
            background-color: #157347 !important;
            color: #fff !important;
            border-color: #0d6efd !important;
        }
        .btn.event-btn-iscrivimi-all-evento.btn-iscrivimi-state-off:disabled {
            background-color: #adb5bd !important;
            color: #fff !important;
            border: 2px solid #0d6efd !important;
            opacity: 1;
        }

        .event-meta-organizer-line {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 0.25rem 0.5rem;
        }

        .event-meta-organizer-box > i,
        .event-meta-date-box > i,
        .event-meta-posti-box > i,
        .event-meta-localita-inline-box > i {
            margin-top: 0;
            flex-shrink: 0;
        }

        .event-meta-date-line {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 0.25rem 0.5rem;
        }

        .event-meta-posti-line {
            flex: 1;
            min-width: 0;
        }

        .event-meta-date-box {
            background-color: #0d6efd;
            color: #fff;
        }
        .event-meta-date-box .fas {
            color: #fff;
        }

        .event-meta-localita-inline-box {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            width: 100%;
            box-sizing: border-box;
            border-radius: 0.375rem;
            padding: 0.38rem 0.65rem;
            font-size: 0.95rem;
            line-height: 1.2;
            border: 2px solid #000;
            background-color: #0d6efd;
            color: #fff;
        }
        .event-meta-localita-inline-text {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 0.2rem 0.45rem;
            flex: 1;
            min-width: 0;
        }
        .event-meta-localita-sep {
            opacity: 0.9;
            user-select: none;
        }
        .event-meta-localita-addr-part {
            min-width: 0;
            word-break: break-word;
        }
        .event-meta-localita-inline-box .fas {
            color: #fff;
            flex-shrink: 0;
        }
        .event-meta-localita-login-link {
            color: #fff;
            text-decoration: underline;
        }

        .event-meta-posti-box {
            background-color: #dee2e6;
            color: #212529;
        }

        .event-meta-posti-box strong {
            color: #212529;
        }

        .event-meta-posti-box > i {
            color: #495057;
        }

        .event-meta-label-iscritti {
            color: #dc3545;
            font-weight: 700;
            text-shadow: none;
        }

        .event-meta-label-liberi {
            color: #198754;
            font-weight: 700;
            text-shadow: none;
        }

        .event-meta-label-totali {
            color: #fd7e14;
            font-weight: 700;
            text-shadow: none;
        }

        .event-meta-posti-hint {
            color: #6c757d;
            font-weight: normal;
        }

        .event-meta-posti-sep {
            color: #adb5bd;
        }

        .event-iscrizione-mappa-row {
            flex-wrap: nowrap;
        }
        .event-iscrizione-mappa-row .btn.btn-event-map-paired.event-map-btn-fill {
            flex: 1 1 auto;
            min-width: 6rem;
            justify-content: center;
        }
        @media (max-width: 575.98px) {
            .event-iscrizione-mappa-row {
                flex-wrap: wrap;
            }
            .event-iscrizione-mappa-row .btn.btn-event-map-paired.event-map-btn-fill {
                flex: 1 1 100%;
                min-width: 0;
            }
        }

        .event-registration-deadline-box {
            background-color: #198754;
            color: #fff;
            border: 2px solid #0d6efd;
            box-shadow: none;
            padding: 0.38rem 0.65rem;
            font-size: 0.95rem;
            line-height: 1.2;
            min-height: 0;
            box-sizing: border-box;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 0.375rem;
        }
        .event-registration-deadline-box .fas {
            opacity: 0.95;
        }

        .btn.btn-event-map-paired {
            background-color: #dc3545;
            color: #fff;
            border: 2px solid #0d6efd;
            box-shadow: none;
            padding: 0.38rem 0.65rem;
            font-size: 0.95rem;
            line-height: 1.2;
            min-height: 0;
            box-sizing: border-box;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 0.375rem;
        }
        .btn.btn-event-map-paired .fas {
            opacity: 0.95;
        }
        .btn.btn-event-map-paired:hover,
        .btn.btn-event-map-paired:focus {
            background-color: #bb2d3b;
            color: #fff;
            border-color: #0d6efd;
        }
        .btn.btn-event-map-paired:focus-visible {
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.45);
        }

        .event-localita-btn-compact {
            padding: 0.1rem 0.45rem;
            font-size: 0.75rem;
            line-height: 1.15;
            min-height: 1.65rem;
            border-radius: 0.25rem;
        }

        .event-participation-btns > .btn,
        .event-participation-btns > form {
            flex: 1 1 0;
            min-width: 0;
        }

        /* Stessa altezza del pulsante Mappa (btn-sm) per Partecipa e Forum Commenta */
        .event-btn-participate-map-height {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.875rem !important;
            line-height: 1.5 !important;
            min-height: 2.125rem;
            box-sizing: border-box;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
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

        /* Box richiesti: bordi blu + titoli con fondo grigio (Organizzatore) */
        .event-main-card {
            border: 2px solid #0d6efd !important;
        }
        .event-main-card > .card-header {
            border-bottom: 1px solid rgba(13, 110, 253, 0.25);
        }
        .event-organizer-box {
            border: 2px solid #0d6efd !important;
        }
        .event-organizer-box__title {
            background: #dee2e6 !important;
            border-bottom: 1px solid rgba(13, 110, 253, 0.35) !important;
        }
        .event-forum-box {
            border: 2px solid #0d6efd !important;
        }

        /* Box "Invita un amico / Porta un amico": bordo marrone */
        .event-invite-box {
            border: 2px solid #8B4513;
        }

        /* Box "Partecipanti": verde */
        .event-participants-box {
            border: 2px solid #198754;
            border-radius: 0.5rem;
            padding: 0.6rem 0.75rem;
        }

        /* Evento al completo: box sotto cover */
        .event-closed-box {
            border: 2px solid #0d6efd;
            background: #dc3545;
            color: #fff;
            border-radius: 0.5rem;
            padding: 0.38rem 0.65rem;
            font-size: 0.95rem;
            line-height: 1.2;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            letter-spacing: 0.06em;
        }

        /* Altezza/padding come "Data evento" */
        .event-btn-meta-height {
            padding: 0.38rem 0.65rem !important;
            font-size: 0.95rem !important;
            line-height: 1.2 !important;
            min-height: 0 !important;
        }

        /* Flash "success" compatto accanto a "Torna alla home" */
        .event-flash-success-sm {
            border: 2px solid #198754 !important;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.375rem;
            max-width: 50%;
            min-height: 2.125rem; /* come btn-sm / box compatti */
        }
        @media (max-width: 575.98px) {
            .event-flash-success-sm {
                max-width: 100%;
                width: 100%;
            }
        }

        /* Bordo marrone (sfondo invariato) */
        .btn.btn-border-brown {
            border: 2px solid #8B4513 !important;
        }
    </style>
@endsection
