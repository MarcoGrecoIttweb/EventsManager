@extends('layouts.app')

@section('title', $event->title . ' - Excursio')

@section('suppress_global_flash', true)

@section('content')
    <style>
        /* Rispetta width/height dall'editor (CKEditor); max-width evita overflow su mobile */
        .event-description img {
            max-width: 100%;
            height: auto;
            object-fit: contain;
            display: block;
            margin: 8px auto;
        }
        /* Descrizioni legacy senza ridimensionamento in editor */
        .event-description img:not([width]) {
            max-height: 250px;
        }

        /* Dettagli evento: corpo descrizione in nero (link restano riconoscibili) */
        .event-main-card .card-body .mb-4 > h5 {
            color: #000;
        }
        .event-main-card .event-description {
            color: #000;
        }
        .event-main-card .event-description a {
            color: #0a58ca !important;
        }
        .event-main-card .event-description a:hover {
            color: #084298 !important;
        }
        .event-main-card .event-description blockquote:not([style*="color"]) {
            color: #000;
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
            @if(session('error'))
                <div class="event-flash-error-sm alert alert-danger mb-0 d-inline-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span class="text-truncate">{{ session('error') }}</span>
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
                            STOP ADESIONI - ( Aperta Lista Riserva)
                        </div>
                    @endif

                    {{-- Gallery --}}
                    @php
                        $galleryImages = $event->images
                            ? $event->images->filter(function ($img) {
                                try {
                                    $p = $img?->path;
                                    if (!is_string($p) || trim($p) === '') {
                                        return false;
                                    }
                                    $full = Storage::disk('public')->path($p);
                                    return is_file($full) && @filesize($full) > 0;
                                } catch (\Throwable $e) {
                                    return false;
                                }
                            })->values()
                            : collect();
                    @endphp
                    @if($galleryImages->count() > 0)
                        <div class="card mb-4" id="eventGalleryCard">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-images"></i> Gallery
                                    <span class="badge bg-primary">{{ $galleryImages->count() }}</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row" id="eventGalleryGrid">
                                    @foreach($galleryImages as $image)
                                        <div class="col-md-4 col-lg-3 mb-3 event-gallery-item">
                                            <a href="{{ Storage::disk('public')->url($image->path) }}" data-lightbox="event-gallery" data-title="{{ $event->title }}">
                                                <img src="{{ Storage::disk('public')->url($image->path) }}" alt="{{ $event->title }}"
                                                     class="img-fluid rounded shadow-sm event-gallery-thumb event-gallery-thumb-js">
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
                                $rawMaxShow = $event->max_participants;
                                $postiTotali = ($rawMaxShow !== null && $rawMaxShow !== '') ? (int) $rawMaxShow : null;
                                $postiLiberi = $postiTotali !== null ? max(0, $postiTotali - $iscrittiCount) : null;
                                $postiLiberiBlink = ($postiTotali !== null && !$event->isFull()) ? max(0, $postiTotali - $iscrittiCount) : null;
                                $eventMetaPostiGapBlink = $postiLiberiBlink !== null && $postiLiberiBlink >= 1 && $postiLiberiBlink <= 2;
                            @endphp
                            <div class="mb-2 event-organizer-strip">
                                <div class="event-organizer-actions event-organizer-strip__cell">
                                    @auth
                                        @if(auth()->user()->isApproved())
                                            @php
                                                $canSendEventComms = auth()->check()
                                                    && auth()->user()->isApproved()
                                                    && (auth()->user()->isAdmin() || (int) ($event->id_organizzatore ?? 0) === (int) auth()->id());
                                                $eventCommsModalId = 'eventCommsModal' . $event->getKey();
                                            @endphp
                                            @if($userParticipating)
                                                @php
                                                    $currentUserGuestsCount = 0;
                                                    $currentUserParticipation = $event->participants()->where('utente.userID', auth()->id())->first();
                                                    if ($currentUserParticipation) {
                                                        $currentUserGuestsCount = $currentUserParticipation->pivot->amici ?? 0;
                                                    }
                                                @endphp
                                                <div class="d-flex justify-content-end">
                                                    <div class="d-flex flex-nowrap gap-2 align-items-stretch event-participation-btns event-participation-btns--toglimi">
                                                        <form action="{{ route('events.cancel', $event) }}" method="POST" class="mb-0 d-flex align-items-stretch">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm event-btn-participate-map-height event-btn-meta-height btn-border-brown">
                                                                <i class="fas fa-times"></i> Toglimi
                                                            </button>
                                                        </form>
                                                        @if($canSendEventComms)
                                                            <button type="button"
                                                                    class="btn btn-success btn-sm event-btn-participate-map-height event-btn-meta-height btn-border-brown"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#{{ $eventCommsModalId }}">
                                                                <i class="fas fa-bullhorn"></i> Comunicazioni
                                                            </button>
                                                        @endif
                                                            {{-- Lista partecipanti sempre visibile --}}
                                                        @if($currentUserGuestsCount > 0)
                                                            <div class="event-porti-guest-box event-btn-meta-height" role="status">
                                                                <span class="fw-semibold">Porti</span>
                                                                <span class="ms-1">{{ $currentUserGuestsCount }}</span>
                                                                <span class="ms-1">{{ $currentUserGuestsCount === 1 ? 'Ospite' : 'Ospiti' }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
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
                                                        <button type="submit" class="btn btn-sm w-100 event-btn-participate-map-height event-btn-iscrivimi-all-evento btn-iscrivimi-state-{{ $cannotJoin ? 'off' : 'on' }} {{ $event->isFull() ? 'btn-iscrivimi-full' : '' }}"
                                                            {{ $cannotJoin ? 'disabled' : '' }}>
                                                            <i class="fas fa-{{ $joinIcon }}"></i>
                                                            {{ $joinLabel }}
                                                        </button>
                                                    </form>
                                                    @if($canSendEventComms)
                                                        <button type="button"
                                                                class="btn btn-success btn-sm event-btn-participate-map-height btn-border-brown w-100"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#{{ $eventCommsModalId }}">
                                                            <i class="fas fa-bullhorn"></i> Comunicazioni
                                                        </button>
                                                    @endif
                                                    @if($cannotJoin)
                                                        @php
                                                            $wlBoxId = 'waitlistBoxEventShow' . $event->getKey();
                                                            $waitName = auth()->user()->nickname ?? trim((auth()->user()->nome ?? '') . ' ' . (auth()->user()->cognome ?? '')) ?: 'Utente';
                                                        @endphp
                                                        <div class="w-100">
                                                            <div class="mt-2 p-2 rounded event-waitlist-box">
                                                                @if(session('waitlist_flash_event_id') == $event->getKey())
                                                                    @if(session('success'))
                                                                        <div class="alert alert-success alert-sm mb-2 py-2">
                                                                            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                                                                        </div>
                                                                    @elseif(session('error'))
                                                                        <div class="alert alert-danger alert-sm mb-2 py-2">
                                                                            <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
                                                                        </div>
                                                                    @endif
                                                                @endif
                                                                @if(isset($isWaitlisted) && $isWaitlisted)
                                                                    <div class="small fw-semibold mb-2">
                                                                        <i class="fas fa-hourglass-half"></i>
                                                                        {{ $waitName }} sei in attesa qualora si liberassero posti..
                                                                    </div>
                                                                @else
                                                                    <button type="button"
                                                                            class="btn btn-warning btn-sm w-100"
                                                                            data-bs-toggle="collapse"
                                                                            data-bs-target="#{{ $wlBoxId }}"
                                                                            aria-expanded="false"
                                                                            aria-controls="{{ $wlBoxId }}">
                                                                        <i class="fas fa-clipboard-list"></i>
                                                                        <span class="event-waitlist-cta-blink">Inseriscimi in lista di Riserva</span>
                                                                    </button>

                                                                    <div class="collapse mt-2" id="{{ $wlBoxId }}">
                                                                        <div class="event-waitlist-explain p-2 rounded">
                                                                            <div class="small">
                                                                                <div class="fw-semibold mb-1">
                                                                                    <i class="fas fa-info-circle"></i> Cosa succede
                                                                                </div>
                                                                                <div class="mb-2">
                                                                                    Ti inseriamo in lista d’attesa per questo evento. Se si libera un posto,
                                                                                    la prima persona in lista riceve una mail e può iscriversi.
                                                                                </div>
                                                                                <div class="d-flex flex-wrap gap-2">
                                                                                    <form action="{{ route('events.waitlist.join', $event) }}" method="POST" class="mb-0 flex-grow-1">
                                                                                        @csrf
                                                                                        <button type="submit" class="btn btn-sm btn-primary w-100">
                                                                                            <i class="fas fa-check"></i> Sì, inseriscimi
                                                                                        </button>
                                                                                    </form>
                                                                                    <button type="button"
                                                                                            class="btn btn-sm btn-outline-secondary flex-grow-1"
                                                                                            data-bs-toggle="collapse"
                                                                                            data-bs-target="#{{ $wlBoxId }}"
                                                                                            aria-expanded="true"
                                                                                            aria-controls="{{ $wlBoxId }}">
                                                                                        <i class="fas fa-times"></i> No, grazie
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                @if(isset($waitlistEntries) && is_iterable($waitlistEntries) && count($waitlistEntries) > 0)
                                                                    <div class="mt-2 small">
                                                                        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap fw-semibold mb-1">
                                                                            <div class="text-truncate">
                                                                                <i class="fas fa-list"></i> Persone in lista d’attesa
                                                                            </div>
                                                                            @if(isset($isWaitlisted) && $isWaitlisted)
                                                                                <form action="{{ route('events.waitlist.leave', $event) }}" method="POST" class="mb-0 flex-shrink-0">
                                                                                    @csrf
                                                                                    @method('DELETE')
                                                                                    <button type="submit"
                                                                                            class="btn btn-success btn-sm event-wl-leave-btn"
                                                                                            onclick="return confirm('Vuoi uscire dalla lista d’attesa?');">
                                                                                        <i class="fas fa-user-slash"></i> Esci dalla lista di attesa
                                                                                    </button>
                                                                                </form>
                                                                            @endif
                                                                        </div>
                                                                        <ul class="mb-0 ps-3">
                                                                            @foreach($waitlistEntries as $wl)
                                                                                @php
                                                                                    $wlName =
                                                                                        $wl->user?->nickname
                                                                                        ?? trim((string) (($wl->user?->nome ?? '') . ' ' . ($wl->user?->cognome ?? '')))
                                                                                        ?: ($wl->display_name ?? 'Utente');
                                                                                @endphp
                                                                                <li>{{ $wlName }}</li>
                                                                            @endforeach
                                                                        </ul>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if($event->isFull())
                                                        <span class="small text-warning mb-0"><i class="fas fa-users-slash"></i> Posti esauriti</span>
                                                    @endif
                                                </div>
                                            @endif

                                            @if($canSendEventComms)
                                                <div class="modal fade" id="{{ $eventCommsModalId }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">
                                                                    <i class="fas fa-bullhorn text-success"></i>
                                                                    Comunicazioni agli iscritti
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                                            </div>
                                                            <form method="POST" action="{{ route('events.communications.send', $event) }}">
                                                                @csrf
                                                                <div class="modal-body">
                                                                    <div class="alert alert-light small mb-3">
                                                                        Verrà inviata una email a <strong>tutti gli iscritti</strong> all’evento che hanno un indirizzo email valido.
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-semibold" for="eventCommsSubject{{ $event->getKey() }}">Oggetto</label>
                                                                        <input type="text"
                                                                               class="form-control @error('subject') is-invalid @enderror"
                                                                               id="eventCommsSubject{{ $event->getKey() }}"
                                                                               name="subject"
                                                                               maxlength="140"
                                                                               value="{{ old('subject', 'Comunicazione relativa ad evento: ' . $event->title) }}"
                                                                               required>
                                                                        @error('subject')
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                    <div class="mb-0">
                                                                        <label class="form-label fw-semibold" for="eventCommsMessage{{ $event->getKey() }}">Messaggio</label>
                                                                        @php
                                                                            $eventDate = $event->date;
                                                                            $defaultMessage = old('message');
                                                                            if (!$defaultMessage && $eventDate) {
                                                                                $domani = now()->addDay()->locale('it')->translatedFormat('l j F Y');
                                                                                $dataEvento = $eventDate->locale('it')->translatedFormat('l j F Y');
                                                                                $oraEvento = $eventDate->format('H:i');
                                                                                $defaultMessage =
"<p>Ciao</p><p>Ricevi questa comunicazione, per ricordarti che ti sei iscritto/a ad evento di Excursio \"" . e($event->title) . "\" in programma per " . $dataEvento . " ore " . $oraEvento . "<br>L'evento è confermato.</p><p>Se per qualche imprevisto tu ed eventuali amici da te iscritti non potete partecipare e volete disdire, ti chiedo cortesemente di comunicarmelo entro la mattinata " . $domani . " per confermare i posti al Locale.</p><p>Puoi comunicarlo depennandoti dall'evento stesso, dal sito www.excursio.org, o inviando una mail, a excursio@libero.it oppure telefonicamente chiamando, o con sms Cell. 3387717376</p><p>Grazie! Loris</p>";
                                                                            }
                                                                        @endphp
                                                                        <textarea class="form-control @error('message') is-invalid @enderror"
                                                                                  id="eventCommsMessage{{ $event->getKey() }}"
                                                                                  name="message"
                                                                                  rows="7"
                                                                                  maxlength="4000"
                                                                                  required>{{ $defaultMessage }}</textarea>
                                                                        @error('message')
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                                                        <i class="fas fa-times"></i> Annulla
                                                                    </button>
                                                                    <button type="submit" class="btn btn-success">
                                                                        <i class="fas fa-paper-plane"></i> Invia a tutti gli iscritti
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
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

                            @php
                                $venueDisplay = trim((string) ($event->venue ?? ''));
                                $mapWithAddress = auth()->check();
                                $mapSrc = $event->googleMapsEmbedUrl($mapWithAddress);
                                $mapOpen = $event->googleMapsExternalUrl($mapWithAddress);
                            @endphp

                            <div class="event-meta-stack mb-1">
                                {{-- Riga 1: Data evento | Nome locale --}}
                                <div class="event-meta-row event-meta-row--line1">
                                    <div class="event-meta-date-box event-meta-row__cell event-meta-date-box--hint"
                                         title="Indica data e ora di inizio dell’evento.">
                                        <i class="fas fa-calendar"></i>
                                        <span class="event-meta-date-line">
                                            <span class="fw-semibold">Data Evento</span>
                                            <span class="ms-1">{{ $event->italian_event_date ?? $event->date->format('d/m/Y H:i') }}</span>
                                        </span>
                                    </div>
                                    <div class="event-meta-place-box event-meta-row__cell">
                                        <i class="fas fa-store"></i>
                                        <span class="event-meta-place-line">
                                            <span class="fw-semibold">Nome del locale</span>
                                            <span class="ms-1">{{ $venueDisplay !== '' ? $venueDisplay : '—' }}</span>
                                        </span>
                                    </div>
                                </div>

                                {{-- Riga 2: Indirizzo | Città --}}
                                <div class="event-meta-row event-meta-row--line2 mt-1">
                                    <div class="event-meta-place-box event-meta-row__cell">
                                        <i class="fas fa-road"></i>
                                        <span class="event-meta-place-line">
                                            <span class="fw-semibold">Indirizzo</span>
                                            @auth
                                                <span class="ms-1">{{ $event->address ?: '—' }}</span>
                                            @else
                                                <span class="ms-1">
                                                    <a href="{{ route('login') }}" class="event-meta-localita-login-link">Accedi</a> per l’indirizzo
                                                </span>
                                            @endauth
                                        </span>
                                    </div>
                                    <div class="event-meta-place-box event-meta-row__cell">
                                        <i class="fas fa-city"></i>
                                        <span class="event-meta-place-line">
                                            <span class="fw-semibold">Città</span>
                                            <span class="ms-1">{{ $event->city ? $event->city : '—' }}</span>
                                        </span>
                                    </div>
                                </div>

                                {{-- Riga 3: Prezzo | Mappa | Iscrizioni | Box iscritti --}}
                                <div class="event-meta-row event-meta-row--line3 mt-1">
                                    <div class="event-meta-price-box event-meta-row__cell">
                                        <i class="fas fa-euro-sign"></i>
                                        <span class="event-meta-price-line">
                                            <span class="fw-semibold">€</span>
                                            <span class="ms-1">{{ $event->formatted_cost ?? '0,00' }}</span>
                                        </span>
                                    </div>
                                    <div class="event-meta-map-slot event-meta-row__cell">
                                        @if($mapSrc)
                                            <button type="button"
                                                    class="btn btn-event-map-paired event-map-btn-fill w-100 h-100"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#eventMapCollapse"
                                                    aria-expanded="false"
                                                    aria-controls="eventMapCollapse"
                                                    id="btnEventMapToggle">
                                                <i class="fas fa-map"></i> Mappa
                                            </button>
                                        @else
                                            <div class="event-meta-map-unavailable">
                                                <i class="fas fa-map"></i> Mappa non disponibile
                                            </div>
                                        @endif
                                    </div>
                                    @if($event->deadline)
                                        @php
                                            $iscrOpen = $event->isRegistrationOpen();
                                            $deadlineTs = optional($event->deadline)->timestamp ?? null;
                                            $nowTs = now()->timestamp;
                                            $secondsToDeadline = is_int($deadlineTs) ? ($deadlineTs - $nowTs) : null;
                                            $iscrSoon = $iscrOpen && is_int($secondsToDeadline) && $secondsToDeadline > 0 && $secondsToDeadline <= 86400; // 24h
                                        @endphp
                                        <div class="event-registration-deadline-box event-meta-row__cell rounded">
                                            <i class="fas fa-clock"></i>
                                            <span class="event-meta-iscr-line {{ $iscrOpen ? ($iscrSoon ? 'event-iscr-status--soon' : 'event-iscr-status--open') : 'event-iscr-status--closed' }}">
                                                <span class="fw-semibold">Iscrizioni</span>
                                                {{ $iscrOpen ? ' entro il ' : ' chiuse il ' }}
                                                {{ $event->deadline->format('d/m/Y H:i') }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="event-meta-iscr-empty-box event-meta-row__cell">
                                            <i class="fas fa-clock"></i>
                                            <span class="event-meta-iscr-line">
                                                <span class="fw-semibold">Iscrizioni</span>
                                                <span class="ms-1">—</span>
                                            </span>
                                        </div>
                                    @endif
                                    <div class="event-meta-posti-box event-meta-row__cell event-meta-posti-box--hint {{ $eventMetaPostiGapBlink ? 'event-meta-posti-box--part-gap' : '' }}"
                                         title="{{ $eventMetaPostiGapBlink
                                             ? 'Restano 1 o 2 posti liberi rispetto al massimo: il box lampeggia per segnalare che l’evento è quasi al completo.'
                                             : ($postiTotali !== null
                                                 ? 'Iscritti (Iscr.), posti ancora liberi (Lib.) e numero massimo di posti previsto (Tot.).'
                                                 : 'Conteggio partecipanti (iscritti più eventuali ospiti). Senza un massimo definito, Lib. e Tot. non si applicano.') }}">
                                        <i class="fas fa-users"></i>
                                        <span class="event-meta-posti-line">
                                            <span class="event-meta-label-iscritti">Iscr.</span> <strong>{{ $iscrittiCount }}</strong>
                                            <span class="event-meta-posti-sep"> / </span>
                                            <span class="event-meta-label-liberi">Lib.</span> <strong>{{ $postiLiberi !== null ? $postiLiberi : '—' }}</strong>
                                            <span class="event-meta-posti-sep"> / </span>
                                            <span class="event-meta-label-totali">Tot.</span> <strong>{{ $postiTotali !== null ? $postiTotali : '—' }}</strong>
                                            @if($eventMetaPostiGapBlink)
                                                <span class="event-meta-ultimi-posti ms-1">{{ $postiLiberi == 1 ? 'Ultimo Posto Disponibile' : 'Ultimi ' . $postiLiberi . ' Posti Disponibili' }}</span>
                                            @endif
                                            @if($postiTotali === null)
                                                <small class="event-meta-posti-hint"> (posti illimitati)</small>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                            @if($mapSrc)
                                @externalmedia
                                <div class="collapse mb-3" id="eventMapCollapse">
                                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                        <p class="small text-muted mb-0">
                                            <i class="fas fa-map"></i> Google Maps
                                        @if(!$mapWithAddress)
                                                <span class="d-block mt-1">Posizione approssimativa: effettua l’accesso per includere l’indirizzo nella ricerca.</span>
                                        @endif
                                        </p>
                                        <button type="button"
                                                class="btn btn-outline-secondary btn-sm flex-shrink-0"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#eventMapCollapse"
                                                aria-controls="eventMapCollapse"
                                                aria-label="Riduci mappa">
                                            <i class="fas fa-compress-alt"></i> Riduci
                                        </button>
                                    </div>
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
                                        var btn = document.getElementById('btnEventMapToggle');

                                        function setMapBtnState(open) {
                                            if (!btn) return;
                                            btn.innerHTML = open
                                                ? '<i class="fas fa-compress-alt"></i> Riduci mappa'
                                                : '<i class="fas fa-map"></i> Mappa';
                                            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                                        }

                                        if (col) {
                                            col.addEventListener('shown.bs.collapse', function () {
                                                if (iframe && iframe.dataset.src && (!iframe.src || iframe.src === 'about:blank')) {
                                                    iframe.src = iframe.dataset.src;
                                                }
                                                setMapBtnState(true);
                                            });
                                            col.addEventListener('hidden.bs.collapse', function () {
                                                setMapBtnState(false);
                                            });
                                        }

                                        // Stato iniziale
                                        if (col) {
                                            setMapBtnState(col.classList.contains('show'));
                                        }
                                    });
                                </script>
                                @else
                                    <div class="alert alert-light border shadow-sm mb-3">
                                        <div class="fw-semibold mb-1"><i class="fas fa-map"></i> Mappa disattivata</div>
                                        <div class="small text-muted">
                                            Per visualizzare la mappa, abilita i <strong>Contenuti esterni (Mappe)</strong> nelle preferenze cookie.
                                        </div>
                                        <button type="button"
                                                class="btn btn-success btn-sm mt-2"
                                                onclick="(function(){var el=document.getElementById('cookieConsentModal'); if(el && typeof bootstrap!=='undefined'){bootstrap.Modal.getOrCreateInstance(el,{backdrop:'static',keyboard:false}).show();}})();">
                                            Apri preferenze cookie
                                        </button>
                                    </div>
                                @endexternalmedia
                            @endif

                            <div class="mb-4">
                                <h5><i class="fas fa-info-circle"></i> Dettagli Evento</h5>
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

                {{-- Smartphone: sposta Partecipanti + Invita un amico sopra il forum --}}
                <div class="d-block d-md-none mt-3">
                    <!-- Partecipanti (mobile) -->
                    <div class="event-participants-box mb-3">
                        <h5 class="mb-2">
                            <i class="fas fa-users"></i> Partecipanti
                            <span class="badge rounded-pill text-white event-show-part-pill--hint {{ $event->isFull() ? 'bg-danger' : 'bg-secondary' }} {{ $eventMetaPostiGapBlink ? 'event-show-part-pill--gap' : '' }}"
                                  title="{{ $eventMetaPostiGapBlink
                                      ? 'Restano 1 o 2 posti liberi rispetto al massimo: il box lampeggia per segnalare che l’evento è quasi al completo.'
                                      : ($postiTotali !== null
                                          ? 'Mostra quanti partecipanti ci sono (iscritti più eventuali ospiti) rispetto al numero massimo di posti previsto dall’organizzatore.'
                                          : 'Mostra il numero di partecipanti (iscritti più eventuali ospiti); l’organizzatore non ha indicato un numero massimo di posti.') }}">
                                <i class="fas fa-users me-1" aria-hidden="true"></i><strong>{{ $event->participants_count }}</strong>@if($postiTotali !== null)<span class="text-white-50 fw-normal"> / </span><strong>{{ $postiTotali }}</strong>@endif
                            </span>
                            @auth
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('events.print', $event) }}" class="btn btn-sm btn-outline-secondary ms-1" target="_blank" title="Stampa lista (solo admin)">
                                        <i class="fas fa-print"></i>
                                    </a>
                                @endif
                            @endauth
                        </h5>

                        @php $canSeeList = true; @endphp
                        @if($event->participants->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($event->participants as $participant)
                                    @php
                                        $currentUserIsParticipant = auth()->check() && auth()->id() === $participant->getKey();
                                        $canAddMoreGuests = $currentUserIsParticipant && $event->canAddMoreGuests($participant);
                                        $hasGuests = $participant->pivot->amici > 0;
                                        $ospitiEntries = \App\Support\OspitiGuestStore::decode($participant->pivot->ospiti_inseriti_il ?? null);
                                        $showGuestRows = $hasGuests && ($canSeeList || $currentUserIsParticipant);
                                    @endphp

                                    <div class="mb-2" id="participant-mobile-{{ $participant->getKey() }}">
                                        <div class="d-flex align-items-center flex-wrap gap-2">
                                            <div>
                                                <a href="{{ route('profile.show', $participant) }}?{{ $eventProfileBackQuery }}" class="text-decoration-none">
                                                    <i class="fas fa-user"></i> {{ $participant->nickname }}
                                                </a>
                                                @if($currentUserIsParticipant)
                                                    <span class="badge bg-primary ms-1">Tu</span>
                                                @endif
                                            </div>
                                            @auth
                                                @if($currentUserIsParticipant)
                                                    @php
                                                        $addGuestBlockReason = '';
                                                        if (!$canAddMoreGuests) {
                                                            if (!$event->allow_guests) {
                                                                $addGuestBlockReason = 'Questo evento non permette di portare ospiti.';
                                                            } elseif ($event->isFull()) {
                                                                $addGuestBlockReason = 'L\'evento è al completo: non puoi aggiungere altri ospiti.';
                                                            } else {
                                                                $addGuestBlockReason = 'Hai raggiunto il limite di ospiti consentiti per questo evento.';
                                                            }
                                                        }
                                                    @endphp
                                                    <form action="{{ route('events.add-guest', $event) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-success btn-sm"
                                                                @if(!$canAddMoreGuests) disabled aria-disabled="true" @endif
                                                                title="{{ $canAddMoreGuests ? 'Con + aggiungi un amico, poi scrivi il nome nella riga sotto' : $addGuestBlockReason }}"
                                                                aria-label="{{ $canAddMoreGuests ? 'Con + aggiungi un amico, poi scrivi il nome nella riga sotto' : $addGuestBlockReason }}">
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

                    {{-- Porta un amico / ospite (mobile) --}}
                    @auth
                        @if($userParticipating && $event->allow_guests)
                            <div class="mt-3 p-3 bg-light rounded event-invite-box">
                                    @php
                                        $authCanAddMoreGuests = auth()->user()->isApproved() && $event->canAddMoreGuests(auth()->user());
                                        $addGuestBlockReasonInvite = '';
                                        if (!$authCanAddMoreGuests) {
                                            if ($event->isFull()) {
                                                $addGuestBlockReasonInvite = 'L\'evento è al completo: non puoi aggiungere altri ospiti.';
                                            } else {
                                                $addGuestBlockReasonInvite = 'Hai raggiunto il limite di ospiti consentiti per questo evento.';
                                            }
                                        }
                                    @endphp
                                    <form action="{{ route('events.add-guest', $event) }}" method="POST" class="mb-0">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm w-100"
                                                @if(!$authCanAddMoreGuests) disabled aria-disabled="true" @endif
                                                title="{{ $authCanAddMoreGuests ? 'Aggiungi una riga ospite in elenco' : $addGuestBlockReasonInvite }}">
                                            <i class="fas fa-user-plus"></i> Porta un amico
                                        </button>
                                    </form>
                            </div>
                        @endif
                    @endauth
                </div>

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
                                    <form action="{{ url('events/' . $event->getKey() . '/comments') }}" method="POST" id="commentForm">
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
                                                @if(auth()->user()->canManageEvents() && (int) $comment->id_utente !== (int) auth()->id())
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-success"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#replyForm-{{ $comment->getKey() }}"
                                                            aria-expanded="false"
                                                            aria-controls="replyForm-{{ $comment->getKey() }}"
                                                            title="Rispondi nel forum (con email all'utente)">
                                                        <i class="fas fa-reply"></i> Rispondi
                                                    </button>
                                                @endif
                                                {{-- Pulsante modifica (autore o amministratore) --}}
                                                @if(auth()->id() === $comment->id_utente || auth()->user()->isAdmin())
                                                    <a href="{{ url('comments/' . $comment->getKey() . '/edit') }}"
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="{{ auth()->user()->isAdmin() && auth()->id() !== $comment->id_utente ? 'Modifica commento (admin)' : 'Modifica commento' }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif

                                                {{-- Pulsante eliminazione (proprietario o admin) --}}
                                                @if(auth()->id() === $comment->id_utente || auth()->user()->isAdmin())
                                                    <form action="{{ url('comments/' . $comment->getKey()) }}" method="POST" class="d-inline">
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

                                    @auth
                                        @if(auth()->user()->canManageEvents() && (int) $comment->id_utente !== (int) auth()->id())
                                            <div class="collapse mt-3" id="replyForm-{{ $comment->getKey() }}">
                                                <form action="{{ route('comments.reply', $comment) }}" method="POST">
                                                    @csrf
                                                    <label for="replyContent-{{ $comment->getKey() }}" class="form-label small mb-1">
                                                        <i class="fas fa-reply"></i> Risposta a
                                                        <strong>{{ $comment->user?->nickname ?? 'utente' }}</strong>
                                                    </label>
                                                    <textarea class="form-control"
                                                              id="replyContent-{{ $comment->getKey() }}"
                                                              name="content"
                                                              rows="5"
                                                              placeholder="Scrivi la risposta…"
                                                              required></textarea>
                                                    <p class="small text-muted mb-2 mt-1">
                                                        <i class="fas fa-info-circle"></i> Puoi usare la formattazione, inserire link e immagini.
                                                    </p>
                                                    <div class="d-flex justify-content-end gap-2 mt-2">
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-secondary"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#replyForm-{{ $comment->getKey() }}">
                                                            Annulla
                                                        </button>
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="fas fa-paper-plane"></i> Invia risposta
                                                        </button>
                                                    </div>
                                                    <p class="small text-muted mb-0 mt-2">
                                                        Email inviata all'utente.
                                                    </p>
                                                </form>
                                            </div>
                                        @endif
                                    @endauth
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

                @if(isset($waitlistEntries) && is_iterable($waitlistEntries) && count($waitlistEntries) > 0)
                    <div class="card mb-3 event-waitlist-side">
                        <div class="card-header py-2 event-waitlist-side__title">
                            <h5 class="mb-0">
                                <i class="fas fa-hourglass-half"></i> Lista d’attesa
                                <span class="badge bg-primary">{{ count($waitlistEntries) }}</span>
                            </h5>
                        </div>
                        <div class="card-body py-2">
                            <div class="small event-waitlist-side__hint mb-2">
                                Utenti in attesa per questo evento (in ordine di inserimento).
                            </div>
                            <ul class="mb-0 ps-3 small">
                                @foreach($waitlistEntries as $wl)
                                    @php
                                        $wlName =
                                            $wl->user?->nickname
                                            ?? trim((string) (($wl->user?->nome ?? '') . ' ' . ($wl->user?->cognome ?? '')))
                                            ?: ($wl->display_name ?? 'Utente');
                                    @endphp
                                    <li>
                                        @if($wl->user)
                                            <a href="{{ route('profile.show', $wl->user) }}?{{ $eventProfileBackQuery }}" class="text-decoration-none">
                                                <i class="fas fa-user"></i> {{ $wlName }}
                                            </a>
                                        @else
                                            <i class="fas fa-user"></i> {{ $wlName }}
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Partecipanti -->
                <div class="event-participants-box mb-3 d-none d-md-block">
                <h5 class="mb-2">
                    <i class="fas fa-users"></i> Partecipanti
                    <span class="badge rounded-pill text-white event-show-part-pill--hint {{ $event->isFull() ? 'bg-danger' : 'bg-secondary' }} {{ $eventMetaPostiGapBlink ? 'event-show-part-pill--gap' : '' }}"
                          title="{{ $eventMetaPostiGapBlink
                              ? 'Restano 1 o 2 posti liberi rispetto al massimo: il box lampeggia per segnalare che l’evento è quasi al completo.'
                              : ($postiTotali !== null
                                  ? 'Mostra quanti partecipanti ci sono (iscritti più eventuali ospiti) rispetto al numero massimo di posti previsto dall’organizzatore.'
                                  : 'Mostra il numero di partecipanti (iscritti più eventuali ospiti); l’organizzatore non ha indicato un numero massimo di posti.') }}">
                        <i class="fas fa-users me-1" aria-hidden="true"></i><strong>{{ $event->participants_count }}</strong>@if($postiTotali !== null)<span class="text-white-50 fw-normal"> / </span><strong>{{ $postiTotali }}</strong>@endif
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
                @php $canSeeList = true; @endphp
                @if($event->participants->count() > 0)
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
                                                    @php
                                                        $addGuestBlockReason = '';
                                                        if (!$canAddMoreGuests) {
                                                            if (!$event->allow_guests) {
                                                                $addGuestBlockReason = 'Questo evento non permette di portare ospiti.';
                                                            } elseif ($event->isFull()) {
                                                                $addGuestBlockReason = 'L\'evento è al completo: non puoi aggiungere altri ospiti.';
                                                            } else {
                                                                $addGuestBlockReason = 'Hai raggiunto il limite di ospiti consentiti per questo evento.';
                                                            }
                                                        }
                                                    @endphp
                                                    <form action="{{ route('events.add-guest', $event) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-success btn-sm"
                                                                @if(!$canAddMoreGuests) disabled aria-disabled="true" @endif
                                                                title="{{ $canAddMoreGuests ? 'Con + aggiungi un amico, poi scrivi il nome nella riga sotto' : $addGuestBlockReason }}"
                                                                aria-label="{{ $canAddMoreGuests ? 'Con + aggiungi un amico, poi scrivi il nome nella riga sotto' : $addGuestBlockReason }}">
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

                        {{-- Porta un amico / ospite (desktop) --}}
                        @auth
                            @if($userParticipating && $event->allow_guests)
                                @php
                                    $authCanAddMoreGuests = auth()->user()->isApproved() && $event->canAddMoreGuests(auth()->user());
                                    $addGuestBlockReasonInvite = '';
                                    if (!$authCanAddMoreGuests) {
                                        if ($event->isFull()) {
                                            $addGuestBlockReasonInvite = 'L\'evento è al completo: non puoi aggiungere altri ospiti.';
                                        } else {
                                            $addGuestBlockReasonInvite = 'Hai raggiunto il limite di ospiti consentiti per questo evento.';
                                        }
                                    }
                                @endphp
                                <div class="mt-3 p-3 bg-light rounded event-invite-box d-none d-md-block">
                                    <form action="{{ route('events.add-guest', $event) }}" method="POST" class="mb-0">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm w-100"
                                                @if(!$authCanAddMoreGuests) disabled aria-disabled="true" @endif
                                                title="{{ $authCanAddMoreGuests ? 'Aggiungi una riga ospite in elenco' : $addGuestBlockReasonInvite }}">
                                            <i class="fas fa-user-plus"></i> Porta un amico
                                        </button>
                                    </form>
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
    @include('partials.ckeditor4-description', [
        'field' => 'commentContent',
        'height' => 260,
        'editable_line_height' => 1.22,
        'editable_p_margin' => '0.2em',
        'lazy_field_prefix' => 'replyContent-',
        'lazy_height' => 260,
    ])
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

            // CKEditor nel modal Comunicazioni agli iscritti: inizializza all'apertura
            @if(isset($eventCommsModalId))
            var commsModalEl = document.getElementById(@json($eventCommsModalId));
            if (commsModalEl) {
                commsModalEl.addEventListener('show.bs.modal', function () {
                    var textareaId = 'eventCommsMessage{{ $event->getKey() }}';
                    if (typeof CKEDITOR !== 'undefined' && !CKEDITOR.instances[textareaId]) {
                        CKEDITOR.replace(textareaId, {
                            language: 'it',
                            height: 280,
                            removePlugins: 'elementspath',
                            resize_dir: 'vertical',
                            versionCheck: false,
                            allowedContent: true,
                            pasteFilter: null,
                            pasteFromWordRemoveFontStyles: false,
                            pasteFromWordRemoveStyles: false,
                            filebrowserImageUploadUrl: @json(route('ckeditor.upload', ['_token' => csrf_token()])),
                            filebrowserUploadUrl: @json(route('ckeditor.upload', ['_token' => csrf_token()])),
                            filebrowserUploadMethod: 'form'
                        });
                    }
                });

                // Sincronizza CKEditor nella textarea prima dell'invio
                var commsForm = commsModalEl.querySelector('form');
                if (commsForm) {
                    commsForm.addEventListener('submit', function () {
                        var textareaId = 'eventCommsMessage{{ $event->getKey() }}';
                        if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances[textareaId]) {
                            CKEDITOR.instances[textareaId].updateElement();
                        }
                    });
                }
            }
            @endif

        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var card = document.getElementById('eventGalleryCard');
            var grid = document.getElementById('eventGalleryGrid');
            if (!card || !grid) return;

            function cleanupIfEmpty() {
                var items = grid.querySelectorAll('.event-gallery-item');
                if (!items || items.length === 0) {
                    card.remove();
                }
            }

            var imgs = grid.querySelectorAll('img.event-gallery-thumb-js');
            if (!imgs || imgs.length === 0) {
                cleanupIfEmpty();
                return;
            }

            imgs.forEach(function (img) {
                img.addEventListener('error', function () {
                    var item = img.closest('.event-gallery-item');
                    if (item) item.remove();
                    cleanupIfEmpty();
                });
            });

            // Se per qualche motivo sono tutte "rotte" ma non scatta error subito, ripulisci dopo un attimo.
            setTimeout(function () {
                var anyOk = false;
                imgs.forEach(function (img) {
                    if (img.complete && img.naturalWidth > 0) anyOk = true;
                });
                if (!anyOk) {
                    grid.querySelectorAll('.event-gallery-item').forEach(function (it) { it.remove(); });
                    cleanupIfEmpty();
                }
            }, 1200);
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

        /* Immagine intera nel riquadro (nessun taglio); riempie il più possibile con bande se il ratio differisce */
        .event-cover-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
        }

        @media (min-width: 768px) {
            .event-cover-frame {
                max-height: min(52vh, 560px);
            }
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

        /* Altezza compatta allineata al box Data evento */
        .event-meta-stack .event-meta-date-box,
        .event-meta-stack .event-meta-place-box,
        .event-meta-stack .event-meta-posti-box,
        .event-meta-stack .event-meta-price-box,
        .event-meta-stack .event-meta-iscr-empty-box,
        .event-meta-stack .event-registration-deadline-box {
            padding: 0.26rem 0.5rem;
            font-size: 0.9rem;
            line-height: 1.15;
        }
        .event-meta-stack .event-meta-map-slot .btn.btn-event-map-paired,
        .event-meta-stack .event-meta-map-unavailable {
            padding: 0.26rem 0.5rem;
            font-size: 0.9rem;
            line-height: 1.15;
            min-height: 0;
        }

        /* Data, luogo, prezzo, iscrizioni: grigio, testo nero, bordo verde */
        .event-meta-stack .event-meta-date-box,
        .event-meta-stack .event-meta-place-box,
        .event-meta-stack .event-meta-price-box,
        .event-meta-stack .event-meta-iscr-empty-box,
        .event-meta-stack .event-registration-deadline-box {
            background-color: #dee2e6;
            color: #000;
            border: none;
        }
        .event-meta-stack .event-meta-date-box .fas,
        .event-meta-stack .event-meta-place-box .fas,
        .event-meta-stack .event-meta-price-box > i,
        .event-meta-stack .event-meta-iscr-empty-box > i,
        .event-meta-stack .event-registration-deadline-box .fas {
            color: #000;
        }
        .event-meta-stack .event-meta-localita-login-link {
            color: #000;
        }

        .event-meta-row__cell {
            min-width: 0;
        }

        /* Riga 1: Data evento | Nome locale */
        .event-meta-row--line1 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.28rem;
            width: 100%;
            box-sizing: border-box;
        }
        @media (max-width: 575.98px) {
            .event-meta-row--line1 {
                grid-template-columns: 1fr;
            }
        }

        /* Riga 2: Indirizzo | Città | Mappa */
        .event-meta-row--line2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.28rem;
            width: 100%;
            box-sizing: border-box;
        }
        @media (max-width: 991.98px) {
            .event-meta-row--line2 {
                grid-template-columns: 1fr;
            }
        }

        /* Riga 3: Prezzo (colonna stretta) | Mappa (colonna stretta) | Iscrizioni | Iscritti */
        .event-meta-row--line3 {
            display: grid;
            grid-template-columns: minmax(0, 5.2rem) minmax(0, 6.4rem) minmax(0, 1fr) minmax(0, 1.05fr);
            gap: 0.28rem;
            width: 100%;
            box-sizing: border-box;
            align-items: stretch;
        }
        @media (max-width: 991.98px) {
            .event-meta-row--line3 {
                grid-template-columns: 1fr;
            }
        }

        .event-meta-price-box {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            border-radius: 0.375rem;
            padding: 0.18rem 0.45rem;
            font-size: 0.84rem;
            line-height: 1.1;
            border: none;
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .event-meta-price-box > i {
            flex-shrink: 0;
            color: #198754;
        }
        .event-meta-price-line {
            flex: 1;
            min-width: 0;
            word-break: break-word;
        }

        .event-meta-iscr-empty-box {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            width: 100%;
            box-sizing: border-box;
            border-radius: 0.375rem;
            padding: 0.38rem 0.65rem;
            font-size: 0.95rem;
            line-height: 1.2;
            border: none;
            background-color: #dee2e6;
            color: #212529;
        }
        .event-meta-iscr-empty-box > i {
            flex-shrink: 0;
            color: #495057;
        }
        .event-meta-iscr-line {
            flex: 1;
            min-width: 0;
            word-break: break-word;
        }

        .event-meta-row--line3 .event-registration-deadline-box {
            width: 100%;
            box-sizing: border-box;
            min-height: 0;
            display: flex;
            align-items: center;
        }

        .event-meta-map-slot {
            display: flex;
            align-items: stretch;
            min-height: 0;
        }
        .event-meta-map-slot .btn.btn-event-map-paired {
            justify-content: center;
        }
        .event-meta-map-unavailable {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            width: 100%;
            box-sizing: border-box;
            border-radius: 0.375rem;
            padding: 0.38rem 0.5rem;
            font-size: 0.85rem;
            line-height: 1.2;
            border: 2px dashed #adb5bd;
            background: #e9ecef;
            color: #6c757d;
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
            border: none;
        }

        /* Box iscritti: più compatto (riga 3) */
        .event-meta-row--line3 .event-meta-posti-box {
            padding: 0.18rem 0.45rem;
            font-size: 0.84rem;
            line-height: 1.1;
        }
        .event-meta-row--line3 .event-meta-posti-line {
            white-space: nowrap;
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
        .btn.event-btn-iscrivimi-all-evento.btn-iscrivimi-full:disabled {
            background-color: #dc3545 !important;
            color: #fff !important;
            border-color: #b02a37 !important;
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
        .event-meta-place-box > i {
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

        /* Città, nome locale, indirizzo: tre box separati (stesso stile del blocco luogo precedente) */
        .event-meta-place-box {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            width: 100%;
            box-sizing: border-box;
            border-radius: 0.375rem;
            padding: 0.38rem 0.65rem;
            font-size: 0.95rem;
            line-height: 1.2;
            border: none;
            background-color: #0d6efd;
            color: #fff;
        }
        .event-meta-place-line {
            flex: 1;
            min-width: 0;
            word-break: break-word;
        }
        .event-meta-place-box .fas {
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

        .event-meta-date-box--hint,
        .event-meta-posti-box--hint,
        .event-show-part-pill--hint {
            cursor: help;
        }

        .event-meta-ultimi-posti {
            font-weight: 800;
            font-size: 0.82rem;
            letter-spacing: 0.04em;
            color: #b00000;
            text-shadow: 0 0 2px #fff, 0 0 4px #fff;
            white-space: nowrap;
        }

        /* Quasi al completo: solo sfondo lampeggiante; Iscr./Lib./Tot. restano rosso/verde/arancio come prima */
        .event-meta-posti-box--part-gap {
            border: 2px solid rgba(114, 10, 10, 0.85) !important;
            font-weight: 600;
            animation: eventShowPostiGapBlink 1s ease-in-out infinite;
        }
        @keyframes eventShowPostiGapBlink {
            0%, 100% {
                background-color: #b02a37 !important;
                box-shadow: 0 0 0 rgba(220, 53, 69, 0);
            }
            50% {
                background-color: #dc3545 !important;
                box-shadow: 0 0 14px rgba(220, 53, 69, 0.95);
            }
        }

        .event-show-part-pill--gap,
        .event-show-part-pill--gap i,
        .event-show-part-pill--gap strong {
            color: #fff !important;
        }
        .event-show-part-pill--gap {
            font-weight: 800;
            border: 2px solid rgba(114, 10, 10, 0.85);
            animation: eventShowPartPillGapBlink 1s ease-in-out infinite;
        }
        @keyframes eventShowPartPillGapBlink {
            0%, 100% {
                background-color: #b02a37 !important;
                box-shadow: 0 0 0 rgba(220, 53, 69, 0);
            }
            50% {
                background-color: #dc3545 !important;
                box-shadow: 0 0 14px rgba(220, 53, 69, 0.95);
            }
        }

        .event-meta-posti-sep {
            color: #adb5bd;
        }

        .event-registration-deadline-box {
            /* Sfondo neutro: permette testo verde/rosso visibile */
            background-color: #dee2e6;
            color: #000;
            border: none;
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
            color: #000;
        }

        /* Evidenzia stato iscrizioni: verde se aperte, rosso se chiuse (scadenza passata) */
        .event-iscr-status--open {
            color: #198754;
            font-weight: 800;
        }
        .event-iscr-status--closed {
            color: #dc3545;
            font-weight: 900;
        }
        .event-iscr-status--soon {
            color: #198754;
            font-weight: 900;
            padding: 0.08rem 0.25rem;
            border-radius: 0.35rem;
            /* Lampeggio evidente (non “sparire”): alterna glow */
            animation: eventIscrBlink 0.85s ease-in-out infinite;
        }
        @keyframes eventIscrBlink {
            0%, 100% {
                opacity: 1;
                text-shadow: 0 0 0 rgba(25,135,84,0);
                background: transparent;
            }
            50% {
                opacity: 1;
                text-shadow:
                    0 0 2px rgba(25,135,84,0.55),
                    0 0 14px rgba(25,135,84,0.95);
                background: rgba(255, 193, 7, 0.55);
            }
        }

        .btn.btn-event-map-paired {
            background-color: #198754;
            color: #fff;
            border: 2px solid #157347;
            box-shadow: none;
            padding: 0.22rem 0.5rem;
            font-size: 0.88rem;
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
            color: #fff;
        }
        .btn.btn-event-map-paired:hover,
        .btn.btn-event-map-paired:focus {
            background-color: #157347;
            color: #fff;
            border-color: #146c43;
        }
        .btn.btn-event-map-paired:focus-visible {
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.45);
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

        .event-participation-btns--toglimi > form {
            flex: 0 0 auto;
            min-width: 0;
        }

        /* Box «Porti» accanto a Toglimi: stessa altezza (event-btn-meta-height), riempimento rosso */
        .event-porti-guest-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            box-sizing: border-box;
            background-color: #dc3545;
            color: #fff;
            border: 2px solid #a52834;
            border-radius: 0.375rem;
            white-space: nowrap;
        }

        /* Avviso: lista partecipanti nascosta */
        .event-participants-hidden-note {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            box-sizing: border-box;
            background: rgba(220, 53, 69, 0.12);
            color: #dc3545;
            border: 2px solid rgba(220, 53, 69, 0.55);
            border-radius: 0.375rem;
            white-space: nowrap;
            font-weight: 800;
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

        /* Immagini nei commenti: sempre responsive, mai sproporzionate */
        .comment-content img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 8px auto;
            object-fit: contain;
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
        /* Solo HTML prodotto dall’editor (CKEditor): interlinea e margini tra righe/paragrafi */
        .event-forum-box .comment-content {
            line-height: 1.22 !important;
        }
        .event-forum-box .comment-content p {
            margin-top: 0 !important;
            margin-bottom: 0.12em !important;
            line-height: inherit !important;
        }
        .event-forum-box .comment-content p:last-child {
            margin-bottom: 0 !important;
        }
        .event-forum-box .comment-content p:empty {
            display: none;
        }
        .event-forum-box .comment-content ul,
        .event-forum-box .comment-content ol {
            margin-top: 0.15em !important;
            margin-bottom: 0.2em !important;
        }
        .event-forum-box .comment-content li {
            margin-bottom: 0.06em !important;
            line-height: inherit !important;
        }
        .event-forum-box .comment-content h1,
        .event-forum-box .comment-content h2,
        .event-forum-box .comment-content h3,
        .event-forum-box .comment-content h4,
        .event-forum-box .comment-content h5,
        .event-forum-box .comment-content h6 {
            margin-top: 0.3em !important;
            margin-bottom: 0.15em !important;
            line-height: 1.22 !important;
        }
        .event-forum-box .comment-content pre {
            margin-top: 0.2em !important;
            margin-bottom: 0.2em !important;
            padding: 0.4rem 0.5rem !important;
            line-height: 1.25 !important;
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
            padding: 0.45rem 0.65rem;
            font-size: 0.88rem;
            line-height: 1.35;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-align: center;
            text-wrap: balance;
        }

        .event-waitlist-box {
            border: 2px dashed rgba(13, 110, 253, 0.65);
            background: rgba(255, 243, 205, 0.85);
        }
        .event-waitlist-explain {
            border: 2px solid rgba(13, 110, 253, 0.35);
            background: rgba(255, 255, 255, 0.85);
        }

        .event-waitlist-cta-blink {
            display: inline-block;
            font-weight: 800;
            animation: eventWaitlistCtaBlink 1.1s ease-in-out infinite;
        }
        @keyframes eventWaitlistCtaBlink {
            0%, 100% {
                color: inherit;
                text-shadow: none;
                transform: scale(1);
            }
            50% {
                color: #9b1c1c;
                text-shadow: 0 0 10px rgba(220, 53, 69, 0.65);
                transform: scale(1.02);
            }
        }
        @media (prefers-reduced-motion: reduce) {
            .event-waitlist-cta-blink {
                animation: none;
            }
        }

        .event-waitlist-box ul li {
            color: #0d6efd;
            font-weight: 700;
        }

        .event-waitlist-side {
            border: 2px solid rgba(13, 110, 253, 0.35) !important;
        }
        .event-waitlist-side__title {
            background: rgba(255, 243, 205, 0.85) !important;
            border-bottom: 1px solid rgba(13, 110, 253, 0.25) !important;
        }
        .event-waitlist-side__hint {
            color: #8B4513;
            font-weight: 700;
        }
        .event-waitlist-side .card-body ul li,
        .event-waitlist-side .card-body ul li a {
            color: #0d6efd !important;
            font-weight: 700;
        }

        .btn.event-wl-leave-btn {
            padding: 0.12rem 0.45rem;
            font-size: 0.78rem;
            line-height: 1.15;
            border-radius: 0.35rem;
            white-space: nowrap;
        }

        /* Gallery: non tagliare le immagini nei thumbnail */
        .event-gallery-thumb {
            width: 100%;
            height: 200px;
            object-fit: contain;
            background: #f8f9fa;
        }

        /* Altezza come box "Data evento" (.event-meta-stack: stessi padding/font/line-height) */
        .event-btn-meta-height {
            padding: 0.26rem 0.5rem !important;
            font-size: 0.9rem !important;
            line-height: 1.15 !important;
            min-height: 0 !important;
            box-sizing: border-box;
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

        .event-flash-error-sm {
            border: 2px solid #842029 !important;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.375rem;
            max-width: 50%;
            min-height: 2.125rem;
        }
        @media (max-width: 575.98px) {
            .event-flash-error-sm {
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
