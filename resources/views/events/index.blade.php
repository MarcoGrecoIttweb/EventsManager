@extends('layouts.app')

@section('title', 'Excursio - Community di amici a Milano')

@section('suppress_admin_pending_box', true)

@section('sidebar_after_online')
    @auth
        @if(auth()->user()->isAdmin())
            <div class="card card-sidebar mb-3 home-stats-card">
                <div class="card-header py-2">
                    <small class="fw-bold">
                        <i class="fas fa-chart-column me-1"></i> Statistiche
                    </small>
                </div>
                <div class="card-body p-2">
                    <div class="small">
                        <div class="mb-1">
                            <span class="fw-semibold">Ad oggi siamo</span>
                            <span class="home-stats-value">{{ number_format((int)($activeUsersCount ?? 0), 0, ',', '.') }}</span>
                        </div>
                        <a href="{{ route('admin.users.logins', ['days' => 1]) }}"
                           class="d-block mb-1 text-decoration-none home-stats-visits-link"
                           title="Apri elenco ingressi giornalieri">
                            <span class="fw-semibold text-body">Visite odierne</span>
                            <span class="home-stats-value">{{ number_format((int)($todayVisitsCount ?? 0), 0, ',', '.') }}</span>
                            <i class="fas fa-external-link-alt ms-1 small text-muted" aria-hidden="true"></i>
                        </a>
                        <div>
                            <span class="fw-semibold">Rapporto visite/iscritti</span>
                            <span class="home-stats-value">{{ number_format((float)($visitVsActivePct ?? 0), 2, ',', '.') }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endauth
@endsection

@section('sidebar_after_my_events')
    @auth
        <div class="card border-2 border-success shadow-sm mb-3">
            <div class="card-body p-2">
                <div class="small fw-bold mb-2" role="button" data-bs-toggle="collapse" data-bs-target="#suggestionsBox" aria-expanded="false" aria-controls="suggestionsBox" style="cursor:pointer;">
                    <i class="fas fa-lightbulb text-warning me-1"></i> Consigli e suggerimenti
                </div>
                <div class="collapse" id="suggestionsBox">
                    <div class="small mb-2">
                        <i class="fas fa-store me-1"></i> <i class="fas fa-lightbulb me-1"></i> Hai un locale da consigliarci o un'idea per migliorare il sito? <i class="fas fa-star text-success me-1"></i><span class="fw-semibold text-success">La tua opinione conta!</span>
                    </div>
                    <a href="mailto:excursio@libero.it?subject=Consigli%20e%20suggerimenti&body=Ciao%20team%20di%20Excursio,%0A%0AVorrei%20lasciarvi%20un%20feedback!%0A%0A%F0%9F%92%A1%20SUGGERIMENTI%20PER%20IL%20SITO:%0A%E2%9E%A1%EF%B8%8F%20%0A%0A%F0%9F%8D%B7%20NUOVO%20LOCALE%20DA%20CONSIGLIARE:%0A%E2%9E%A1%EF%B8%8F%20%0A%0A%E2%AD%90%20RECENSIONE%20/%20CRITICA%20LOCALE%20PROVATO:%0A%E2%9E%A1%EF%B8%8F%20%0A%0AGrazie!"
                       class="btn btn-warning btn-sm w-100">
                        <i class="fas fa-envelope me-1"></i> Invia consiglio
                    </a>
                </div>
            </div>
        </div>
    @endauth
@endsection

@php
    // Ordine e didascalie come nel vecchio sito (html.it / xfade); solo file presenti in public/slide
    $slideCatalog = [
        ['file' => 'foto1.jpg', 'alt' => 'Terra'],
        ['file' => 'foto2.jpg', 'alt' => 'Fuoco'],
        ['file' => 'foto3.jpg', 'alt' => 'Aria'],
        ['file' => 'foto4.jpg', 'alt' => 'Acqua'],
        ['file' => 'foto5.jpg', 'alt' => 'Cielo'],
        ['file' => 'foto7.jpg', 'alt' => 'Aria'],
        ['file' => 'foto8.jpg', 'alt' => 'Acqua'],
        ['file' => 'foto9.jpg', 'alt' => 'Cielo'],
        ['file' => 'foto10.jpg', 'alt' => 'Cielo'],
        ['file' => 'foto11.jpg', 'alt' => 'Cielo'],
        ['file' => 'foto13.jpg', 'alt' => 'Aria'],
        ['file' => 'foto14.jpg', 'alt' => 'Acqua'],
        ['file' => 'foto15.jpg', 'alt' => 'Cielo'],
        ['file' => 'foto16.jpg', 'alt' => 'Cielo'],
        ['file' => 'foto17.jpg', 'alt' => 'Aria'],
        ['file' => 'foto18.jpg', 'alt' => 'Acqua'],
        ['file' => 'foto19.jpg', 'alt' => 'Cielo'],
        ['file' => 'foto20.jpg', 'alt' => 'Cielo'],
    ];
    $slideDir = public_path('slide');
    $slideImages = [];
    foreach ($slideCatalog as $row) {
        if (is_file($slideDir . DIRECTORY_SEPARATOR . $row['file'])) {
            $slideImages[] = $row;
        }
    }
@endphp

@section('content')
    @auth
        @if(auth()->user()->isAdmin())
            <div class="mb-3">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#adminAnnouncementModal">
                    <i class="fas fa-bullhorn me-1"></i> Inserisci Messaggio
                </button>
            </div>

            <div class="modal fade" id="adminAnnouncementModal" tabindex="-1" aria-labelledby="adminAnnouncementModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('admin.site-settings.announcement') }}">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="adminAnnouncementModalLabel">Messaggio per gli utenti</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-2">
                                    <label for="announcementMessage" class="form-label">Testo del messaggio</label>
                                    <textarea id="announcementMessage" name="message" class="form-control" rows="5" maxlength="2000"
                                              placeholder="Scrivi qui il messaggio da mostrare agli utenti subito dopo il login...">{{ old('message', $adminAnnouncementMessage ?? '') }}</textarea>
                                </div>
                                <p class="small text-muted mb-0">
                                    Se scrivi un testo e premi Salva, verrà mostrato a ogni utente appena effettua il login.
                                    Per disattivarlo, svuota la casella e premi Salva.
                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                                <button type="submit" class="btn btn-primary">Salva</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($adminPendingRegistrationBanner) && is_array($adminPendingRegistrationBanner) && auth()->user()->isAdmin())
            <div class="alert alert-warning border border-warning shadow-sm mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3"
                 role="alert"
                 id="admin-home-pending-registrations-banner">
                <div class="flex-grow-1 min-w-0">
                    @if(($adminPendingRegistrationBanner['count'] ?? 0) === 1)
                        <strong>Nuova iscrizione.</strong>
                        Si è iscritto un nuovo utente con nickname
                        <strong>{{ $adminPendingRegistrationBanner['latest_username'] }}</strong>:
                        va <strong>abilitato</strong> da un amministratore prima di poter accedere al sito.
                    @else
                        <strong>Nuove iscrizioni.</strong>
                        Si sono iscritti <strong>{{ (int) $adminPendingRegistrationBanner['count'] }}</strong> nuovi utenti in attesa di abilitazione;
                        il più recente ha nickname <strong>{{ $adminPendingRegistrationBanner['latest_username'] }}</strong>.
                        Vanno <strong>abilitati</strong> da un amministratore prima che possano accedere.
                    @endif
                    <a href="{{ route('admin.users.index', ['registrations' => 'pending']) }}" class="alert-link fw-semibold ms-1">Apri le iscrizioni in attesa</a>
                </div>
                <form method="POST" action="{{ url('/admin/home-pending-registrations/dismiss') }}" class="d-flex align-items-center flex-shrink-0 m-0">
                    @csrf
                    <input type="hidden" name="user_ids" value="{{ $adminPendingRegistrationBanner['dismiss_user_ids'] }}">
                    <button type="submit" class="btn btn-sm btn-dark">Chiudi</button>
                </form>
            </div>
        @endif
    @endauth

    @if(!empty($loginAnnouncementMessage))
        <div class="modal fade" id="loginAnnouncementModal" tabindex="-1" aria-labelledby="loginAnnouncementModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="loginAnnouncementModalLabel"><i class="fas fa-bullhorn me-1"></i> Messaggio</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0" style="white-space: pre-line;">{{ $loginAnnouncementMessage }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Ho capito</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var el = document.getElementById('loginAnnouncementModal');
                if (el && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    new bootstrap.Modal(el).show();
                }
            });
        </script>
    @endif

    {{-- Hero --}}
    <div class="hero-section mb-4">
        <img src="{{ asset('upload_immagini/hero.jpg') }}" alt="Excursio" class="hero-img">
    </div>

    @if(count($slideImages) > 0)
        <div class="home-slideshow-row">
            @guest
                {{-- Visitatore non loggato, solo smartphone: "Utenti online" a sinistra
                     della galleria, "Non sei registrato..." a destra --}}
                <div class="home-slideshow-row__online d-md-none">
                    @include('partials.online-users-box')
                    @include('partials.guest-participate-box', ['idSuffix' => 'Gallery'])
                </div>
                <div class="home-slideshow-row__participate d-md-none">
                    @include('partials.recent-access-box')
                    @include('partials.gallery-placeholder-box')
                </div>
            @endguest
            <div class="home-slideshow-wrap mb-4 mx-auto" style="max-width:1200px;">
                <div class="home-slideshow"
                     id="homeSlideshow"
                     data-interval="5500"
                     role="img"
                     aria-label="Slideshow fotografico Excursio">
                    @foreach($slideImages as $idx => $row)
                        <img src="{{ asset('slide/' . $row['file']) }}"
                             alt="{{ $row['alt'] }}"
                             class="home-slideshow__img{{ $idx === 0 ? ' is-active' : '' }}"
                             @if($idx > 0) loading="lazy" @endif>
                    @endforeach
                </div>
                <p class="home-slideshow-caption text-center small text-muted mt-2 mb-0 d-none d-md-block">
                    <i class="fas fa-images"></i> Galleria fotografica
                </p>
            </div>
        </div>
    @endif

    <div class="container">
        <div class="text-center mb-4">
            <h2 class="mb-0 text-uppercase title-algerian">Eventi in programma</h2>
            @auth
                @if(auth()->user()->isAdmin())
                    <div class="mt-3 text-md-end">
                        <a href="{{ route('admin.events.create') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> Crea Evento
                        </a>
                    </div>
                @endif
            @endauth
        </div>

        {{-- Intro sotto il titolo: box centrato (ancora #descrizione-eventi dalla navbar) --}}
        @guest
            <div id="descrizione-eventi" class="intro-box-below-events mb-4" tabindex="-1">
                <p class="intro-text">
                    Excursio è una community di amici, che propone iniziative con l'obiettivo di offrire opportunità per conoscere persone e fare nuove amicizie, per evadere dalla solita routine quotidiana. Le iniziative proposte si svolgono a Milano e sono di costi modesti, alla portata di tutti, e in alcune occasioni anche gratuite, senza alcun fine di lucro. <strong>La registrazione è gratuita.</strong>
                </p>
                <p class="intro-text mb-0">
                    Dopo esserti registrato dovrai loggarti per poter visualizzare gli eventi proposti e potrai aggregarti senza alcun obbligo — la tua adesione è libera.
                </p>
                <div class="intro-actions">
                    <a href="{{ route('register') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Registrati
                    </a>
                    <a href="mailto:info@excursio.org" class="btn btn-outline-secondary">
                        <i class="fas fa-envelope"></i> Scrivici
                    </a>
                </div>
            </div>
        @endguest

        @if($events->count() > 0)
            {{-- PC: 2 card per riga, stessa altezza; immagine a sinistra, contenuto a destra --}}
            <div class="row g-4 align-items-stretch">
                @foreach($events as $event)
                    <div class="col-12 col-lg-6 d-flex">
                        <div id="event-card-{{ $event->getKey() }}"
                             class="card h-100 w-100 event-box {{ $event->isFull() ? 'event-box--full' : '' }} {{ session('waitlist_flash_event_id') == $event->getKey() ? 'event-box--flash' : '' }}">
                            @if(!$event->is_active && auth()->check() && auth()->user()->isAdmin())
                                <div class="card-header bg-secondary text-white text-center py-1 small" data-hint="Visibile in questo elenco solo a te come amministratore: gli altri utenti non lo vedono">
                                    <i class="fas fa-eye-slash"></i> Non pubblicato — visibile solo a te
                                </div>
                            @endif
                            @if($event->isFull())
                                <div class="card-header event-full-banner text-white text-center py-2">
                                    <span class="event-full-banner__blink">
                                        <i class="fas fa-ban"></i> COMPLETO &mdash; {{ $event->participants_count }}@if($event->max_participants)/{{ (int)$event->max_participants }}@endif Adesioni &mdash; <i class="fas fa-clipboard-list"></i> Aperta Lista Riserva
                                    </span>
                                </div>
                            @endif

                            {{-- Smartphone: titolo mostrato prima dell'immagine (su PC resta dentro alla card, invariato) --}}
                            <h5 class="card-title d-block d-md-none px-3 pt-3 mb-0 {{ $event->isFull() ? 'text-muted' : '' }}">{{ $event->title }}</h5>

                            <div class="row g-0 h-100 event-card-row">
                                <div class="col-md-4">
                                    @if($event->cover_image_url)
                                        <div class="event-thumb-box position-relative bg-light h-100">
                                            <img src="{{ $event->cover_image_url }}"
                                                 alt="{{ $event->title }}"
                                                 class="event-thumb-box__img">
                                            @if($event->isFull())
                                                <div class="position-absolute top-0 start-0 m-2">
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-lock"></i> Completo
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="event-thumb-box bg-light d-flex align-items-center justify-content-center h-100">
                                            <div class="text-center text-muted">
                                                <i class="fas fa-calendar-alt fa-3x mb-2"></i>
                                                <p class="mb-0 small">Nessuna immagine</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-8 d-flex flex-column h-100 event-card-content">
                                    <div class="card-body">
                                        <h5 class="card-title d-none d-md-block {{ $event->isFull() ? 'text-muted' : '' }}">{{ $event->title }}</h5>
                                        @php
                                            $rawMaxPart = $event->max_participants;
                                            $maxPosti = ($rawMaxPart !== null && $rawMaxPart !== '') ? (int) $rawMaxPart : null;
                                            $cntPart = (int) $event->participants_count;
                                            $postiLiberiEv = ($maxPosti !== null && !$event->isFull()) ? max(0, $maxPosti - $cntPart) : null;
                                            /* Lampeggio solo quando restano 1 o 2 posti al completamento */
                                            $mancanoPerCompletareMax = $postiLiberiEv !== null && $postiLiberiEv >= 1 && $postiLiberiEv <= 2;
                                            $weekdayAbbrIt = [0 => 'dom', 1 => 'lun', 2 => 'mar', 3 => 'merc', 4 => 'gio', 5 => 'ven', 6 => 'sab'];
                                            $eventDateShort = $event->date
                                                ? $weekdayAbbrIt[(int) $event->date->format('w')] . ' ' . $event->date->format('j') . ' ' . $event->date->locale('it')->translatedFormat('F') . ' h' . $event->date->format('H:i')
                                                : '';
                                        @endphp
                                        <div class="mb-3 event-meta-badges">
                                            <span class="badge bg-primary event-meta-badges__badge event-meta-badges__badge--hint"
                                                  title="Indica data e ora di inizio dell’evento.">
                                                <i class="fas fa-calendar"></i>
                                                {{ $eventDateShort }}
                                            </span>
                                            <span class="badge event-meta-badges__badge event-meta-badges__badge--hint {{ $event->isFull() ? 'bg-danger' : 'bg-secondary' }} {{ $mancanoPerCompletareMax ? 'event-meta-badges__badge--part-gap' : '' }}"
                                                  title="{{ $mancanoPerCompletareMax
                                                      ? 'Restano 1 o 2 posti liberi rispetto al massimo: il box lampeggia per segnalare che l’evento è quasi al completo.'
                                                      : ($maxPosti !== null
                                                          ? 'Mostra quanti partecipanti ci sono (iscritti più eventuali ospiti) rispetto al numero massimo di posti previsto dall’organizzatore.'
                                                          : 'Mostra il numero di partecipanti (iscritti più eventuali ospiti); l’organizzatore non ha indicato un numero massimo di posti.') }}">
                                                <i class="fas fa-users" aria-hidden="true"></i>
                                                <strong>{{ $event->participants_count }}</strong>@if($maxPosti !== null)<span class="text-white-50 fw-normal"> / </span><strong>{{ $maxPosti }}</strong>@endif
                                            </span>
                                            @if($event->deadline || !$event->isRegistrationOpen())
                                                @php
                                                    $iscrOpen = $event->isRegistrationOpen();
                                                    $deadline = $event->deadline;
                                                    $secondsToDeadline = $deadline ? $deadline->diffInSeconds(now(), false) * -1 : null; // futuro = positivo
                                                    $iscrSoon = $iscrOpen && is_int($secondsToDeadline) && $secondsToDeadline > 0 && $secondsToDeadline <= 86400; // 24h
                                                    $iscrClass = $iscrOpen
                                                        ? ($iscrSoon ? 'event-card-iscr--soon' : 'event-card-iscr--open')
                                                        : 'event-card-iscr--closed';
                                                @endphp
                                                <span class="badge event-meta-badges__badge event-card-iscr-badge {{ $iscrClass }}">
                                                    <i class="fas fa-clock"></i>
                                                    @if($iscrOpen)
                                                        Iscriviti entro {{ $weekdayAbbrIt[(int) $event->deadline->format('w')] }} {{ $event->deadline->format('d/m') }} h{{ $event->deadline->format('H:i') }}
                                                    @else
                                                        Adesioni chiuse
                                                    @endif
                                                </span>
                                            @endif
                                            @auth
                                                <button type="button" class="btn btn-guest-details btn-sm event-meta-badges__badge" data-bs-toggle="modal" data-bs-target="#homeParticipantsModal{{ $event->getKey() }}">
                                                    <i class="fas fa-users"></i> Lista iscritti
                                                </button>
                                            @else
                                                <a href="{{ route('login') }}" class="btn btn-guest-details btn-sm event-meta-badges__badge" data-hint="Accedi per vedere la lista degli iscritti">
                                                    <i class="fas fa-lock"></i>
                                                </a>
                                            @endauth
                                        </div>

                                        @include('partials.event-public-preview', ['event' => $event, 'charLimit' => 100])

                                        @if($event->isFull() && (!auth()->check() || !auth()->user()->isApproved()))
                                            <div class="alert alert-warning alert-sm mb-0 py-2 mt-2">
                                                <small>
                                                    <i class="fas fa-info-circle"></i>
                                                    <strong>Evento al completo</strong> - Non è più possibile iscriversi
                                                </small>
                                            </div>
                                        @endif

                                        @auth
                                            @if(auth()->user()->isApproved())
                                                @php
                                                    $cannotJoin = $event->isFull() || !$event->isRegistrationOpen();
                                                    $isWaitlistedHere = isset($waitlistedEventIds) && in_array($event->getKey(), $waitlistedEventIds, true);
                                                    $waitName = auth()->user()->nickname ?? trim((auth()->user()->nome ?? '') . ' ' . (auth()->user()->cognome ?? '')) ?: 'Utente';
                                                @endphp
                                                @if($cannotJoin)
                                                    <div class="mt-2 p-2 rounded event-waitlist-box">
                                                        @if(session('waitlist_flash_event_id') == $event->getKey())
                                                            @if(session('success'))
                                                                <div class="alert alert-success alert-sm mb-2 py-2">
                                                                    <i class="fas fa-check-circle me-1"></i>
                                                                    <strong>Sei stato inserito in questa lista d’attesa.</strong>
                                                                    <span class="d-block">
                                                                        Evento:
                                                                        <a href="{{ route('events.show', $event) }}" class="text-decoration-underline">
                                                                            {{ $event->title }}
                                                                        </a>
                                                                    </span>
                                                                    <span class="d-block">{{ session('success') }}</span>
                                                                </div>
                                                            @elseif(session('error'))
                                                                <div class="alert alert-danger alert-sm mb-2 py-2">
                                                                    <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
                                                                </div>
                                                            @endif
                                                        @endif
                                                        @php
                                                            $waitlistEntriesHere = isset($waitlistByEventId) ? ($waitlistByEventId[$event->getKey()] ?? []) : [];
                                                        @endphp
                                                        @if($isWaitlistedHere)
                                                            <div class="small fw-semibold mb-2">
                                                                <i class="fas fa-hourglass-half"></i>
                                                                {{ $waitName }} è in attesa qualora si liberassero posti.
                                                            </div>
                                                            <form action="{{ route('events.waitlist.leave', $event) }}" method="POST" class="mb-0">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-outline-secondary btn-sm w-100"
                                                                        onclick="return confirm('Vuoi uscire dalla lista d’attesa?');">
                                                                    <i class="fas fa-user-slash"></i> Esci dalla lista d’attesa
                                                                </button>
                                                            </form>
                                                        @else
                                                            @php
                                                                $wlBoxId = 'waitlistBoxEvent' . $event->getKey();
                                                            @endphp
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
                                                                            <i class="fas fa-info-circle"></i> Come funziona
                                                                        </div>
                                                                        <div class="mb-2">
                                                                            Ti inseriamo in lista d’attesa per questo evento. Se qualcuno si toglie e si libera un posto,
                                                                            la prima persona in lista riceve una mail e può iscriversi dall’evento.
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

                                                        @if(is_iterable($waitlistEntriesHere) && count($waitlistEntriesHere) > 0)
                                                            <div class="mt-2 small">
                                                                <div class="fw-semibold mb-1">
                                                                    <i class="fas fa-list"></i> Persone in lista d’attesa
                                                                </div>
                                                                <ul class="mb-0 ps-3">
                                                                    @foreach($waitlistEntriesHere as $wl)
                                                                        @php
                                                                            $wlName =
                                                                                $wl->user?->nickname
                                                                                ?? trim((string) (($wl->user?->nome ?? '') . ' ' . ($wl->user?->cognome ?? '')))
                                                                                ?: ($wl->display_name ?? 'Utente');
                                                                        @endphp
                                                                        <li>
                                                                            @if($wl->user)
                                                                                <a href="{{ route('profile.show', $wl->user) }}" class="text-decoration-none">
                                                                                    <i class="fas fa-user"></i> {{ $wlName }}
                                                                                </a>
                                                                            @else
                                                                                <i class="fas fa-user"></i> {{ $wlName }}
                                                                            @endif
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endif
                                        @endauth
                                    </div>

                                    @auth
                                        <div class="card-footer bg-transparent mt-auto">
                                            @include('partials.event-details-button', ['event' => $event])
                                        </div>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modale fuori dalla card: un genitore con "transform" (hover della card)
                         romperebbe il posizionamento "fixed" del modale Bootstrap.
                         Visibile solo agli utenti registrati (vedi pulsante che lo apre). --}}
                    @auth
                    <div class="modal fade" id="homeParticipantsModal{{ $event->getKey() }}" tabindex="-1" aria-labelledby="homeParticipantsModalLabel{{ $event->getKey() }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="homeParticipantsModalLabel{{ $event->getKey() }}">
                                        <i class="fas fa-users"></i> Iscritti all'evento
                                        @php
                                            $homeRealCount = $event->real_participants_count;
                                            $homeGuestsCount = $event->participants_count - $homeRealCount;
                                        @endphp
                                        <span class="badge bg-secondary ms-1">
                                            {{ $event->participants_count }} totali
                                            ({{ $homeRealCount }} iscritti @if($homeGuestsCount > 0)+ {{ $homeGuestsCount }} {{ $homeGuestsCount === 1 ? 'ospite' : 'ospiti' }}@endif)
                                        </span>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                </div>
                                <div class="modal-body">
                                    @if($event->participants->count() > 0)
                                        <ul class="list-group list-group-flush">
                                            @foreach($event->participants as $participant)
                                                @php
                                                    $homeHasGuests = $participant->pivot->amici > 0;
                                                    $homeOspitiEntries = \App\Support\OspitiGuestStore::decode($participant->pivot->ospiti_inseriti_il ?? null);
                                                @endphp
                                                <li class="list-group-item">
                                                    <a href="{{ route('profile.show', $participant) }}" class="d-flex align-items-center gap-2 text-decoration-none">
                                                        @if($participant->photo_url)
                                                            <img src="{{ $participant->photo_url }}"
                                                                 alt="{{ $participant->nickname }}"
                                                                 class="rounded-circle"
                                                                 style="width:32px;height:32px;object-fit:cover;flex-shrink:0;">
                                                        @else
                                                            <i class="fas fa-user"></i>
                                                        @endif
                                                        <span class="fw-semibold">{{ $participant->nickname }}</span>
                                                    </a>
                                                    @if($homeHasGuests)
                                                        <ul class="list-unstyled small mt-1 mb-0 ps-4">
                                                            @for($hgi = 0; $hgi < (int) $participant->pivot->amici; $hgi++)
                                                                @php $homeGNome = $homeOspitiEntries[$hgi]['nome'] ?? ''; @endphp
                                                                <li>
                                                                    <i class="fas fa-user-friends text-success"></i>
                                                                    <span class="text-primary">{{ $homeGNome !== '' ? $homeGNome : 'Ospite' }}</span>
                                                                </li>
                                                            @endfor
                                                        </ul>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-muted mb-0">Nessun iscritto al momento.</p>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <a href="{{ route('events.show', $event) }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-external-link-alt"></i> Apri pagina evento
                                    </a>
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Chiudi</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endauth
                @endforeach
            </div>

            {{-- Pagination - SOLO SE ESISTE --}}
            @if(method_exists($events, 'links'))
                <div class="d-flex justify-content-center mt-4">
                    {{ $events->links() }}
                </div>
            @endif
        @else
            <div class="intro-box-below-events intro-box-below-events--empty py-5 text-center">
                <img src="{{ asset('images/nessun-evento-programmato.webp') }}"
                     alt="Nessun evento in programma"
                     class="img-fluid rounded shadow-sm mb-3"
                     style="max-width: 400px; width: 100%;">
                <h3 class="h4">Nessun evento in programma</h3>
                <p class="text-muted mb-0">Non ci sono eventi in programma al momento.</p>
                @auth
                    @if(auth()->user()->isAdmin())
                        <div class="intro-actions mt-3">
                            <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Crea il primo evento
                            </a>
                        </div>
                    @endif
                @endauth
            </div>
        @endif
    </div>{{-- /container --}}

    <style>
        .title-algerian {
            font-family: Algerian, "Algerian", serif;
            letter-spacing: 0.5px;
        }
        .home-stats-card {
            border: 2px solid rgba(13, 110, 253, 0.35);
            border-radius: 8px;
        }
        .home-stats-card .card-header {
            background: rgba(13, 110, 253, 0.08);
            border-bottom: 1px solid rgba(13, 110, 253, 0.25);
        }
        .home-stats-value {
            font-weight: 800;
            color: #0d6efd;
        }
        .home-stats-visits-link {
            border-radius: 4px;
            padding: 0.15rem 0.25rem;
            margin-left: -0.25rem;
            margin-right: -0.25rem;
        }
        .home-stats-visits-link:hover {
            background: rgba(13, 110, 253, 0.1);
        }
        .home-stats-visits-link:hover .home-stats-value {
            text-decoration: underline;
        }
        /* Immagine hero.jpg nascosta (il blocco resta nel DOM per eventuali riattivazioni) */
        .hero-section {
            display: none !important;
        }

        /* Slideshow dissolvenza sotto hero */
        .home-slideshow-wrap {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }
        .home-slideshow {
            position: relative;
            width: 100%;
            min-height: 200px;
            border-radius: 6px;
            overflow: hidden;
            border: 3px solid #f5c400;
            box-shadow: 0 0 0 2px #000;
            /* Sfondo dietro letterboxing (immagine intera con contain) */
            background: #1a1a1a;
        }
        .home-slideshow__img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            opacity: 0;
            transition: opacity 1.35s ease-in-out;
            pointer-events: none;
        }
        .home-slideshow__img.is-active {
            opacity: 1;
            z-index: 1;
        }
        @media (max-width: 767.98px) {
            /* Immagine principale ridotta mantenendo le proporzioni (bordo compreso):
               si riduce la larghezza, l'altezza si adatta di conseguenza via JS. */
            .home-slideshow-wrap {
                width: 50%;
                margin-left: auto;
                margin-right: auto;
            }
            /* Visitatore non loggato: "Utenti online" nel margine libero a sinistra
               della galleria, "Non sei registrato..." in quello a destra. La galleria
               resta sempre esattamente centrata (stessa larghezza/margini di prima),
               i due box non la spostano. */
            .home-slideshow-row {
                position: relative;
                /* I box laterali sono "position: absolute" e non contano per l'altezza
                   della riga: senza un minimo esplicito, se sono piu' alti della
                   galleria (es. con piu' box impilati) finiscono per coprire il
                   contenuto sottostante (titolo "Eventi in programma"). */
                min-height: 210px;
            }
            .home-slideshow-row__online,
            .home-slideshow-row__participate {
                position: absolute;
                top: 0;
                width: 20%;
                z-index: 2;
            }
            .home-slideshow-row__online {
                left: 0;
            }
            .home-slideshow-row__participate {
                right: 0;
            }
            .home-slideshow-row__online .card,
            .home-slideshow-row__participate .card {
                margin-bottom: 0.35rem !important;
                font-size: 0.7rem;
                border: 2px solid #198754 !important;
            }
            .home-slideshow-row__online .card:last-child,
            .home-slideshow-row__participate .card:last-child {
                margin-bottom: 0 !important;
            }
            .home-slideshow-row__online .card-body,
            .home-slideshow-row__participate .card-body {
                height: 60px !important;
                padding: 0.35rem !important;
            }
            /* Titoli e nickname troppo grandi per il riquadro stretto: dimensioni
               ridotte, forzate con !important perche' altrove sono definite regole
               globali con valori assoluti piu' grandi per il contesto sidebar. */
            .home-slideshow-row__online .card-header small,
            .home-slideshow-row__participate .card-header small {
                font-size: 0.65rem !important;
            }
            .home-slideshow-row__online .online-user-row .small,
            .home-slideshow-row__participate .online-user-row .small {
                font-size: 0.62rem !important;
            }
            .home-slideshow-row__online .online-user-row .small a,
            .home-slideshow-row__participate .online-user-row .small a {
                display: block;
                max-width: 100%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.2), rgba(0,0,0,0.6));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #fff;
        }
        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            text-shadow: 2px 2px 6px rgba(0,0,0,0.7);
            margin-bottom: 0.25rem;
        }
        .hero-subtitle {
            font-size: 1.2rem;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.7);
        }
        .intro-box-below-events {
            background: #f8f9fa;
            max-width: 48rem;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            padding: 1.5rem 1.75rem;
        }
        #descrizione-eventi {
            display: none;
            scroll-margin-top: 5.5rem;
        }
        .intro-box-below-events--empty {
            max-width: 36rem;
        }
        .intro-box-below-events .intro-text {
            text-align: center;
        }
        .intro-box-below-events .intro-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 0.5rem 0.75rem;
        }
        .intro-box-below-events .intro-actions .btn {
            margin-left: 0 !important;
        }
        .intro-text {
            font-size: 1rem;
            color: #333;
            margin-bottom: 0.75rem;
            line-height: 1.7;
        }
        .intro-actions {
            margin-top: 1rem;
        }
        /* Anteprima descrizione: nero come in dettaglio evento */
        .event-box .event-public-desc-preview {
            color: #000;
        }
        /* Anteprima: max 3 righe + puntini (altezza contenuta, card uniformi) */
        .event-box .event-preview {
            line-height: 1.4;
            font-size: 0.875rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            word-break: break-word;
            max-height: calc(1.4em * 3);
        }

        /* Card eventi (home): box immagine che riempie senza spazi */
        .event-thumb-box {
            min-height: 220px;
            overflow: hidden;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .event-thumb-box__img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            display: block;
        }
        @media (max-width: 767.98px) {
            /* Altezza fissa (non solo minima): tutte le foto evento hanno lo stesso
               riquadro larghezza/altezza; con object-fit:contain la foto intera resta
               visibile, con eventuale bordo/sfondo se le proporzioni non combaciano. */
            .event-thumb-box.h-100 {
                height: 200px !important;
                min-height: 200px;
            }
            /* Fix mobile: evita che l'h-100 nasconda il footer (pulsante guest) */
            .event-card-row,
            .event-card-content {
                height: auto !important;
            }
            .btn-mobile-home {
                border-width: 2px;
                font-weight: 700;
            }
        }

        /* Griglia 2 colonne: data evento sopra "Iscriviti entro" (stessa larghezza,
           allineati), partecipanti sopra "Lista iscritti" (stessa larghezza,
           allineati) accanto ai primi due, sia su PC sia su smartphone. */
        .event-meta-badges {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
        }
        .event-meta-badges .badge,
        .event-meta-badges .btn {
            width: 100%;
        }
        .event-meta-badges__badge {
            font-size: 0.85rem;
            font-weight: 700;
            padding: 0.25rem 0.6rem !important;
            border-radius: 0.6rem;
            height: 1.8rem !important;
            box-sizing: border-box;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            line-height: 1.1;
        }
        .event-meta-badges__badge i {
            margin-right: 0.35rem;
        }
        .event-meta-badges__badge--hint {
            cursor: help;
        }
        /* Quasi al completo: lampeggio rosso solo se restano 1–2 posti liberi */
        .event-meta-badges__badge--part-gap,
        .event-meta-badges__badge--part-gap i,
        .event-meta-badges__badge--part-gap strong {
            color: #fff !important;
        }
        .event-meta-badges__badge--part-gap {
            font-weight: 800;
            border: 2px solid rgba(114, 10, 10, 0.85);
            animation: eventPartGapBlink 1s ease-in-out infinite;
        }
        @keyframes eventPartGapBlink {
            0%, 100% {
                background-color: #b02a37 !important;
                box-shadow: 0 0 0 rgba(220, 53, 69, 0);
            }
            50% {
                background-color: #dc3545 !important;
                box-shadow: 0 0 14px rgba(220, 53, 69, 0.95);
            }
        }

        /* Iscrizioni: verde/rosso + lampeggio nelle 24h (lista eventi) */
        .event-card-iscr-badge {
            background: rgba(255, 255, 255, 0.92);
            border: 2px solid currentColor;
        }
        .event-card-iscr--open {
            color: #198754;
            font-weight: 800;
        }
        .event-card-iscr--closed {
            color: #dc3545;
            font-weight: 900;
        }
        .event-card-iscr--soon {
            color: #198754;
            font-weight: 900;
            animation: eventCardIscrBlink 0.85s ease-in-out infinite;
        }
        @keyframes eventCardIscrBlink {
            0%, 100% {
                box-shadow: 0 0 0 rgba(25,135,84,0);
                transform: scale(1);
                background: rgba(255, 255, 255, 0.92);
            }
            50% {
                box-shadow:
                    0 0 0 3px rgba(25,135,84,0.35),
                    0 0 22px rgba(25,135,84,0.9);
                transform: scale(1.03);
                /* Giallo attenzione (più evidente del verde) */
                background: rgba(255, 193, 7, 0.55);
            }
        }

        .alert-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }

        .event-waitlist-box {
            border: 2px dashed rgba(13, 110, 253, 0.65);
            background: rgba(255, 243, 205, 0.85);
        }
        .event-waitlist-explain {
            border: 2px solid rgba(13, 110, 253, 0.35);
            background: rgba(255, 255, 255, 0.85);
        }

        /* Testo CTA lista riserva: lampeggio attenzione */
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

        .event-box.event-box--flash {
            box-shadow: 0 0 0 4px rgba(25, 135, 84, 0.35), 0 8px 22px rgba(0, 0, 0, 0.12);
            border: 2px solid rgba(25, 135, 84, 0.65);
            animation: waitlistFlashPulse 1.2s ease-in-out 0s 2;
        }
        @keyframes waitlistFlashPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.01); }
            100% { transform: scale(1); }
        }

        .card-img-top {
            border-bottom: 1px solid rgba(0,0,0,0.125);
        }

        /* Banner COMPLETO lampeggiante in cima alla card */
        .event-full-banner {
            background: linear-gradient(135deg, #b02a37, #dc3545);
            border-bottom: 3px solid #720a0a;
            padding: 0.6rem 0.75rem !important;
        }
        .event-full-banner__blink {
            display: inline-block;
            font-size: 1.05rem;
            font-weight: 900;
            letter-spacing: 1px;
            text-transform: uppercase;
            animation: eventFullBlink 0.9s ease-in-out infinite;
            white-space: nowrap;
        }
        @keyframes eventFullBlink {
            0%, 100% {
                opacity: 1;
                text-shadow: 0 0 6px rgba(255,255,255,0.3);
            }
            50% {
                opacity: 0.25;
                text-shadow: 0 0 20px rgba(255,255,255,0.9);
            }
        }
        @media (prefers-reduced-motion: reduce) {
            .event-full-banner__blink { animation: none; }
        }

        /* Migliora l'aspetto dei badge sulla thumbnail */
        .position-absolute .badge {
            font-size: 0.7rem;
            backdrop-filter: blur(10px);
            background-color: rgba(220, 53, 69, 0.9) !important;
        }
    </style>
@endsection

@push('scripts')
    @if(isset($slideImages) && count($slideImages) > 0)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var root = document.getElementById('homeSlideshow');
                if (!root) return;
                var imgs = root.querySelectorAll('.home-slideshow__img');
                if (!imgs.length) return;

                function updateSlideshowHeight() {
                    var w = root.clientWidth;
                    if (w <= 0) return;
                    var maxH = 0;
                    for (var i = 0; i < imgs.length; i++) {
                        var im = imgs[i];
                        if (im.naturalWidth > 0 && im.naturalHeight > 0) {
                            var h = (im.naturalHeight / im.naturalWidth) * w;
                            if (h > maxH) maxH = h;
                        }
                    }
                    if (maxH > 0) {
                        root.style.height = Math.ceil(maxH) + 'px';
                    }
                }

                var resizeTimer;
                window.addEventListener('resize', function () {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(updateSlideshowHeight, 120);
                });

                var loaded = 0;
                function onImgReady() {
                    loaded++;
                    updateSlideshowHeight();
                    if (loaded === imgs.length && imgs.length > 1) {
                        var ms = parseInt(root.getAttribute('data-interval'), 10) || 5500;
                        var idx = 0;
                        setInterval(function () {
                            imgs[idx].classList.remove('is-active');
                            idx = (idx + 1) % imgs.length;
                            imgs[idx].classList.add('is-active');
                        }, ms);
                    }
                }

                for (var j = 0; j < imgs.length; j++) {
                    var img = imgs[j];
                    if (img.complete && img.naturalWidth > 0) {
                        onImgReady();
                    } else {
                        img.addEventListener('load', onImgReady, { once: true });
                        img.addEventListener('error', onImgReady, { once: true });
                    }
                }
            });
        </script>
    @endif
    @if(session('waitlist_flash_event_id'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var id = 'event-card-{{ (int) session('waitlist_flash_event_id') }}';
                var el = document.getElementById(id);
                if (!el) return;
                setTimeout(function () {
                    try {
                        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    } catch (e) {
                        el.scrollIntoView(true);
                    }
                }, 150);
            });
        </script>
    @endif
@endpush
