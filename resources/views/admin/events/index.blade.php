@extends('layouts.app')

@section('title', 'Gestione Eventi - Admin')
@section('no_sidebar', '1')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="display-4">
                        <i class="fas fa-calendar-alt"></i> Gestione Eventi
                    </h1>
                    <div class="d-flex gap-2">
                        <a href="{{ route('home') }}" class="btn btn-secondary">
                            <i class="fas fa-home"></i> Torna alla home
                        </a>
                        <a href="{{ route('admin.events.create') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> Nuovo Evento
                        </a>
                    </div>
                </div>

                <!-- Statistiche -->
                <div class="row mb-4 admin-events-stats">
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
                                <table class="table table-striped table-hover table-sm admin-events-table">
                                    <thead>
                                    <tr>
                                        <th>Titolo</th>
                                        <th>Passato</th>
                                        <th>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark fw-semibold admin-events-sort" data-sort-key="data">
                                                Data
                                                <span class="admin-events-sort-icons" aria-hidden="true">
                                                    <i class="fas fa-sort-up"></i>
                                                    <i class="fas fa-sort-down"></i>
                                                </span>
                                            </button>
                                        </th>
                                        <th>Luogo</th>
                                        <th>Iscritti</th>
                                        <th>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark fw-semibold admin-events-sort" data-sort-key="stato">
                                                Attiva
                                                <span class="admin-events-sort-icons" aria-hidden="true">
                                                    <i class="fas fa-sort-up"></i>
                                                    <i class="fas fa-sort-down"></i>
                                                </span>
                                            </button>
                                        </th>
                                        <th>Creato da</th>
                                        <th>Azioni</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($events as $event)
                                        <tr class="{{ $event->date < now() ? 'table-secondary' : '' }}"
                                            data-sort-data="{{ $event->date ? $event->date->timestamp : 0 }}"
                                            data-sort-stato="{{ $event->is_active ? 1 : 0 }}">
                                            <td>
                                                <strong>{{ $event->title }}</strong>
                                            </td>
                                            <td>
                                                @if($event->date < now())
                                                    <span class="badge bg-secondary">Passato</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>{{ $event->date ? $event->date->format('d/m/y H:i') : '—' }}</td>
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
                                                    <span class="badge bg-danger">Disab.</span>
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
                                                       class="btn btn-sm btn-outline-secondary"
                                                       title="Modifica">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.events.toggle-status', $event) }}"
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm {{ $event->is_active ? 'btn-outline-brown' : 'btn-outline-success' }}"
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
        .admin-events-stats .card-body {
            padding: 0.42rem 0.5rem;
        }
        .admin-events-stats .card-title {
            font-size: 0.9rem;
            margin-bottom: 0.15rem;
            line-height: 1.05;
        }
        .admin-events-stats .card-text {
            font-size: 1.15rem;
            line-height: 1;
            margin-bottom: 0;
        }
        .admin-events-table th,
        .admin-events-table td {
            padding-top: 0.12rem;
            padding-bottom: 0.12rem;
            padding-left: 0.55rem;
            padding-right: 0.55rem;
            vertical-align: middle;
            line-height: 1;
            font-size: 0.82rem;
        }
        .admin-events-table .badge {
            font-size: 0.66rem;
            padding: 0.12rem 0.32rem;
        }
        .admin-events-sort-icons {
            display: inline-flex;
            flex-direction: column;
            line-height: 0.6;
            margin-left: 0.2rem;
            opacity: 0.75;
            vertical-align: middle;
        }
        .admin-events-sort-icons .fa-sort-up,
        .admin-events-sort-icons .fa-sort-down {
            font-size: 0.78em;
            height: 0.52em;
        }
        .admin-events-sort[data-dir="asc"] .fa-sort-up { opacity: 1; }
        .admin-events-sort[data-dir="asc"] .fa-sort-down { opacity: 0.25; }
        .admin-events-sort[data-dir="desc"] .fa-sort-up { opacity: 0.25; }
        .admin-events-sort[data-dir="desc"] .fa-sort-down { opacity: 1; }
        .admin-events-table td:first-child strong {
            display: inline-block;
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 0.82rem;
        }
        .btn-group .btn {
            margin-right: 0.25rem;
        }
        .btn-group .btn:last-child {
            margin-right: 0;
        }
        .btn-outline-brown {
            color: #b8860b;
            border-color: #b8860b;
        }
        .btn-outline-brown:hover {
            color: #fff;
            background-color: #b8860b;
            border-color: #b8860b;
        }
    </style>

    <script>
        (function () {
            var tbody = document.querySelector('.admin-events-table tbody');
            if (!tbody) return;

            var sortButtons = Array.prototype.slice.call(document.querySelectorAll('.admin-events-sort'));
            var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));

            sortButtons.forEach(function (btn) {
                var key = btn.getAttribute('data-sort-key');
                var def = key === 'data' ? 'desc' : 'asc';
                btn.setAttribute('data-dir', def);

                btn.addEventListener('click', function () {
                    var dir = btn.getAttribute('data-dir') === 'asc' ? 'desc' : 'asc';
                    btn.setAttribute('data-dir', dir);

                    var attr = key === 'data' ? 'data-sort-data' : 'data-sort-stato';
                    rows.sort(function (a, b) {
                        var av = parseInt(a.getAttribute(attr) || '0', 10);
                        var bv = parseInt(b.getAttribute(attr) || '0', 10);
                        return dir === 'asc' ? (av - bv) : (bv - av);
                    });

                    rows.forEach(function (tr) { tbody.appendChild(tr); });
                });
            });
        })();
    </script>
@endsection
