@extends('layouts.app')

@section('title', 'Eventi - EventSite')

@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="display-4">Prossimi Eventi</h1>
                <p class="lead">Scopri gli eventi in programma nella tua città</p>
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
                                        @if($event->isFull())
                                            <i class="fas fa-lock ms-1"></i>
                                        @endif
                            </span>
                                </div>
                                <p class="card-text">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <strong>{{ $event->city }}</strong>
                                </p>
                                <p class="card-text text-muted small">
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
                                    {{ $event->isFull() ? 'Visualizza (Completo)' : 'Dettagli Evento' }}
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
