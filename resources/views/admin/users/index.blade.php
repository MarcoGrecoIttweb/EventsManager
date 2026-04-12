@extends('layouts.app')

@section('title', 'Gestione Utenti - Admin')
@section('no_sidebar', '1')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                @if(request('registrations') === 'pending')
                    <div class="mb-3 w-100">
                        <div class="admin-users-pending-title-box">
                            <h1 class="display-5 admin-users-page-title admin-users-pending-heading mb-0 d-flex flex-wrap align-items-baseline gap-2 gap-md-3">
                                <span class="d-inline-flex align-items-center flex-wrap">
                                    <i class="fas fa-users-cog admin-users-page-title-icon"></i>
                                    <span class="admin-users-page-title-text">Gestione Utenti Sospesi</span>
                                </span>
                                <span class="small admin-users-pending-subtitle fw-normal">
                                    Stai visualizzando Solo la lista degli utenti sospesi
                                </span>
                            </h1>
                        </div>
                    </div>
                @else
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="display-5 admin-users-page-title">
                            <i class="fas fa-users-cog admin-users-page-title-icon"></i>
                            <span class="admin-users-page-title-text">Gestione Utenti</span>
                        </h1>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.users.logins') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-sign-in-alt"></i> Ingressi giornalieri utenti ult. 10 gg.
                            </a>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-home"></i> Home
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Tabella Utenti -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <div class="d-flex align-items-center gap-2 admin-users-header-inline">
                            <h5 class="mb-0">Lista Utenti</h5>
                            <div class="d-flex align-items-center gap-2 admin-user-stats-pills">
                                <span class="admin-user-stats-pill bg-danger text-white">
                                    <i class="fas fa-clock"></i>
                                    <span class="admin-user-stats-pill-label">Sospesi</span>
                                    <span class="admin-user-stats-pill-value">{{ $pendingCount }}</span>
                                </span>
                                <span class="admin-user-stats-pill bg-success text-white">
                                    <i class="fas fa-check-circle"></i>
                                    <span class="admin-user-stats-pill-label">Attivi</span>
                                    <span class="admin-user-stats-pill-value">{{ $approvedCount }}</span>
                                </span>
                                <span class="admin-user-stats-pill bg-danger text-white">
                                    <i class="fas fa-ban"></i>
                                    <span class="admin-user-stats-pill-label">Bannati</span>
                                    <span class="admin-user-stats-pill-value">{{ $bannedCount }}</span>
                                </span>
                            </div>
                            <div class="input-group input-group-sm admin-user-finder">
                                <label class="visually-hidden" for="userFinderField">Campo</label>
                                <select id="userFinderField" class="form-select">
                                    <option value="nome" selected>Nome</option>
                                    <option value="cognome">Cognome</option>
                                    <option value="nickname">Nickname</option>
                                </select>
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input id="userFinder"
                                       type="search"
                                       class="form-control"
                                       placeholder="Trova utente…"
                                       autocomplete="off"
                                       aria-label="Trova utente">
                                <button id="userFinderClear" type="button" class="btn btn-outline-light" title="Pulisci">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($users->count() > 0)
                            {{-- Tabella completa: visibile anche su smartphone (con scroll orizzontale) --}}
                            <div class="table-responsive admin-users-table-wrapper">
                                <table class="table table-striped table-hover table-sm align-middle admin-users-table">
                                    <thead>
                                    <tr>
                                        <th class="d-none d-lg-table-cell">ID</th>
                                        <th class="col-foto">Foto</th>
                                        <th class="col-nome">
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark fw-semibold admin-sort" data-sort-key="nome" aria-label="Ordina per nome">
                                                Nome
                                                <span class="admin-sort-icons" aria-hidden="true">
                                                    <i class="fas fa-sort-up"></i>
                                                    <i class="fas fa-sort-down"></i>
                                                </span>
                                            </button>
                                        </th>
                                        <th class="col-cognome">
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark fw-semibold admin-sort" data-sort-key="cognome" aria-label="Ordina per cognome">
                                                Cognome
                                                <span class="admin-sort-icons" aria-hidden="true">
                                                    <i class="fas fa-sort-up"></i>
                                                    <i class="fas fa-sort-down"></i>
                                                </span>
                                            </button>
                                        </th>
                                        <th>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark fw-semibold admin-sort" data-sort-key="nickname" aria-label="Ordina per nickname">
                                                Nickname
                                                <span class="admin-sort-icons" aria-hidden="true">
                                                    <i class="fas fa-sort-up"></i>
                                                    <i class="fas fa-sort-down"></i>
                                                </span>
                                            </button>
                                        </th>
                                        <th>Email</th>
                                        <th>Telefono</th>
                                        <th>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark fw-semibold admin-sort" data-sort-key="sesso" aria-label="Ordina per sesso">
                                                Sesso
                                                <span class="admin-sort-icons" aria-hidden="true">
                                                    <i class="fas fa-sort-up"></i>
                                                    <i class="fas fa-sort-down"></i>
                                                </span>
                                            </button>
                                        </th>
                                        <th>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark fw-semibold admin-sort" data-sort-key="datanascita" aria-label="Ordina per data di nascita">
                                                Data nascita
                                                <span class="admin-sort-icons" aria-hidden="true">
                                                    <i class="fas fa-sort-up"></i>
                                                    <i class="fas fa-sort-down"></i>
                                                </span>
                                            </button>
                                        </th>
                                        <th class="col-residenza">Residenza</th>
                                        <th>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark fw-semibold admin-sort" data-sort-key="ruolo" aria-label="Ordina per ruolo">
                                                Ruolo
                                                <span class="admin-sort-icons" aria-hidden="true">
                                                    <i class="fas fa-sort-up"></i>
                                                    <i class="fas fa-sort-down"></i>
                                                </span>
                                            </button>
                                        </th>
                                        <th>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark fw-semibold admin-sort" data-sort-key="stato" aria-label="Ordina per stato">
                                                Stato
                                                <span class="admin-sort-icons" aria-hidden="true">
                                                    <i class="fas fa-sort-up"></i>
                                                    <i class="fas fa-sort-down"></i>
                                                </span>
                                            </button>
                                        </th>
                                        <th>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark fw-semibold admin-sort" data-sort-key="news" aria-label="Ordina per newsletter">
                                                News
                                                <span class="admin-sort-icons" aria-hidden="true">
                                                    <i class="fas fa-sort-up"></i>
                                                    <i class="fas fa-sort-down"></i>
                                                </span>
                                            </button>
                                        </th>
                                        <th>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark fw-semibold admin-sort" data-sort-key="eventi" aria-label="Ordina per numero eventi">
                                                Eventi
                                                <span class="admin-sort-icons" aria-hidden="true">
                                                    <i class="fas fa-sort-up"></i>
                                                    <i class="fas fa-sort-down"></i>
                                                </span>
                                            </button>
                                        </th>
                                        <th>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark fw-semibold admin-sort" data-sort-key="iscr" aria-label="Ordina per iscrizione">
                                                Iscr.
                                                <span class="admin-sort-icons" aria-hidden="true">
                                                    <i class="fas fa-sort-up"></i>
                                                    <i class="fas fa-sort-down"></i>
                                                </span>
                                            </button>
                                        </th>
                                        <th class="col-ultacc">
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark fw-semibold admin-sort" data-sort-key="ultimo_accesso" aria-label="Ordina per ultimo accesso">
                                                Ult. acc.
                                                <span class="admin-sort-icons" aria-hidden="true">
                                                    <i class="fas fa-sort-up"></i>
                                                    <i class="fas fa-sort-down"></i>
                                                </span>
                                            </button>
                                        </th>
                                        <th>Azioni</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($users as $user)
                                        <tr
                                            data-user-nome="{{ strtolower(trim($user->nome ?? '')) }}"
                                            data-user-cognome="{{ strtolower(trim($user->cognome ?? '')) }}"
                                            data-user-nickname="{{ strtolower(trim($user->nickname ?? $user->username ?? '')) }}"
                                            data-user-sesso="{{ strtolower(trim($user->sesso ?? '')) }}"
                                            data-user-ruolo="{{ (int) ($user->ruolo ?? 2) }}"
                                            data-user-stato="{{ strtolower(trim($user->status ?? '')) }}"
                                            data-user-stato-rank="{{ $user->status === 'approved' ? 0 : ($user->status === 'pending' ? 1 : 2) }}"
                                            data-user-news-rank="{{ $user->invia ? 1 : 0 }}"
                                            data-user-eventi-count="{{ (int) ($user->events_count ?? 0) }}">
                                            <td class="text-muted d-none d-lg-table-cell">{{ $user->userID }}</td>
                                            <td class="col-foto">
                                                @if($user->photo_url)
                                                    <img src="{{ $user->photo_url }}" alt="{{ $user->nickname }}"
                                                         style="width:32px;height:32px;object-fit:cover;border-radius:50%;border:1px solid rgba(0,0,0,0.15);">
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            <td class="col-nome">{{ $user->nome }}</td>
                                            <td class="col-cognome">{{ $user->cognome }}</td>
                                            <td>
                                                <a href="{{ route('profile.show', $user) }}" target="_blank">
                                                    {{ $user->nickname }}
                                                </a>
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->telefono ?: '—' }}</td>
                                            <td>{{ $user->sesso ?: '—' }}</td>
                                            <td data-sort-datanascita="{{ $user->datanascita ? $user->datanascita->timestamp : 0 }}">
                                                {{ $user->datanascita ? $user->datanascita->format('d/m/Y') : '—' }}
                                            </td>
                                            <td class="col-residenza" title="{{ $user->residenza ?: '—' }}">{{ $user->residenza ?: '—' }}</td>
                                            <td>
                                                <form action="{{ route('admin.users.update-role', $user) }}" method="POST" class="d-flex align-items-center gap-1">
                                                    @csrf
                                                    <select name="ruolo" class="form-select form-select-sm w-auto">
                                                        <option value="2" {{ (int)$user->ruolo === 2 ? 'selected' : '' }}>Utente</option>
                                                        <option value="1" {{ (int)$user->ruolo === 1 ? 'selected' : '' }}>Organizzatore</option>
                                                        <option value="0" {{ (int)$user->ruolo === 0 ? 'selected' : '' }}>Amministratore</option>
                                                    </select>
                                                    <button type="submit" class="btn btn-outline-secondary btn-sm" title="Aggiorna ruolo">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                @if($user->status === 'pending')
                                                    <span class="badge bg-danger">Sospeso</span>
                                                @elseif($user->status === 'approved')
                                                    <span class="badge bg-success">Attivo</span>
                                                @else
                                                    <span class="badge bg-secondary">Bannato</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($user->invia)
                                                    <span class="badge bg-success">Sì</span>
                                                @else
                                                    <span class="badge bg-danger">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $user->events_count }} eventi</span>
                                            </td>
                                            <td data-sort-iscr="{{ $user->created_at ? $user->created_at->timestamp : 0 }}">
                                                <span class="admin-date-small">{{ $user->created_at ? $user->created_at->format('d/m/Y') : '—' }}</span>
                                            </td>
                                            <td data-sort-ultimo-accesso="{{ $user->ultimo_accesso ? $user->ultimo_accesso->timestamp : 0 }}">
                                                <span class="admin-date-small">{{ $user->ultimo_accesso ? $user->ultimo_accesso->format('d/m/Y') : '—' }}</span>
                                            </td>
                                            <td>
                                                {{-- Identificazione Attivo/Sospeso/Bannato (pulsanti) + azioni sulla stessa riga --}}
                                                <div class="d-inline-flex align-items-center flex-wrap gap-1 admin-users-azioni-inline">
                                                    @if($user->status === 'pending')
                                                        <button type="button" class="btn btn-danger btn-sm py-0 px-2" disabled title="Stato account">Sospeso</button>
                                                        @if(!$user->isAdmin())
                                                            <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="d-inline m-0">
                                                                @csrf
                                                                <button type="submit" class="btn btn-success btn-sm py-0" title="Approva e rendi attivo">
                                                                    <i class="fas fa-check me-1"></i> Attiva
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @elseif($user->status === 'approved')
                                                        <button type="button" class="btn btn-success btn-sm py-0 px-2" disabled title="Stato account">Attivo</button>
                                                    @else
                                                        <button type="button" class="btn btn-secondary btn-sm py-0 px-2" disabled title="Stato account">Bannato</button>
                                                    @endif

                                                    @if($user->status !== 'banned')
                                                        <form action="{{ route('admin.users.ban', $user) }}" method="POST" class="d-inline m-0">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm py-0" title="Banna">
                                                                <i class="fas fa-ban"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('admin.users.unban', $user) }}" method="POST" class="d-inline m-0">
                                                            @csrf
                                                            <button type="submit" class="btn btn-warning btn-sm py-0" title="Sbanna">
                                                                <i class="fas fa-unlock"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm py-0"
                                                                onclick="return confirm('Sei sicuro di voler eliminare questo utente?')"
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
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                                <h5>Nessun utente registrato</h5>
                                <p class="text-muted">Non ci sono utenti nel sistema.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Vista ?registrations=pending: titolo + sottotitolo marrone in box azzurro (come concordato) */
        .admin-users-pending-title-box {
            width: 100%;
            border: 2px solid #0dcaf0;
            border-radius: 12px;
            background: rgba(13, 202, 240, 0.1);
            padding: 10px 12px;
        }
        @media (max-width: 767.98px) {
            .admin-users-pending-title-box {
                padding: 8px 10px;
            }
        }
        .admin-users-pending-heading,
        .admin-users-pending-heading .admin-users-page-title-text,
        .admin-users-pending-heading .admin-users-page-title-icon,
        .admin-users-pending-subtitle {
            color: #8B4513;
        }

        .admin-users-azioni-inline {
            max-width: 100%;
        }
        .admin-users-azioni-inline .btn:disabled {
            opacity: 1;
        }

        .admin-users-page-title-icon {
            margin-right: 0.5rem;
        }
        @media (max-width: 767.98px) {
            .admin-users-page-title {
                display: inline-flex;
                flex-direction: row;
                align-items: center;
                gap: 0.35rem;
                margin-bottom: 0;
                font-size: calc(1.375rem + 0.6vw); /* più piccolo della display-4 */
                white-space: nowrap;
            }
            .admin-users-page-title-icon {
                margin-right: 0;
                font-size: 0.95em;
            }
            .admin-users-page-title-text {
                text-align: left;
            }
        }

        .admin-users-header-inline {
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .admin-users-header-inline::-webkit-scrollbar {
            height: 6px;
        }
        .admin-users-header-inline::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.25);
            border-radius: 999px;
        }

        .admin-user-stats-pills {
            line-height: 1;
            flex-wrap: nowrap;
            white-space: nowrap;
        }
        .admin-user-finder {
            width: 360px;
            flex: 0 0 auto;
        }
        .admin-user-finder .input-group-text,
        .admin-user-finder .form-control {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            border-color: rgba(255, 255, 255, 0.18);
        }
        .admin-user-finder .form-select {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            border-color: rgba(255, 255, 255, 0.18);
            max-width: 120px;
        }
        .admin-user-finder .form-select:focus {
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 0 .2rem rgba(255, 255, 255, 0.12);
        }
        .admin-user-finder .form-select option {
            color: #000;
        }
        .admin-user-finder .form-control::placeholder {
            color: rgba(255, 255, 255, 0.65);
        }
        .admin-user-finder .form-control:focus {
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 0 .2rem rgba(255, 255, 255, 0.12);
        }
        .admin-user-stats-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .admin-user-stats-pill i {
            font-size: 0.95em;
        }
        .admin-user-stats-pill-label {
            font-weight: 600;
        }
        .admin-user-stats-pill-value {
            font-weight: 900;
        }
        @media (max-width: 767.98px) {
            .admin-user-stats-pill {
                font-size: 0.8rem;
                padding: 0.18rem 0.5rem;
            }
            .admin-user-finder {
                width: 310px;
            }
            .admin-user-finder .form-select {
                max-width: 110px;
            }
        }

        .admin-users-table th, .admin-users-table td {
            white-space: nowrap;
            vertical-align: middle;
        }
        .admin-users-table th.col-residenza,
        .admin-users-table td.col-residenza {
            max-width: 140px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .admin-users-table th.col-foto,
        .admin-users-table td.col-foto {
            width: 44px;
            min-width: 44px;
            max-width: 44px;
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }
        .admin-users-table th.col-nome,
        .admin-users-table td.col-nome,
        .admin-users-table th.col-cognome,
        .admin-users-table td.col-cognome {
            width: 1%;
        }
        .admin-users-table th.col-ultacc {
            min-width: 92px;
        }
        .admin-sort-icons {
            display: inline-flex;
            flex-direction: column;
            line-height: 0.6;
            margin-left: 0.25rem;
            opacity: 0.7;
            vertical-align: middle;
        }
        .admin-sort-icons .fa-sort-up,
        .admin-sort-icons .fa-sort-down {
            font-size: 0.8em;
            height: 0.55em;
        }
        .admin-sort[data-dir="asc"] .fa-sort-up { opacity: 1; }
        .admin-sort[data-dir="asc"] .fa-sort-down { opacity: 0.25; }
        .admin-sort[data-dir="desc"] .fa-sort-up { opacity: 0.25; }
        .admin-sort[data-dir="desc"] .fa-sort-down { opacity: 1; }
        .admin-date-small {
            font-size: 0.82em;
        }
        @media (max-width: 767.98px) {
            .admin-users-table {
                font-size: 0.78rem;
            }
            .admin-users-table th,
            .admin-users-table td {
                padding: 0.25rem 0.35rem;
            }
        }
        .admin-users-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #fff;
        }
        .admin-users-table-wrapper {
            max-height: calc(100vh - 260px);
            overflow-y: auto;
        }
        .btn-group .btn {
            margin-right: 0.25rem;
        }
        .btn-group .btn:last-child {
            margin-right: 0;
        }
    </style>

    <script>
        (function () {
            var input = document.getElementById('userFinder');
            var clearBtn = document.getElementById('userFinderClear');
            var fieldSel = document.getElementById('userFinderField');
            if (!input) return;

            var rows = Array.prototype.slice.call(document.querySelectorAll('.admin-users-table tbody tr'));

            function normalize(v) {
                return (v || '')
                    .toString()
                    .trim()
                    .toLowerCase();
            }

            function applyFilter() {
                var q = normalize(input.value);
                if (!q) {
                    rows.forEach(function (tr) { tr.style.display = ''; });
                    return;
                }
                var field = fieldSel ? fieldSel.value : 'nome';

                // Permetti di incollare/ scrivere anche "Nome Cognome" quando si filtra per Nome o Cognome:
                // - Nome: prendiamo la prima parola
                // - Cognome: prendiamo l'ultima parola
                // Per Nickname: se scrive "@nick" prendiamo nick senza "@"
                var effectiveQ = q;
                if (field === 'nickname') {
                    var m = q.match(/@([a-z0-9._-]+)/i);
                    if (m && m[1]) effectiveQ = m[1].toLowerCase();
                } else if (q.indexOf(' ') !== -1) {
                    var parts = q.split(/\s+/).filter(Boolean);
                    if (parts.length) {
                        effectiveQ = (field === 'nome') ? parts[0] : parts[parts.length - 1];
                    }
                }

                function matchesField(tr, f) {
                    var hay = normalize(tr.getAttribute('data-user-' + f));
                    return hay.indexOf(effectiveQ) !== -1;
                }

                // 1) Prova sul campo selezionato
                var anyMatch = false;
                rows.forEach(function (tr) {
                    var ok = matchesField(tr, field);
                    tr.style.display = ok ? '' : 'none';
                    if (ok) anyMatch = true;
                });

                // 2) Se non trova nulla (es. stai cercando un nickname ma hai selezionato "Nome"),
                // fai fallback automatico sugli altri campi per non "sembrare rotto".
                if (!anyMatch) {
                    rows.forEach(function (tr) {
                        var ok =
                            matchesField(tr, 'nome') ||
                            matchesField(tr, 'cognome') ||
                            matchesField(tr, 'nickname');
                        tr.style.display = ok ? '' : 'none';
                    });
                }
            }

            input.addEventListener('input', applyFilter);
            input.addEventListener('change', applyFilter);
            if (fieldSel) {
                fieldSel.addEventListener('change', function () {
                    applyFilter();
                    input.focus();
                });
            }

            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    input.value = '';
                    applyFilter();
                    input.focus();
                });
            }

            function defaultSortDirForKey(sortKey) {
                return (sortKey === 'ultimo_accesso' || sortKey === 'iscr' || sortKey === 'datanascita' || sortKey === 'eventi')
                    ? 'desc'
                    : 'asc';
            }

            function sortAllRowsByColumn(sortKey, sortDir) {
                var tbody = document.querySelector('.admin-users-table tbody');
                if (!tbody) return;

                var all = rows.slice();

                function getStr(tr, attrName) {
                    return normalize(tr.getAttribute(attrName));
                }
                function getNumFromCell(tr, cellSelector, attrName) {
                    var cell = tr.querySelector(cellSelector);
                    return parseInt((cell && cell.getAttribute(attrName)) || '0', 10);
                }
                function getNumAttr(tr, attrName) {
                    return parseInt(tr.getAttribute(attrName) || '0', 10);
                }

                function cmpForKey(a, b, key) {
                    if (key === 'ultimo_accesso') {
                        var av = getNumFromCell(a, '[data-sort-ultimo-accesso]', 'data-sort-ultimo-accesso');
                        var bv = getNumFromCell(b, '[data-sort-ultimo-accesso]', 'data-sort-ultimo-accesso');
                        return av - bv;
                    }
                    if (key === 'iscr') {
                        var ai = getNumFromCell(a, '[data-sort-iscr]', 'data-sort-iscr');
                        var bi = getNumFromCell(b, '[data-sort-iscr]', 'data-sort-iscr');
                        return ai - bi;
                    }
                    if (key === 'nome') {
                        return getStr(a, 'data-user-nome').localeCompare(getStr(b, 'data-user-nome'), 'it', { sensitivity: 'base' });
                    }
                    if (key === 'cognome') {
                        return getStr(a, 'data-user-cognome').localeCompare(getStr(b, 'data-user-cognome'), 'it', { sensitivity: 'base' });
                    }
                    if (key === 'nickname') {
                        return getStr(a, 'data-user-nickname').localeCompare(getStr(b, 'data-user-nickname'), 'it', { sensitivity: 'base' });
                    }
                    if (key === 'sesso') {
                        return getStr(a, 'data-user-sesso').localeCompare(getStr(b, 'data-user-sesso'), 'it', { sensitivity: 'base' });
                    }
                    if (key === 'datanascita') {
                        var ad = getNumFromCell(a, '[data-sort-datanascita]', 'data-sort-datanascita');
                        var bd = getNumFromCell(b, '[data-sort-datanascita]', 'data-sort-datanascita');
                        return ad - bd;
                    }
                    if (key === 'ruolo') {
                        return getNumAttr(a, 'data-user-ruolo') - getNumAttr(b, 'data-user-ruolo');
                    }
                    if (key === 'stato') {
                        return getNumAttr(a, 'data-user-stato-rank') - getNumAttr(b, 'data-user-stato-rank');
                    }
                    if (key === 'news') {
                        return getNumAttr(a, 'data-user-news-rank') - getNumAttr(b, 'data-user-news-rank');
                    }
                    if (key === 'eventi') {
                        return getNumAttr(a, 'data-user-eventi-count') - getNumAttr(b, 'data-user-eventi-count');
                    }
                    return 0;
                }

                all.sort(function (a, b) {
                    var cmp = cmpForKey(a, b, sortKey);
                    if (sortDir !== 'asc') {
                        cmp = -cmp;
                    }
                    return cmp;
                });

                all.forEach(function (tr) {
                    tbody.appendChild(tr);
                });
            }

            var sortButtons = Array.prototype.slice.call(document.querySelectorAll('.admin-sort'));

            sortButtons.forEach(function (btn) {
                var key = btn.getAttribute('data-sort-key');
                btn.setAttribute('data-dir', defaultSortDirForKey(key));

                btn.addEventListener('click', function () {
                    var dir = btn.getAttribute('data-dir') || 'asc';
                    btn.setAttribute('data-dir', dir === 'asc' ? 'desc' : 'asc');

                    sortButtons.forEach(function (b) {
                        if (b === btn) {
                            return;
                        }
                        var k = b.getAttribute('data-sort-key');
                        b.setAttribute('data-dir', defaultSortDirForKey(k));
                        b.removeAttribute('data-active');
                    });
                    btn.setAttribute('data-active', '1');

                    sortAllRowsByColumn(key, dir);
                });
            });
        })();
    </script>
@endsection
