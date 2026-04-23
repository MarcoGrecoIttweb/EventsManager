@extends('layouts.app')

@section('title', 'Evento in comune')

@section('content')
    <div class="container-fluid py-3" style="max-width: 58rem;">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <h1 class="h3 mb-0">
                <i class="fas fa-random text-primary"></i> Trova evento in comune
            </h1>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.common-event.search') }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-12 col-md-5">
                        <label class="form-label fw-semibold">Username 1</label>
                        <input type="text" name="username1" class="form-control @error('username1') is-invalid @enderror" value="{{ old('username1', $username1 ?? '') }}" required>
                        @error('username1')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-5">
                        <label class="form-label fw-semibold">Username 2</label>
                        <input type="text" name="username2" class="form-control @error('username2') is-invalid @enderror" value="{{ old('username2', $username2 ?? '') }}" required>
                        @error('username2')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-2 d-grid">
                        <button class="btn btn-primary">
                            <i class="fas fa-search"></i> Cerca
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-3">
            @if(($username1 ?? '') !== '' || ($username2 ?? '') !== '')
                @if(!$user1 || !$user2)
                    <div class="alert alert-warning border shadow-sm">
                        <div class="fw-semibold mb-1">Utenti non trovati</div>
                        <div class="small">
                            @if(!$user1)
                                - Username 1: <span class="fw-semibold">{{ $username1 }}</span> non esiste.<br>
                            @endif
                            @if(!$user2)
                                - Username 2: <span class="fw-semibold">{{ $username2 }}</span> non esiste.
                            @endif
                        </div>
                    </div>
                @else
                    @if($commonEvents->count() === 0)
                        <div class="alert alert-light border shadow-sm">
                            Nessun evento in comune trovato per <span class="fw-semibold">{{ $username1 }}</span> e <span class="fw-semibold">{{ $username2 }}</span>.
                        </div>
                    @else
                        <div class="card shadow-sm">
                            <div class="card-header bg-white fw-semibold">
                                Eventi in comune (max 20)
                            </div>
                            <div class="list-group list-group-flush">
                                @foreach($commonEvents as $event)
                                    <div class="list-group-item">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                            <div class="min-w-0">
                                                <div class="fw-semibold text-truncate">
                                                    <a href="{{ route('events.show', $event) }}" class="text-decoration-none">
                                                        {{ $event->title }}
                                                    </a>
                                                </div>
                                                <div class="small text-muted">
                                                    <i class="fas fa-calendar"></i>
                                                    {{ $event->italian_event_date ?? ($event->date ? $event->date->format('d/m/Y H:i') : '') }}
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('events.show', $event) }}" target="_blank" rel="noopener">
                                                    Apri evento
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            @endif
        </div>
    </div>
@endsection

