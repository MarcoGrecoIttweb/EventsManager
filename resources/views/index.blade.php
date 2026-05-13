@extends('layouts.app')

@section('title', 'Eventi - Excursio')

@section('content')
    <div class="container">
        <style>
            .event-box .event-public-desc-preview {
                color: #000;
            }
            .event-public-meta--hint { cursor: help; }
            .event-public-meta--part-gap,
            .event-public-meta--part-gap i,
            .event-public-meta--part-gap strong {
                color: #fff !important;
            }
            .event-public-meta--part-gap {
                font-weight: 800;
                border: 2px solid rgba(114, 10, 10, 0.85);
                animation: eventPublicPartGapBlink 1s ease-in-out infinite;
            }
            @keyframes eventPublicPartGapBlink {
                0%, 100% { background-color: #b02a37 !important; box-shadow: 0 0 0 rgba(220, 53, 69, 0); }
                50% { background-color: #dc3545 !important; box-shadow: 0 0 14px rgba(220, 53, 69, 0.95); }
            }
        </style>
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="display-4">Prossimi Eventi</h1>
                <p class="lead">Scopri gli eventi in programma nella tua città</p>
            </div>
            <div class="col-md-4 text-end">
                @auth
                    @php $me = auth()->user(); @endphp
                    @if($me->canManageEvents())
                        @if($me->isAdmin())
                            <a href="{{ route('admin.events.create') }}" class="btn btn-success mb-2">
                                <i class="fas fa-plus"></i> Crea Evento
                            </a>
                        @else
                            <a href="{{ route('manage.events.create') }}" class="btn btn-success mb-2">
                                <i class="fas fa-plus"></i> Crea Evento
                            </a>
                        @endif
                    @endif
                @endauth
            </div>
        </div>

        @if($events->count() > 0)
            <div class="row">
                @foreach($events as $event)
                    @php
                        $rawMaxHome = $event->max_participants;
                        $maxPostiHome = ($rawMaxHome !== null && $rawMaxHome !== '') ? (int) $rawMaxHome : null;
                        $cntPartHome = (int) $event->participants_count;
                        $postiLiberiHome = ($maxPostiHome !== null && !$event->isFull()) ? max(0, $maxPostiHome - $cntPartHome) : null;
                        $mancanoPerCompletareMaxHome = $postiLiberiHome !== null && $postiLiberiHome >= 1 && $postiLiberiHome <= 2;
                    @endphp
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card event-box h-100">
                            @if($event->isFull())
                                <div class="card-header bg-danger text-white text-center py-2">
                                    <small><i class="fas fa-exclamation-triangle"></i> <strong>EVENTO AL COMPLETO</strong></small>
                                </div>
                            @endif
                            <div class="card-body">
                                <h5 class="card-title {{ $event->isFull() ? 'text-muted' : '' }}">{{ $event->title }}</h5>
                                <div class="mb-3">
                            <span class="badge bg-primary event-public-meta--hint"
                                  title="Indica data e ora di inizio dell’evento.">
                                <i class="fas fa-calendar"></i>
                                {{ $event->italian_event_date ?? ($event->date ? $event->date->format('d/m/Y H:i') : '') }}
                            </span>
                                    <span class="badge ms-1 event-public-meta--hint {{ $event->isFull() ? 'bg-danger' : 'bg-secondary' }} {{ $mancanoPerCompletareMaxHome ? 'event-public-meta--part-gap' : '' }}"
                                          title="{{ $mancanoPerCompletareMaxHome
                                              ? 'Restano 1 o 2 posti liberi rispetto al massimo: il box lampeggia per segnalare che l’evento è quasi al completo.'
                                              : ($maxPostiHome !== null
                                                  ? 'Mostra quanti partecipanti ci sono (iscritti più eventuali ospiti) rispetto al numero massimo di posti previsto dall’organizzatore.'
                                                  : 'Mostra il numero di partecipanti (iscritti più eventuali ospiti); l’organizzatore non ha indicato un numero massimo di posti.') }}">
                                <i class="fas fa-users" aria-hidden="true"></i>
                                <strong>{{ $event->participants_count }}</strong>@if($maxPostiHome !== null)<span class="text-white-50 fw-normal"> / </span><strong>{{ $maxPostiHome }}</strong>@endif
                                        @if($event->isFull())
                                            <i class="fas fa-lock ms-1"></i>
                                        @endif
                            </span>
                                </div>
                                <p class="card-text">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <strong>{{ $event->city }}</strong>
                                </p>
                                <p class="card-text small event-public-desc-preview">
                                    {{ $event->short_preview }}
                                </p>

                                @if($event->isFull())
                                    <div class="alert alert-warning alert-sm mb-0 py-2">
                                        <small>
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Evento al completo</strong> - Non è più possibile iscriversi
                                        </small>
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="{{ route('events.show', $event) }}" class="btn btn-{{ $event->isFull() ? 'outline-secondary' : 'primary' }} w-100">
                                    <i class="fas fa-eye"></i>
                                    Visualizza Dettagli Evento
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h3>Nessun evento in programma</h3>
                <p class="text-muted">Non ci sono eventi in programma al momento.</p>
            </div>
        @endif
    </div>
@endsection
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Anteprima immagini prima dell'upload
            const imageInput = document.getElementById('gallery_images');
            const previewContainer = document.getElementById('imagePreviews');

            if (imageInput) {
                imageInput.addEventListener('change', function() {
                    previewContainer.innerHTML = '';
                    previewContainer.style.display = 'none';

                    if (this.files.length > 0) {
                        previewContainer.style.display = 'flex';

                        Array.from(this.files).forEach((file) => {
                            if (file.type.startsWith('image/')) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    const col = document.createElement('div');
                                    col.className = 'col-md-3 mb-3';
                                    col.innerHTML = `
                                <div class="card">
                                    <img src="${e.target.result}" class="card-img-top" style="height: 150px; object-fit: cover;">
                                    <div class="card-body">
                                        <small class="text-muted">${file.name}</small>
                                    </div>
                                </div>
                            `;
                                    previewContainer.appendChild(col);
                                };
                                reader.readAsDataURL(file);
                            }
                        });
                    }
                });
            }
        });
    </script>
@endsection

@section('sidebar_after_my_events')
    @auth
        @php $me = auth()->user(); @endphp
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-2">
                <div class="small fw-bold mb-2">
                    <i class="fas fa-calendar-plus text-success me-1"></i> Vuoi organizzare eventi?
                </div>
                <div class="small text-muted mb-2">
                    Crea un nuovo evento oppure richiedi l’abilitazione come organizzatore.
                </div>
                <div class="d-grid">
                    @if($me->canManageEvents())
                        @if($me->isAdmin())
                            <a href="{{ route('admin.events.create') }}" class="btn btn-success btn-sm">
                                Crea evento
                            </a>
                        @else
                            <a href="{{ route('manage.events.create') }}" class="btn btn-success btn-sm">
                                Crea evento
                            </a>
                        @endif
                    @else
                        <a href="{{ route('profile.edit', $me) }}" class="btn btn-outline-success btn-sm">
                            Richiedi abilitazione
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endauth
@endsection
