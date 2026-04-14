@extends('layouts.app')

@section('title', 'Excursio - Community di amici a Milano')

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
    {{-- Hero --}}
    <div class="hero-section mb-4">
        <img src="{{ asset('upload_immagini/hero.jpg') }}" alt="Excursio" class="hero-img">
    </div>

    @if(count($slideImages) > 0)
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
            <p class="home-slideshow-caption text-center small text-muted mt-2 mb-0">
                <i class="fas fa-images"></i> Galleria fotografica
            </p>
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
                        <div class="card h-100 w-100 event-box {{ $event->isFull() ? 'event-box--full' : '' }}">
                            @if($event->isFull())
                                <div class="card-header bg-danger text-white text-center py-2">
                                    <small><i class="fas fa-exclamation-triangle"></i> <strong>EVENTO AL COMPLETO</strong></small>
                                </div>
                            @endif

                            <div class="row g-0 h-100">
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
                                <div class="col-md-8 d-flex flex-column h-100">
                                    <div class="card-body">
                                        <h5 class="card-title {{ $event->isFull() ? 'text-muted' : '' }}">{{ $event->title }}</h5>
                                        <div class="mb-3 d-flex flex-wrap gap-2 event-meta-badges">
                                            <span class="badge bg-primary event-meta-badges__badge">
                                                <i class="fas fa-calendar"></i>
                                                {{ $event->italian_event_date ?? ($event->date ? $event->date->format('d/m/Y H:i') : '') }}
                                            </span>
                                            <span class="badge bg-{{ $event->isFull() ? 'danger' : 'secondary' }} event-meta-badges__badge">
                                                <i class="fas fa-users"></i>
                                                {{ $event->participants_count }}
                                                @if($event->max_participants)
                                                    / {{ $event->max_participants }}
                                                @endif
                                            </span>
                                        </div>
                                        <p class="card-text mb-2">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <strong>{{ $event->city }}</strong>
                                            <span class="text-muted small ms-2">
                                                <strong>Org.</strong>
                                                {{ $event->user->nickname ?? $event->user->nome ?? '—' }}
                                            </span>
                                        </p>
                                        <div class="card-text text-muted small event-preview">
                                            {{ $event->getHomepagePreview(100) }}
                                        </div>

                                        @if($event->isFull())
                                            <div class="alert alert-warning alert-sm mb-0 py-2 mt-2">
                                                <small>
                                                    <i class="fas fa-info-circle"></i>
                                                    <strong>Evento al completo</strong> - Non è più possibile iscriversi
                                                </small>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="card-footer bg-transparent mt-auto">
                                        @auth
                                            <a href="{{ route('events.show', $event) }}" class="btn btn-{{ $event->isFull() ? 'outline-secondary' : 'primary' }} w-100">
                                                <i class="fas fa-eye"></i>
                                                {{ $event->isFull() ? 'Visualizza (Completo)' : 'Dettagli Evento' }}
                                            </a>
                                        @else
                                            <a href="{{ route('login') }}" class="btn btn-guest-details w-100">
                                                <i class="fas fa-lock"></i> Accedi per vedere i dettagli
                                            </a>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination - SOLO SE ESISTE --}}
            @if(method_exists($events, 'links'))
                <div class="d-flex justify-content-center mt-4">
                    {{ $events->links() }}
                </div>
            @endif
        @else
            <div class="intro-box-below-events intro-box-below-events--empty py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3 d-block"></i>
                <h3 class="h4">Nessun evento in programma</h3>
                <p class="text-muted mb-0">Non ci sono eventi in programma al momento.</p>
                @auth
                    @if(auth()->user()->isAdmin())
                        <div class="intro-actions">
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
            .home-slideshow-wrap {
                width: 100%;
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
        /* Visibile solo con URL #descrizione-eventi (click su «Chi siamo e cosa facciamo») */
        #descrizione-eventi {
            display: none;
            scroll-margin-top: 5.5rem;
        }
        #descrizione-eventi:target {
            display: block;
            outline: 3px solid #0d6efd;
            outline-offset: 3px;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.25), 0 1px 3px rgba(0, 0, 0, 0.06);
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
        }
        .event-thumb-box__img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        @media (max-width: 767.98px) {
            .event-thumb-box {
                min-height: 200px;
            }
        }

        .event-meta-badges__badge {
            font-size: 0.95rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.6rem;
        }
        .event-meta-badges__badge i {
            margin-right: 0.35rem;
        }

        .alert-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }

        .card-img-top {
            border-bottom: 1px solid rgba(0,0,0,0.125);
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
@endpush
