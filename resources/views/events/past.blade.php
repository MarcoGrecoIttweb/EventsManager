@extends('layouts.app')

@section('title', 'Eventi Passati - Excursio')

@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="display-4">Eventi Passati</h1>
                <p class="lead">Rivedi gli eventi a cui hai partecipato o che si sono già svolti</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('events.index') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Torna agli Eventi Futuri
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body">
                {{-- Ricerca: visibile solo ad admin --}}
                @if(auth()->check() && auth()->user()->isAdmin())
                    <form method="GET" action="{{ route('events.past') }}" class="row g-2 align-items-end">
                        <div class="col-12 col-md-4">
                            <label for="past_search_field" class="form-label fw-semibold mb-1">Cerca per</label>
                            <select name="field" id="past_search_field" class="form-select">
                                <option value="title" {{ ($field ?? 'title') === 'title' ? 'selected' : '' }}>Titolo</option>
                                <option value="description" {{ ($field ?? 'title') === 'description' ? 'selected' : '' }}>Descrizione</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="past_search_q" class="form-label fw-semibold mb-1">Testo</label>
                            <input
                                type="text"
                                name="q"
                                id="past_search_q"
                                class="form-control"
                                value="{{ $q ?? '' }}"
                                placeholder="Scrivi qui…"
                            >
                        </div>
                        <div class="col-12 col-md-2 d-grid">
                            <button type="submit" class="btn btn-dark">
                                <i class="fas fa-search"></i> Cerca
                            </button>
                        </div>
                        @if(($q ?? '') !== '')
                            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2">
                                <small class="text-muted">
                                    Ricerca attiva: <strong>{{ ($field ?? 'title') === 'description' ? 'Descrizione' : 'Titolo' }}</strong> contiene “<strong>{{ $q }}</strong>”
                                </small>
                                <a href="{{ route('events.past', array_merge(request()->except('q', 'field', 'page'), [])) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times"></i> Pulisci
                                </a>
                            </div>
                        @endif
                    </form>
                    <hr class="my-3">
                @endif

                {{-- Filtro "Solo i miei eventi": per tutti gli utenti loggati --}}
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex flex-wrap gap-2">
                        @if(($mine ?? false))
                            <a href="{{ route('events.past', array_merge(request()->except('mine', 'page'), [])) }}"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-layer-group"></i> Tutti gli eventi
                            </a>
                        @else
                            <a href="{{ route('events.past', array_merge(request()->all(), ['mine' => 1])) }}"
                               class="btn btn-sm btn-success">
                                <i class="fas fa-user-check"></i> Solo i miei eventi
                            </a>
                        @endif
                    </div>
                    @if(($mine ?? false))
                        <span class="badge bg-success">Filtro: solo eventi a cui hai partecipato</span>
                    @endif
                </div>
            </div>
        </div>

        @if($events->count() > 0)
            <div class="row">
                @foreach($events as $event)
                    @php
                        $rawMaxPast = $event->max_participants;
                        $maxPostiPast = ($rawMaxPast !== null && $rawMaxPast !== '') ? (int) $rawMaxPast : null;
                        $cntPartPast = (int) $event->participants_count;
                        $postiLiberiPast = ($maxPostiPast !== null && !$event->isFull()) ? max(0, $maxPostiPast - $cntPartPast) : null;
                        $pastPartGapBlink = $postiLiberiPast !== null && $postiLiberiPast >= 1 && $postiLiberiPast <= 2;
                    @endphp
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card event-box h-100">
                            {{-- Badge Evento Passato --}}
                            <div class="card-header bg-secondary text-white text-center py-2">
                                <small><i class="fas fa-history"></i> <strong>EVENTO CONCLUSO</strong></small>
                            </div>

                            {{-- Thumbnail Image --}}
                            @if($event->cover_image_url)
                                <div class="position-relative">
                                    <img src="{{ $event->cover_image_url }}"
                                         alt="{{ $event->title }}"
                                         class="card-img-top"
                                         style="height: 200px; object-fit: cover; width: 100%; opacity: 0.8;">
                                    <div class="position-absolute top-0 start-0 m-2">
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-clock"></i> Passato
                                        </span>
                                    </div>
                                </div>
                            @else
                                {{-- Placeholder se non c'è immagine --}}
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                     style="height: 200px; opacity: 0.7;">
                                    <div class="text-center text-muted">
                                        <i class="fas fa-calendar-alt fa-3x mb-2"></i>
                                        <p class="mb-0 small">Nessuna immagine</p>
                                    </div>
                                </div>
                            @endif

                            <div class="card-body">
                                <h5 class="card-title text-muted">{{ $event->title }}</h5>
                                <div class="mb-3">
                                    <span class="badge bg-secondary event-past-meta--hint"
                                          title="Indica data e ora in cui si è svolto l’evento (evento concluso).">
                                        <i class="fas fa-calendar"></i>
                                        {{ $event->italian_event_date ?? ($event->date ? $event->date->format('d/m/Y H:i') : '') }}
                                    </span>
                                    <span class="badge ms-1 text-white event-past-meta--hint {{ $event->isFull() ? 'bg-danger' : 'bg-secondary' }} {{ $pastPartGapBlink ? 'event-past-meta--part-gap' : '' }}"
                                          title="{{ $pastPartGapBlink
                                              ? 'Capacità quasi raggiunta: risultavano solo 1 o 2 posti liberi rispetto al massimo (stessa evidenziazione visiva degli eventi futuri).'
                                              : ($maxPostiPast !== null
                                                  ? 'Mostra quanti partecipanti c’erano (iscritti più eventuali ospiti) rispetto al numero massimo di posti previsto.'
                                                  : 'Mostra il numero di partecipanti (iscritti più eventuali ospiti); non era indicato un numero massimo di posti.') }}">
                                        <i class="fas fa-users" aria-hidden="true"></i>
                                        <strong>{{ $event->participants_count }}</strong>@if($maxPostiPast !== null)<span class="text-white-50 fw-normal"> / </span><strong>{{ $maxPostiPast }}</strong>@endif
                                    </span>
                                </div>
                                <p class="card-text">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <strong>{{ $event->city }}</strong>
                                </p>
                                <div class="card-text small event-preview event-public-desc-preview">
                                    {{ $event->getHomepagePreview() }}
                                </div>

                                {{-- Indicatore partecipazione --}}
                                @auth
                                    @php
                                        $userParticipating = $event->participants->contains('userID', auth()->id());
                                    @endphp
                                    @if($userParticipating)
                                        <div class="alert alert-success alert-sm mb-0 py-2 mt-2">
                                            <small>
                                                <i class="fas fa-check-circle"></i>
                                                <strong>Hai partecipato a questo evento</strong>
                                            </small>
                                        </div>
                                    @endif
                                @endauth
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="d-grid gap-2">
                                    <a href="{{ route('events.show', $event) }}" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-eye"></i> Dettagli evento
                                    </a>
                                    @auth
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-warning w-100">
                                                <i class="fas fa-edit"></i> Modifica / ripristina data
                                            </a>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if(method_exists($events, 'links'))
                <div class="d-flex justify-content-center mt-4">
                    {{ $events->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                <h3>Nessun evento passato</h3>
                <p class="text-muted">Non ci sono eventi conclusi al momento.</p>
                <a href="{{ route('events.index') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-calendar"></i> Vedi Eventi Futuri
                </a>
            </div>
        @endif
    </div>

    <style>
        .event-past-meta--hint {
            cursor: help;
        }
        .event-past-meta--part-gap,
        .event-past-meta--part-gap i,
        .event-past-meta--part-gap strong {
            color: #fff !important;
        }
        .event-past-meta--part-gap {
            font-weight: 800;
            border: 2px solid rgba(114, 10, 10, 0.85);
            animation: eventPastPartGapBlink 1s ease-in-out infinite;
        }
        @keyframes eventPastPartGapBlink {
            0%, 100% {
                background-color: #b02a37 !important;
                box-shadow: 0 0 0 rgba(220, 53, 69, 0);
            }
            50% {
                background-color: #dc3545 !important;
                box-shadow: 0 0 14px rgba(220, 53, 69, 0.95);
            }
        }

        .event-public-desc-preview {
            color: #000;
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

    </style>
@endsection
