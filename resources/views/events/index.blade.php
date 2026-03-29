@extends('layouts.app')

@section('title', 'Excursio - Community di amici a Milano')

@section('content')
    {{-- Hero --}}
    <div class="hero-section mb-4">
        <img src="{{ asset('upload_immagini/hero.jpg') }}" alt="Excursio" class="hero-img">
    </div>

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
            <div class="row">
                @foreach($events as $event)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card event-box h-100 {{ $event->isFull() ? 'event-box--full' : '' }}">
                            @if($event->isFull())
                                <div class="card-header bg-danger text-white text-center py-2">
                                    <small><i class="fas fa-exclamation-triangle"></i> <strong>EVENTO AL COMPLETO</strong></small>
                                </div>
                            @endif

                            {{-- Thumbnail Image --}}
                            @if($event->cover_image_url)
                                <div class="position-relative">
                                    <img src="{{ $event->cover_image_url }}"
                                         alt="{{ $event->title }}"
                                         class="card-img-top"
                                         style="height: 200px; object-fit: cover; width: 100%;">
                                    @if($event->isFull())
                                        <div class="position-absolute top-0 start-0 m-2">
                                            <span class="badge bg-danger">
                                                <i class="fas fa-lock"></i> Completo
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            @else
                                {{-- Placeholder se non c'è immagine --}}
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                     style="height: 200px;">
                                    <div class="text-center text-muted">
                                        <i class="fas fa-calendar-alt fa-3x mb-2"></i>
                                        <p class="mb-0 small">Nessuna immagine</p>
                                    </div>
                                </div>
                            @endif

                            <div class="card-body">
                                <h5 class="card-title {{ $event->isFull() ? 'text-muted' : '' }}">{{ $event->title }}</h5>
                                <div class="mb-3">
                                    <span class="badge bg-primary">
                                        <i class="fas fa-calendar"></i>
                                        {{ $event->italian_event_date ?? ($event->date ? $event->date->format('d/m/Y H:i') : '') }}
                                    </span>
                                    <span class="badge bg-{{ $event->isFull() ? 'danger' : 'secondary' }} ms-1">
                                        <i class="fas fa-users"></i>
                                        {{ $event->participants_count }}
                                        @if($event->max_participants)
                                            / {{ $event->max_participants }}
                                        @endif
                                    </span>
                                </div>
                                <p class="card-text">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <strong>{{ $event->city }}</strong>
                                </p>
                                <div class="card-text text-muted small event-preview">
                                    {{ $event->getHomepagePreview() }}
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
                            <div class="card-footer bg-transparent">
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
        .hero-section {
            position: relative;
            width: 70%;
            margin: 0 auto;
            border-radius: 6px;
            overflow: hidden;
            border: 3px solid #f5c400;
            box-shadow: 0 0 0 2px #000;
            background: #000;
        }
        .hero-img {
            width: 100%;
            height: auto;
            display: block;
        }

        @media (max-width: 767.98px) {
            .hero-section {
                width: 100%;
                max-width: 100%;
            }

            .hero-img {
                max-height: 200px;
                height: 200px;
                object-fit: cover;
                object-position: center;
            }
        }

        @media (max-width: 374.98px) {
            .hero-img {
                max-height: 160px;
                height: 160px;
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
        .event-preview {
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
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
