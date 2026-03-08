@extends('layouts.app')

@section('title', 'Excursio - Community di amici a Milano')

@section('content')
    {{-- Hero --}}
    <div class="hero-section mb-4">
        <img src="{{ asset('upload_immagini/hero.jpg') }}" alt="Excursio" class="hero-img">
    </div>

    {{-- Intro testuale (stile vecchio sito) --}}
    @guest
    <div class="intro-box mb-4">
        <p class="intro-text">
            Excursio è una community di amici, che propone iniziative con l'obiettivo di offrire opportunità per conoscere persone e fare nuove amicizie, per evadere dalla solita routine quotidiana. Le iniziative proposte si svolgono a Milano e sono di costi modesti, alla portata di tutti, e in alcune occasioni anche gratuite, senza alcun fine di lucro. <strong>La registrazione è gratuita.</strong>
        </p>
        <p class="intro-text">
            Dopo esserti registrato dovrai loggarti per poter visualizzare gli eventi proposti e potrai aggregarti senza alcun obbligo — la tua adesione è libera.
        </p>
        <div class="intro-actions">
            <a href="{{ route('register') }}" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Registrati
            </a>
            <a href="mailto:info@excursio.org" class="btn btn-outline-secondary ms-2">
                <i class="fas fa-envelope"></i> Scrivici
            </a>
        </div>
    </div>
    @endguest

    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Prossimi Eventi</h2>
            </div>
            <div class="col-md-4 text-end">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.events.create') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> Crea Evento
                        </a>
                    @endif
                @endauth
            </div>
        </div>

        @if($events->count() > 0)
            <div class="row">
                @foreach($events as $event)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm {{ $event->isFull() ? 'border-danger' : '' }}">
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
                                        @php
                                            try {
                                                $date = \Carbon\Carbon::parse($event->date);
                                                echo $date->format('d/m/Y H:i');
                                            } catch (\Exception $e) {
                                                echo $event->date;
                                            }
                                        @endphp
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
                                    <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100">
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
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h3>Nessun evento in programma</h3>
                <p class="text-muted">Non ci sono eventi in programma al momento.</p>
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.events.create') }}" class="btn btn-primary mt-3">
                            <i class="fas fa-plus"></i> Crea il primo evento
                        </a>
                    @endif
                @endauth
            </div>
        @endif
    </div>{{-- /container --}}

    <style>
        .hero-section {
            position: relative;
            width: 70%;
            margin: 0 auto;
            border-radius: 8px;
            overflow: hidden;
        }
        .hero-img {
            width: 100%;
            height: auto;
            display: block;
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
        .intro-box {
            background: #f8f9fa;
            border-left: 4px solid #0d6efd;
            border-radius: 6px;
            padding: 1.25rem 1.5rem;
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

        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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
