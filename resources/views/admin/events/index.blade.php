@extends('layouts.app')

@section('title', 'Gestione Eventi - Admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="display-4">
                        <i class="fas fa-calendar-alt"></i> Gestione Eventi
                    </h1>
                    <a href="{{ route('admin.events.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Nuovo Evento
                    </a>
                </div>

                <!-- Statistiche -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Totale Eventi</h5>
                                <h3 class="card-text">{{ $events->total() }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Eventi Attivi</h5>
                                <h3 class="card-text">{{ $events->where('is_active', true)->count() }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Eventi Passati</h5>
                                <h3 class="card-text">{{ $events->where('date', '<', now())->count() }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Con Ospiti</h5>
                                <h3 class="card-text">{{ $events->where('allow_guests', true)->count() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabella Eventi -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Lista Eventi</h5>
                    </div>
                    <div class="card-body">
                        @if($events->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                    <tr>
                                        <th>Titolo</th>
                                        <th>Data</th>
                                        <th>Luogo</th>
                                        <th>Partecipanti</th>
                                        <th>Stato</th>
                                        <th>Ospiti</th>
                                        <th>Creato da</th>
                                        <th>Azioni</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($events as $event)
                                        <tr class="{{ $event->date < now() ? 'table-secondary' : '' }}">
                                            <td>
                                                <strong>{{ $event->title }}</strong>
                                                @if($event->date < now())
                                                    <span class="badge bg-secondary ms-1">Passato</span>
                                                @endif
                                            </td>
                                            <td>{{ $event->date->format('d/m/Y H:i') }}</td>
                                            <td>{{ $event->city }}</td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $event->participants_count }}
                                                    @if($event->max_participants)
                                                        / {{ $event->max_participants }}
                                                    @endif
                                                </span>
                                            </td>
                                            <td>
                                                @if($event->is_active)
                                                    <span class="badge bg-success">Attivo</span>
                                                @else
                                                    <span class="badge bg-danger">Disattivato</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($event->allow_guests)
                                                    <span class="badge bg-warning">Sì (max {{ $event->max_guests_per_user }})</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('profile.show', $event->user) }}" class="text-decoration-none">
                                                    {{ $event->user->nickname }}
                                                </a>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('events.show', $event) }}"
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="Vedi" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.events.edit', $event) }}"
                                                       class="btn btn-sm btn-outline-warning"
                                                       title="Modifica">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="{{ route('admin.events.show', $event) }}"
                                                       class="btn btn-sm btn-outline-info"
                                                       title="Dettagli Admin">
                                                        <i class="fas fa-info-circle"></i>
                                                    </a>
                                                    <form action="{{ route('admin.events.toggle-status', $event) }}"
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-{{ $event->is_active ? 'warning' : 'success' }}"
                                                                title="{{ $event->is_active ? 'Disattiva' : 'Attiva' }}">
                                                            <i class="fas fa-{{ $event->is_active ? 'pause' : 'play' }}"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.events.destroy', $event) }}"
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                onclick="return confirm('Sei sicuro di voler eliminare questo evento?')"
                                                                title="Elimina">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginazione -->
                            <div class="d-flex justify-content-center mt-4">
                                {{ $events->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5>Nessun evento trovato</h5>
                                <p class="text-muted">Crea il primo evento per iniziare.</p>
                                <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Crea il Primo Evento
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .btn-group .btn {
            margin-right: 0.25rem;
        }
        .btn-group .btn:last-child {
            margin-right: 0;
        }
    </style>
@endsection
