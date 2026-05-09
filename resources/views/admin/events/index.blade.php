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
                        <a href="{{ route('home') }}" class="btn btn-secondary" data-hint="Torna alla homepage">
                            <i class="fas fa-home"></i> Torna alla home
                        </a>
                        <a href="{{ route('admin.events.create') }}" class="btn btn-success" data-hint="Crea un nuovo evento">
                            <i class="fas fa-plus"></i> Nuovo Evento
                        </a>
                    </div>
                </div>

                <!-- Ricerca + Statistiche (stessa riga) -->
                <div class="row mb-4 admin-events-stats g-2 align-items-stretch">
                    <div class="col-12 col-md">
                        <div class="card h-100">
                            <div class="card-body">
                                <form method="GET" action="{{ route('admin.events.index') }}" class="admin-events-search">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-12 col-md-3">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">Cerca per</span>
                                                <select id="adminEventsSearchField" name="field" class="form-select" aria-label="Cerca per">
                                                    <option value="nome" {{ request('field', 'nome') === 'nome' ? 'selected' : '' }}>Evento</option>
                                                    <option value="locale" {{ request('field') === 'locale' ? 'selected' : '' }}>Locale</option>
                                                    <option value="indirizzo" {{ request('field') === 'indirizzo' ? 'selected' : '' }}>Indirizzo</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-5">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">Testo</span>
                                                <input
                                                    id="adminEventsSearchQuery"
                                                    type="text"
                                                    name="q"
                                                    value="{{ request('q', '') }}"
                                                    class="form-control"
                                                    placeholder="Scrivi…"
                                                    autocomplete="off"
                                                    aria-label="Testo"
                                                    list="adminEventsSearchSuggestions"
                                                >
                                            </div>
                                            <datalist id="adminEventsSearchSuggestions"></datalist>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-sm btn-primary flex-grow-1" data-hint="Filtra la lista eventi">
                                                    <i class="fas fa-search"></i> Cerca
                                                </button>
                                                <a href="{{ route('admin.events.index') }}" class="btn btn-sm btn-outline-secondary flex-grow-1" data-hint="Rimuovi filtri e mostra tutti gli eventi">
                                                    <i class="fas fa-undo"></i> Reset
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body text-center">
                                <h5 class="card-title">Totale Eventi</h5>
                                <h3 class="card-text">{{ $events->total() }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body text-center">
                                <h5 class="card-title">Eventi Attivi</h5>
                                <h3 class="card-text">{{ $events->where('is_active', true)->count() }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto">
                        <div class="card bg-warning text-white h-100">
                            <div class="card-body text-center">
                                <h5 class="card-title">Eventi Passati</h5>
                                <h3 class="card-text">{{ $events->where('date', '<', now())->count() }}</h3>
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
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark fw-semibold admin-events-sort" data-sort-key="data" data-hint="Ordina la tabella per data">
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
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark fw-semibold admin-events-sort" data-sort-key="stato" data-hint="Ordina la tabella per stato (attivo/disattivo)">
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
                                                       title="Vedi" target="_blank"
                                                       data-hint="Apri la pagina evento in una nuova scheda">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.events.edit', $event) }}"
                                                       class="btn btn-sm btn-outline-secondary"
                                                       title="Modifica"
                                                       data-hint="Apri la modifica di questo evento">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.events.toggle-status', $event) }}"
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm {{ $event->is_active ? 'btn-outline-brown' : 'btn-outline-success' }}"
                                                                title="{{ $event->is_active ? 'Disattiva' : 'Attiva' }}"
                                                                data-hint="{{ $event->is_active ? 'Disattiva l’evento (non sarà visibile tra i prossimi eventi)' : 'Attiva l’evento (torna visibile tra i prossimi eventi)' }}">
                                                            <i class="fas fa-{{ $event->is_active ? 'pause' : 'play' }}"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.events.destroy', $event) }}"
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                onclick="return confirm('Sei sicuro di voler eliminare questo evento?')"
                                                                title="Elimina"
                                                                data-hint="Elimina definitivamente questo evento">
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
                                {{ $events->appends(request()->query())->links() }}
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
            padding: 0.28rem 0.45rem;
        }
        .admin-events-stats .card.bg-primary,
        .admin-events-stats .card.bg-success,
        .admin-events-stats .card.bg-warning {
            width: fit-content;
            min-width: 10rem; /* abbastanza per titolo+numero senza sprechi */
        }
        .admin-events-stats .card-title {
            font-size: 0.78rem;
            margin-bottom: 0.05rem;
            line-height: 1.05;
            white-space: nowrap;
        }
        .admin-events-stats .card-text {
            font-size: 1.02rem;
            line-height: 1;
            margin-bottom: 0;
        }
        .admin-events-search .row.g-2 {
            --bs-gutter-x: 0.35rem;
            --bs-gutter-y: 0.2rem;
        }
        .admin-events-search .input-group-text {
            padding-top: 0.15rem;
            padding-bottom: 0.15rem;
        }
        .admin-events-search .form-select,
        .admin-events-search .form-control {
            padding-top: 0.15rem;
            padding-bottom: 0.15rem;
        }
        .admin-events-search .btn {
            padding-top: 0.18rem;
            padding-bottom: 0.18rem;
            line-height: 1.1;
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
            /* Allarga il titolo nella lista eventi admin (prima era troppo stretto) */
            max-width: min(520px, 55vw);
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

    @php
        // Usa la root della request per evitare 404 quando l'app è servita da una sottocartella (es. /excursio/public)
        $adminEventsSuggestionsEndpoint = rtrim(request()->root(), '/') . route('admin.events.suggestions', [], false);
    @endphp
    <script>
        (function () {
            var input = document.getElementById('adminEventsSearchQuery');
            var select = document.getElementById('adminEventsSearchField');
            var datalist = document.getElementById('adminEventsSearchSuggestions');
            if (!input || !select || !datalist) return;

            var endpoint = @json($adminEventsSuggestionsEndpoint);
            var timer = null;
            var lastKey = '';

            function clearOptions() {
                while (datalist.firstChild) datalist.removeChild(datalist.firstChild);
            }

            function setOptions(items) {
                clearOptions();
                (items || []).forEach(function (v) {
                    var opt = document.createElement('option');
                    opt.value = String(v || '');
                    datalist.appendChild(opt);
                });
            }

            function fetchSuggestions() {
                var q = (input.value || '').trim();
                var field = (select.value || 'nome').trim();
                var key = field + '|' + q;
                if (key === lastKey) return;
                lastKey = key;

                if (q.length < 2) {
                    clearOptions();
                    return;
                }

                var url = endpoint + '?field=' + encodeURIComponent(field) + '&q=' + encodeURIComponent(q);
                fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.ok ? r.json() : []; })
                    .then(function (items) { setOptions(items); })
                    .catch(function () { /* no-op */ });
            }

            function schedule() {
                if (timer) window.clearTimeout(timer);
                timer = window.setTimeout(fetchSuggestions, 220);
            }

            input.addEventListener('input', schedule);
            select.addEventListener('change', function () {
                lastKey = '';
                clearOptions();
                schedule();
            });
        })();
    </script>
@endsection
