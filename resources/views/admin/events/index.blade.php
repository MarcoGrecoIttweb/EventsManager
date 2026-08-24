@extends('layouts.app')

@section('title', 'Gestione Eventi - Admin')
@section('no_sidebar', '1')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="mb-4">
                    <h1 class="display-4 mb-0">
                        <i class="fas fa-calendar-alt"></i> Gestione Eventi
                    </h1>
                </div>

                <!-- Ricerca + Statistiche (stessa riga) -->
                <div class="row mb-4 admin-events-stats g-2 align-items-stretch">
                    <div class="col-12 col-md-auto d-flex admin-events-search-wrap">
                        <div class="card h-100 admin-events-search-card flex-fill">
                            <div class="card-body">
                                <form method="GET" action="{{ route('admin.events.index') }}" class="admin-events-search">
                                    <div class="d-flex flex-wrap align-items-end gap-2 admin-events-search-inner">
                                        <div class="admin-events-search-field admin-events-search-field--select flex-shrink-0">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">Cerca per</span>
                                                <select id="adminEventsSearchField" name="field" class="form-select" aria-label="Cerca per">
                                                    <option value="nome" {{ request('field', 'nome') === 'nome' ? 'selected' : '' }}>Evento</option>
                                                    <option value="locale" {{ request('field') === 'locale' ? 'selected' : '' }}>Locale</option>
                                                    <option value="indirizzo" {{ request('field') === 'indirizzo' ? 'selected' : '' }}>Indirizzo</option>
                                                    <option value="data" {{ request('field') === 'data' ? 'selected' : '' }}>Data</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="admin-events-search-field admin-events-search-field--q flex-shrink-0">
                                            <div class="input-group input-group-sm">
                                                <span id="adminEventsSearchQueryLabel" class="input-group-text">{{ request('field') === 'data' ? 'Data' : 'Testo' }}</span>
                                                <input
                                                    id="adminEventsSearchQuery"
                                                    type="{{ request('field') === 'data' ? 'date' : 'search' }}"
                                                    name="q"
                                                    value="{{ request('q', '') }}"
                                                    class="form-control"
                                                    placeholder="Scrivi…"
                                                    autocomplete="off"
                                                    aria-label="Testo"
                                                    aria-autocomplete="list"
                                                    list="adminEventsSearchSuggestions"
                                                >
                                            </div>
                                            <datalist id="adminEventsSearchSuggestions"></datalist>
                                        </div>
                                        <div class="admin-events-search-actions d-flex gap-2 flex-shrink-0">
                                            <button type="submit" class="btn btn-sm btn-primary" data-hint="Filtra la lista eventi">
                                                <i class="fas fa-search"></i> Cerca
                                            </button>
                                            <a href="{{ route('admin.events.index') }}" class="btn btn-sm btn-outline-secondary" data-hint="Rimuovi filtri e mostra tutti gli eventi">
                                                <i class="fas fa-undo"></i> Reset
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto d-flex">
                        <div class="card bg-primary text-white h-100 w-100 flex-fill">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <h5 class="card-title">Totale Eventi</h5>
                                <h3 class="card-text">{{ $events->total() }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto d-flex">
                        <div class="card bg-warning text-white h-100 w-100 flex-fill">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <h5 class="card-title text-dark">Eventi Passati</h5>
                                <h3 class="card-text text-dark">{{ $events->where('date', '<', now())->count() }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto d-flex admin-events-top-actions">
                        <div class="card admin-events-actions-card border border-secondary bg-light h-100 w-100 flex-fill">
                            <div class="card-body d-flex align-items-center justify-content-center py-2 px-2">
                                <div class="d-flex flex-wrap align-items-center justify-content-center gap-2">
                                    <button type="button"
                                            class="btn btn-success btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#adminActiveEventsModal"
                                            data-hint="Elenco eventi attivi da oggi in poi (totale attivi sul badge)">
                                        <i class="fas fa-check-circle"></i> Eventi attivi
                                        <span class="badge bg-dark ms-1">{{ (int) ($activePublishedCount ?? 0) }}</span>
                                    </button>
                                    <button type="button"
                                            class="btn btn-warning text-dark btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#adminSuspendedEventsModal"
                                            data-hint="Elenco eventi sospesi da riprogrammare">
                                        <i class="fas fa-pause-circle"></i> Eventi sospesi
                                        <span class="badge bg-dark ms-1">{{ (int) ($suspendedUpcomingCount ?? 0) }}</span>
                                    </button>
                                    <a href="{{ route('admin.events.create') }}" class="btn btn-success btn-sm" data-hint="Crea un nuovo evento">
                                        <i class="fas fa-plus"></i> Nuovo Evento
                                    </a>
                                    <a href="{{ route('home') }}" class="btn btn-secondary btn-sm" data-hint="Torna alla homepage">
                                        <i class="fas fa-home"></i> Torna alla home
                                    </a>
                                </div>
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
                                            <td>
                                                {{ $event->city }}@if($event->address)<span class="text-muted"> · {{ $event->address }}</span>@endif
                                            </td>
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
                                                @if($event->user)
                                                    <a href="{{ route('profile.show', $event->user) }}" class="text-decoration-none">
                                                        {{ $event->user->nickname }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">Utente cancellato</span>
                                                @endif
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

    <div class="modal fade" id="adminSuspendedEventsModal" tabindex="-1" aria-labelledby="adminSuspendedEventsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="adminSuspendedEventsModalLabel">Eventi sospesi (Da riprogrammare)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <div id="adminSuspendedEventsBody">
                        <p class="text-muted mb-0"><i class="fas fa-spinner fa-spin me-1"></i>Caricamento…</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="adminActiveEventsModal" tabindex="-1" aria-labelledby="adminActiveEventsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="adminActiveEventsModalLabel">Eventi attivi <span class="fs-6 fw-normal text-muted">(da oggi in poi)</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <div id="adminActiveEventsBody">
                        <p class="text-muted mb-0"><i class="fas fa-spinner fa-spin me-1"></i>Caricamento…</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .admin-events-stats .card-body {
            padding: 0.28rem 0.45rem;
        }
        .admin-events-stats .card.bg-primary,
        .admin-events-stats .card.bg-warning,
        .admin-events-stats .admin-events-actions-card {
            width: fit-content;
            min-width: 10rem;
        }
        .admin-events-stats .admin-events-actions-card {
            min-width: min(100%, 28rem);
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
        /* Box ricerca: larghezza aderente ai campi, non tutta la riga */
        .admin-events-search-wrap {
            flex: 0 0 auto;
            width: auto;
            max-width: 100%;
        }
        .admin-events-search-card {
            width: fit-content;
            max-width: 100%;
        }
        .admin-events-search-inner {
            row-gap: 0.35rem;
        }
        .admin-events-search-field--select .form-select {
            width: auto;
            min-width: 6.5rem;
        }
        .admin-events-search-field--q .input-group {
            width: auto;
        }
        .admin-events-search-field--q .form-control {
            width: 12.5rem;
            max-width: min(18rem, 72vw);
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
        /* Modale «Eventi sospesi»: contenitore bordo verde; box distinti da sfondi tenui (no bordo blu) */
        .admin-suspended-events-list {
            border: 2px solid #198754;
            border-radius: 0.5rem;
            padding: 0.45rem;
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
            background: #f8f9fa;
        }
        .admin-suspended-event-row {
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(0, 7rem) minmax(0, 1fr) minmax(0, 6.85rem);
            gap: 0.35rem;
            align-items: stretch;
        }
        @media (max-width: 767.98px) {
            .admin-suspended-event-row {
                grid-template-columns: 1fr;
            }
        }
        .admin-suspended-event-box {
            border: none;
            border-radius: 0.35rem;
            padding: 0.35rem 0.45rem;
            min-width: 0;
        }
        .admin-suspended-event-box--title {
            background-color: #e8f2fe;
            color: #212529;
        }
        .admin-suspended-event-box--date {
            background-color: #e8f6ee;
            color: #212529;
        }
        .admin-suspended-event-box--place {
            background-color: #fff6e5;
            color: #212529;
        }
        .admin-suspended-event-box--actions {
            background-color: #f2ebfb;
            color: #212529;
        }
        .admin-suspended-event-label {
            display: block;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: rgba(33, 37, 41, 0.55);
            margin-bottom: 0.12rem;
            font-weight: 600;
            line-height: 1.1;
        }
        .admin-suspended-event-box--actions .admin-suspended-actions-pair {
            max-width: none;
        }
        /* Modale «Eventi sospesi»: Apri (verde) sopra, Modifica (giallo) sotto, compatti */
        .admin-suspended-actions-pair {
            display: flex;
            flex-direction: column;
            flex-wrap: nowrap;
            align-items: stretch;
            gap: 0.22rem;
            width: 100%;
            max-width: 6.75rem;
        }
        .admin-suspended-actions-pair .admin-suspended-action-btn {
            width: 100%;
            padding-top: 0.1rem;
            padding-bottom: 0.1rem;
            padding-left: 0.35rem;
            padding-right: 0.35rem;
            font-size: 0.72rem;
            line-height: 1.12;
            text-align: center;
            white-space: nowrap;
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
                if (field === 'data') {
                    clearOptions();
                    return;
                }
                var key = field + '|' + q;
                if (key === lastKey) return;
                lastKey = key;

                if (q.length < 2) {
                    clearOptions();
                    return;
                }

                var url = endpoint + '?field=' + encodeURIComponent(field) + '&q=' + encodeURIComponent(q);
                fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(function (r) { return r.ok ? r.json() : []; })
                    .then(function (items) { setOptions(Array.isArray(items) ? items : []); })
                    .catch(function () { clearOptions(); });
            }

            function schedule() {
                if (timer) window.clearTimeout(timer);
                timer = window.setTimeout(fetchSuggestions, 200);
            }

            input.addEventListener('input', function () {
                lastKey = '';
                schedule();
            });
            input.addEventListener('focus', schedule);
            input.addEventListener('compositionend', schedule);

            var label = document.getElementById('adminEventsSearchQueryLabel');

            function syncFieldMode() {
                if (select.value === 'data') {
                    input.type = 'date';
                    input.removeAttribute('list');
                    input.placeholder = '';
                    if (label) label.textContent = 'Data';
                    clearOptions();
                } else {
                    input.type = 'search';
                    input.setAttribute('list', 'adminEventsSearchSuggestions');
                    input.placeholder = 'Scrivi…';
                    if (label) label.textContent = 'Testo';
                }
            }

            select.addEventListener('change', function () {
                lastKey = '';
                clearOptions();
                syncFieldMode();
                schedule();
            });
        })();
    </script>
    <script>
        window.ADMIN_SUSPENDED_EVENTS_JSON_URL = @json(route('admin.events.suspended-upcoming-future'));
        window.ADMIN_ACTIVE_EVENTS_JSON_URL = @json(route('admin.events.active-published'));
        (function () {
            function escHtml(t) {
                if (t == null) return '';
                return String(t)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            function renderAdminEventsModalList(bodyEl, rows) {
                if (!rows.length) {
                    bodyEl.innerHTML = '<p class="text-muted mb-0">Nessun evento in questa fascia.</p>';
                    return;
                }
                var html = '<div class="admin-suspended-events-list">';
                for (var i = 0; i < rows.length; i++) {
                    var r = rows[i];
                    var hrefEdit = String(r.edit_url || '').replace(/"/g, '&quot;');
                    var hrefShow = String(r.show_url || '').replace(/"/g, '&quot;');
                    html += '<div class="admin-suspended-event-row">' +
                        '<div class="admin-suspended-event-box admin-suspended-event-box--title">' +
                        '<span class="admin-suspended-event-label">Evento</span>' +
                        '<div class="small mb-0">' + escHtml(r.title) + '</div></div>' +
                        '<div class="admin-suspended-event-box admin-suspended-event-box--date">' +
                        '<span class="admin-suspended-event-label">Data</span>' +
                        '<div class="small text-nowrap mb-0">' + escHtml(r.date) + '</div></div>' +
                        '<div class="admin-suspended-event-box admin-suspended-event-box--place">' +
                        '<span class="admin-suspended-event-label">Luogo</span>' +
                        '<div class="small mb-0">' + escHtml(r.place) + '</div></div>' +
                        '<div class="admin-suspended-event-box admin-suspended-event-box--actions">' +
                        '<span class="admin-suspended-event-label">Azioni</span>' +
                        '<div class="admin-suspended-actions-pair">' +
                        '<a href="' + hrefShow + '" class="btn btn-success btn-sm admin-suspended-action-btn" target="_blank" rel="noopener" title="Vedi scheda evento (senza modifica)">Apri</a>' +
                        '<a href="' + hrefEdit + '" class="btn btn-warning text-dark btn-sm admin-suspended-action-btn" target="_blank" rel="noopener">Modifica</a>' +
                        '</div></div></div>';
                }
                html += '</div>';
                bodyEl.innerHTML = html;
            }

            function wireModal(modalEl, bodyEl, jsonUrl) {
                if (!modalEl || !bodyEl || !jsonUrl) return;
                modalEl.addEventListener('show.bs.modal', function () {
                    bodyEl.innerHTML = '<p class="text-muted mb-0"><i class="fas fa-spinner fa-spin me-1"></i>Caricamento…</p>';
                    fetch(jsonUrl, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin'
                    })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            renderAdminEventsModalList(bodyEl, data.events || []);
                        })
                        .catch(function () {
                            bodyEl.innerHTML = '<p class="text-danger mb-0">Impossibile caricare l’elenco.</p>';
                        });
                });
            }

            wireModal(
                document.getElementById('adminSuspendedEventsModal'),
                document.getElementById('adminSuspendedEventsBody'),
                window.ADMIN_SUSPENDED_EVENTS_JSON_URL
            );
            wireModal(
                document.getElementById('adminActiveEventsModal'),
                document.getElementById('adminActiveEventsBody'),
                window.ADMIN_ACTIVE_EVENTS_JSON_URL
            );
        })();
    </script>
@endsection
