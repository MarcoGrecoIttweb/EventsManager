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
                                    <span class="admin-users-page-title-text">Iscrizioni in attesa di approvazione</span>
                                </span>
                            </h1>
                        </div>
                    </div>
                @else
                    <div class="d-flex justify-content-start align-items-start mb-4 gap-3 flex-wrap">
                        <h1 class="display-5 admin-users-page-title">
                            <i class="fas fa-users-cog admin-users-page-title-icon"></i>
                            <span class="admin-users-page-title-text">Gestione Utenti</span>
                        </h1>
                    </div>
                @endif

                <!-- Tabella Utenti -->
                <div class="card">
                    <div class="card-header admin-users-list-header">
                        <div class="d-flex align-items-center gap-2 admin-users-header-inline">
                            <h5 class="mb-0">Lista Utenti</h5>
                            <div class="admin-users-actions-box">
                                <div class="d-flex flex-nowrap gap-2 admin-users-actions-box__row">
                                    <a href="{{ route('admin.users.logins') }}" class="btn btn-primary btn-sm btn-border-brown">
                                        <i class="fas fa-sign-in-alt me-1"></i> Ingressi giornalieri
                                    </a>
                                    <a href="{{ route('admin.users.index', ['registrations' => 'pending']) }}" class="btn btn-warning btn-sm btn-border-brown text-dark">
                                        <i class="fas fa-list me-1"></i> Vedi solo in attesa
                                    </a>
                                    <a href="{{ route('admin.users.index', ['status' => 'approved']) }}" class="btn btn-success btn-sm btn-border-brown text-white">
                                        <i class="fas fa-check-circle me-1"></i> Visualizza Attivi
                                    </a>
                                    <a href="{{ route('admin.users.index', ['status' => 'suspended']) }}" class="btn btn-danger btn-sm btn-border-brown text-white admin-users-actions-btn--sospesi-width">
                                        <i class="fas fa-pause-circle me-1"></i> Visualizza sospesi
                                    </a>
                                    <a href="{{ route('admin.users.index') }}" class="btn btn-success btn-sm btn-border-brown text-white admin-users-actions-btn--sospesi-width">Vista completa</a>
                                    <a href="{{ route('home') }}" class="btn btn-secondary btn-sm btn-border-brown admin-users-actions-btn--sospesi-width">
                                        <i class="fas fa-home"></i> Home
                                    </a>
                                </div>
                            </div>
                            <div class="input-group input-group-sm admin-user-finder">
                                <label class="visually-hidden" for="userFinderField">Campo</label>
                                <div class="admin-user-finder-select-wrap">
                                    <select id="userFinderField" class="form-select">
                                        <option value="nome" selected>Nome</option>
                                        <option value="cognome">Cognome</option>
                                        <option value="nickname">Nickname</option>
                                    </select>
                                    <span class="admin-user-finder-select-icon" aria-hidden="true">
                                        <i class="fas fa-chevron-down"></i>
                                    </span>
                                </div>
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input id="userFinder"
                                       type="search"
                                       class="form-control"
                                       placeholder="Trova utente…"
                                       autocomplete="off"
                                       list="adminUsersFinderSuggestions"
                                       aria-autocomplete="list"
                                       aria-label="Trova utente">
                                <datalist id="adminUsersFinderSuggestions"></datalist>
                                <button id="userFinderClear" type="button" class="btn btn-outline-dark" title="Pulisci">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="admin-user-stats-grid">
                                {{-- Riga 1: Attivi + In attesa --}}
                                <div class="admin-user-stats-grid__item">
                                    @if(request('registrations') === 'pending')
                                        <a href="{{ route('admin.users.index') }}" class="admin-user-stats-pill bg-success text-white text-decoration-none" title="Vai alla lista completa utenti">
                                            <i class="fas fa-check-circle"></i>
                                            <span class="admin-user-stats-pill-label">Attivi</span>
                                            <span class="admin-user-stats-pill-value">{{ $approvedCount }}</span>
                                        </a>
                                    @else
                                        <span class="admin-user-stats-pill bg-success text-white">
                                            <i class="fas fa-check-circle"></i>
                                            <span class="admin-user-stats-pill-label">Attivi</span>
                                            <span class="admin-user-stats-pill-value">{{ $approvedCount }}</span>
                                        </span>
                                    @endif
                                </div>
                                <div class="admin-user-stats-grid__item">
                                    <span class="admin-user-stats-pill bg-warning text-dark">
                                        <i class="fas fa-user-clock"></i>
                                        <span class="admin-user-stats-pill-label">In attesa</span>
                                        <span class="admin-user-stats-pill-value">{{ $awaitingCount }}</span>
                                    </span>
                                </div>

                                {{-- Riga 2: Sospesi + Bannati --}}
                                <div class="admin-user-stats-grid__item">
                                    <span class="admin-user-stats-pill bg-danger text-white">
                                        <i class="fas fa-pause-circle"></i>
                                        <span class="admin-user-stats-pill-label">Sospesi</span>
                                        <span class="admin-user-stats-pill-value">{{ $suspendedCount }}</span>
                                    </span>
                                </div>
                                <div class="admin-user-stats-grid__item">
                                    <span class="admin-user-stats-pill bg-danger text-white">
                                        <i class="fas fa-ban"></i>
                                        <span class="admin-user-stats-pill-label">Bannati</span>
                                        <span class="admin-user-stats-pill-value">{{ $bannedCount }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @php
                            $adminUsersListUrl = preg_replace('/#.*$/', '', request()->fullUrl());
                            $usersScrollRestore = request()->has('_rs')
                                ? [
                                    'scroll_top' => (int) request()->query('_rs', 0),
                                    'window_scroll' => (int) request()->query('_rw', 0),
                                    'user_id' => (int) request()->query('_ru', 0),
                                ]
                                : session('admin_users_scroll_restore');
                        @endphp
                        @if($users->count() > 0)
                            {{-- Tabella completa: visibile anche su smartphone (con scroll orizzontale) --}}
                            <div id="adminUsersTableScroller" class="table-responsive admin-users-table-wrapper"
                                @if(!empty($usersScrollRestore))
                                    data-restore-scroll="{{ (int) ($usersScrollRestore['scroll_top'] ?? 0) }}"
                                    data-restore-window="{{ (int) ($usersScrollRestore['window_scroll'] ?? 0) }}"
                                    data-restore-user="{{ (int) ($usersScrollRestore['user_id'] ?? 0) }}"
                                @endif>
                                @if(!empty($usersScrollRestore))
                                    <script>
                                        (function () {
                                            var top = {{ (int) ($usersScrollRestore['scroll_top'] ?? 0) }};
                                            var win = {{ (int) ($usersScrollRestore['window_scroll'] ?? 0) }};
                                            function applyEarly() {
                                                var el = document.getElementById('adminUsersTableScroller');
                                                if (el) el.scrollTop = top;
                                                if (win > 0) window.scrollTo(0, win);
                                            }
                                            applyEarly();
                                            document.addEventListener('DOMContentLoaded', applyEarly);
                                        })();
                                    </script>
                                @endif
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
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark admin-sort" data-sort-key="sesso" aria-label="Ordina per sesso">
                                                Sesso
                                                <span class="admin-sort-icons" aria-hidden="true">
                                                    <i class="fas fa-sort-up"></i>
                                                    <i class="fas fa-sort-down"></i>
                                                </span>
                                            </button>
                                        </th>
                                        <th>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark admin-sort" data-sort-key="datanascita" aria-label="Ordina per data di nascita">
                                                Data nascita
                                                <span class="admin-sort-icons" aria-hidden="true">
                                                    <i class="fas fa-sort-up"></i>
                                                    <i class="fas fa-sort-down"></i>
                                                </span>
                                            </button>
                                        </th>
                                        <th class="col-residenza">Residenza</th>
                                        <th>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark admin-sort" data-sort-key="ruolo" aria-label="Ordina per ruolo">
                                                Ruolo
                                                <span class="admin-sort-icons" aria-hidden="true">
                                                    <i class="fas fa-sort-up"></i>
                                                    <i class="fas fa-sort-down"></i>
                                                </span>
                                            </button>
                                        </th>
                                        <th>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark admin-sort" data-sort-key="eventi" aria-label="Ordina per numero eventi">
                                                Eventi
                                                <span class="admin-sort-icons" aria-hidden="true">
                                                    <i class="fas fa-sort-up"></i>
                                                    <i class="fas fa-sort-down"></i>
                                                </span>
                                            </button>
                                        </th>
                                        <th>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark admin-sort" data-sort-key="iscr" aria-label="Ordina per iscrizione">
                                                Iscr.
                                                <span class="admin-sort-icons" aria-hidden="true">
                                                    <i class="fas fa-sort-up"></i>
                                                    <i class="fas fa-sort-down"></i>
                                                </span>
                                            </button>
                                        </th>
                                        <th class="col-ultacc">
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark admin-sort" data-sort-key="ultimo_accesso" aria-label="Ordina per ultimo accesso">
                                                Ult. acc.
                                                <span class="admin-sort-icons" aria-hidden="true">
                                                    <i class="fas fa-sort-up"></i>
                                                    <i class="fas fa-sort-down"></i>
                                                </span>
                                            </button>
                                        </th>
                                        <th class="col-giorni">
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark admin-sort" data-sort-key="giorni" aria-label="Ordina per giorni">
                                                Giorni
                                                <span class="admin-sort-icons" aria-hidden="true">
                                                    <i class="fas fa-sort-up"></i>
                                                    <i class="fas fa-sort-down"></i>
                                                </span>
                                            </button>
                                        </th>
                                        <th>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark admin-sort" data-sort-key="news" aria-label="Ordina per newsletter">
                                                News
                                                <span class="admin-sort-icons" aria-hidden="true">
                                                    <i class="fas fa-sort-up"></i>
                                                    <i class="fas fa-sort-down"></i>
                                                </span>
                                            </button>
                                        </th>
                                        <th>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-dark admin-sort" data-sort-key="stato" aria-label="Ordina per stato">
                                                Stato
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
                                        @php
                                            $st = $user->status;
                                            $rowClass = match ($st) {
                                                'approved' => 'admin-user-row admin-user-row--approved',
                                                'awaiting' => 'admin-user-row admin-user-row--awaiting',
                                                'suspended' => 'admin-user-row admin-user-row--suspended',
                                                default => 'admin-user-row admin-user-row--banned',
                                            };
                                            $statoRank = match ($st) {
                                                'approved' => 0,
                                                'awaiting' => 1,
                                                'suspended' => 2,
                                                default => 3,
                                            };
                                        @endphp
                                        @php
                                            $usersListReturn = route('admin.users.index', request()->only(['status', 'registrations']));
                                        @endphp
                                        <tr id="admin-user-{{ $user->userID }}" class="{{ $rowClass }}"
                                            data-user-nome="{{ strtolower(trim($user->nome ?? '')) }}"
                                            data-user-cognome="{{ strtolower(trim($user->cognome ?? '')) }}"
                                            data-user-nickname="{{ strtolower(trim($user->nickname ?? $user->username ?? '')) }}"
                                            data-user-sesso="{{ strtolower(trim($user->sesso ?? '')) }}"
                                            data-user-ruolo="{{ (int) ($user->ruolo ?? 2) }}"
                                            data-user-stato="{{ strtolower(trim($st ?? '')) }}"
                                            data-user-stato-rank="{{ $statoRank }}"
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
                                                <a href="{{ route('profile.show', ['user' => $user, 'return' => $usersListReturn]) }}">
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
                                                    @include('admin.users._list_scroll_fields')
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
                                                <span class="badge bg-info">{{ $user->events_count }} eventi</span>
                                            </td>
                                            <td data-sort-iscr="{{ $user->created_at ? $user->created_at->timestamp : 0 }}">
                                                <span class="admin-date-small">{{ $user->created_at ? $user->created_at->format('d/m/Y') : '—' }}</span>
                                            </td>
                                            <td data-sort-ultimo-accesso="{{ $user->ultimo_accesso ? $user->ultimo_accesso->timestamp : 0 }}">
                                                <span class="admin-date-small">{{ $user->ultimo_accesso ? $user->ultimo_accesso->format('d/m/Y') : '—' }}</span>
                                            </td>
                                            <td data-sort-giorni="{{ $user->ultimo_accesso ? (int) \Carbon\Carbon::now()->diffInDays($user->ultimo_accesso) : 0 }}">
                                                @if($user->ultimo_accesso)
                                                    <span class="badge bg-secondary">{{ (int) \Carbon\Carbon::now()->diffInDays($user->ultimo_accesso) }} gg</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $newsletterDisabled = $user->isAdmin();
                                                    $newsletterId = 'news_toggle_' . $user->userID;
                                                @endphp
                                                <div class="d-inline-flex align-items-center gap-2">
                                                    <form action="{{ route('admin.users.update-newsletter', $user) }}" method="POST" class="d-inline m-0 admin-user-news-toggle-form">
                                                        @csrf
                                                        @include('admin.users._list_scroll_fields')
                                                        <input type="hidden" name="invia" value="{{ $user->invia ? 0 : 1 }}">
                                                        <button type="submit"
                                                                class="btn btn-sm {{ $user->invia ? 'btn-success' : 'btn-outline-danger' }}"
                                                                @if($newsletterDisabled) disabled aria-disabled="true" @endif
                                                                title="{{ $user->invia ? 'Disattiva newsletter' : 'Attiva newsletter' }}">
                                                            <i class="fas {{ $user->invia ? 'fa-envelope-open' : 'fa-envelope' }}"></i>
                                                            {{ $user->invia ? 'Sì' : 'No' }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                            <td>
                                                @if($user->status === 'awaiting')
                                                    <div class="admin-user-state-checkrow">
                                                        <span class="badge bg-warning text-dark admin-user-state-pill">In attesa</span>
                                                        @if(!$user->isAdmin())
                                                            <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="d-inline m-0 admin-user-state-toggle-form"
                                                                  data-url-approve="{{ route('admin.users.approve', $user) }}"
                                                                  data-url-suspend="{{ route('admin.users.suspend', $user) }}">
                                                                @csrf
                                                                @include('admin.users._list_scroll_fields')
                                                                <input type="checkbox"
                                                                       class="form-check-input admin-user-state-check"
                                                                       aria-label="Attiva utente"
                                                                       title="Attiva utente">
                                                            </form>
                                                        @else
                                                            <input type="checkbox" class="form-check-input admin-user-state-check" disabled aria-label="Utente admin">
                                                        @endif
                                                    </div>
                                                @elseif($user->status === 'suspended')
                                                    <div class="admin-user-state-checkrow">
                                                        <span class="badge bg-danger admin-user-state-pill">Sospeso</span>
                                                        @if(!$user->isAdmin())
                                                            <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="d-inline m-0 admin-user-state-toggle-form"
                                                                  data-url-approve="{{ route('admin.users.approve', $user) }}"
                                                                  data-url-suspend="{{ route('admin.users.suspend', $user) }}">
                                                                @csrf
                                                                @include('admin.users._list_scroll_fields')
                                                                <input type="checkbox"
                                                                       class="form-check-input admin-user-state-check"
                                                                       aria-label="Riattiva utente"
                                                                       title="Riattiva utente">
                                                            </form>
                                                        @else
                                                            <input type="checkbox" class="form-check-input admin-user-state-check" disabled aria-label="Utente admin">
                                                        @endif
                                                    </div>
                                                @elseif($user->status === 'approved')
                                                    <div class="admin-user-state-checkrow">
                                                        <span class="badge bg-success admin-user-state-pill">Attivo</span>
                                                        @if(!$user->isAdmin())
                                                            <form action="{{ route('admin.users.suspend', $user) }}" method="POST" class="d-inline m-0 admin-user-state-toggle-form"
                                                                  data-url-approve="{{ route('admin.users.approve', $user) }}"
                                                                  data-url-suspend="{{ route('admin.users.suspend', $user) }}">
                                                                @csrf
                                                                @include('admin.users._list_scroll_fields')
                                                                <input type="checkbox"
                                                                       class="form-check-input admin-user-state-check"
                                                                       checked
                                                                       aria-label="Disattiva (sospendi) utente"
                                                                       title="Disattiva (sospendi) utente">
                                                            </form>
                                                        @else
                                                            <input type="checkbox" class="form-check-input admin-user-state-check" checked disabled aria-label="Utente admin">
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="badge bg-secondary admin-user-state-pill">Bannato</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{-- Identificazione Attivo/Sospeso/Bannato (pulsanti) + azioni sulla stessa riga --}}
                                                <div class="d-inline-flex align-items-center gap-0 admin-users-azioni-inline">
                                                    @if(auth()->check() && auth()->user()->isAdmin() && !session()->has('impersonator_id') && !$user->isAdmin() && (int) $user->userID !== (int) auth()->id())
                                                        <form action="{{ route('admin.users.impersonate', $user) }}" method="POST" class="d-inline m-0">
                                                            @csrf
                                                            @include('admin.users._list_scroll_fields')
                                                            <button type="submit"
                                                                    class="btn btn-outline-primary btn-sm py-0"
                                                                    onclick="return confirm('Impersonare {{ $user->nickname }}?')"
                                                                    title="Impersona">
                                                                <i class="fas fa-user-secret"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if($user->status !== 'banned')
                                                        <form action="{{ route('admin.users.ban', $user) }}" method="POST" class="d-inline m-0">
                                                            @csrf
                                                            @include('admin.users._list_scroll_fields')
                                                            <button type="submit" class="btn btn-danger btn-sm py-0" title="Banna">
                                                                <i class="fas fa-ban"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('admin.users.unban', $user) }}" method="POST" class="d-inline m-0">
                                                            @csrf
                                                            @include('admin.users._list_scroll_fields')
                                                            <button type="submit" class="btn btn-warning btn-sm py-0" title="Sbanna">
                                                                <i class="fas fa-unlock"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline m-0">
                                                        @csrf
                                                        @include('admin.users._list_scroll_fields')
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
                                @if(request('registrations') === 'pending')
                                    <h5>Nessuna iscrizione in attesa</h5>
                                    <p class="text-muted">Al momento non ci sono nuovi profili da approvare.</p>
                                @else
                                    <h5>Nessun utente registrato</h5>
                                    <p class="text-muted">Non ci sono utenti nel sistema.</p>
                                @endif
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
            flex-wrap: nowrap;
        }
        .admin-users-azioni-inline .btn:disabled {
            opacity: 1;
        }

        /* Box azioni (ocra + bordo marrone): dopo titolo, prima di Trova utente e riquadri statistiche */
        .admin-users-actions-box {
            border: 2px solid #8B4513;
            background: rgba(184, 134, 11, 0.22); /* giallo ocra */
            border-radius: 12px;
            padding: 10px 12px;
            flex: 0 0 auto;
            margin-top: 0.25rem;
        }
        .admin-users-actions-box__row {
            align-items: center;
        }
        .admin-users-actions-box .btn {
            flex: 0 0 auto;
            justify-content: center;
            text-align: center;
            white-space: nowrap;
            line-height: 1.2;
            padding-left: 0.35rem;
            padding-right: 0.35rem;
        }
        /* Larghezza unica: Visualizza sospesi, Vista completa, Home */
        .admin-users-actions-box .admin-users-actions-btn--sospesi-width {
            flex: 0 0 13.35rem;
            width: 13.35rem;
            max-width: 13.35rem;
            box-sizing: border-box;
        }
        .btn.btn-border-brown {
            border: 2px solid #8B4513 !important;
        }

        /* Stato utente (Sospeso / Attivo / Bannato) + pulsante Attiva: stessa misura */
        .admin-user-state-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            font-size: 0.85rem;
            line-height: 1.2;
            padding: 0.25rem 0.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
        }
        .admin-user-state-checkrow {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .admin-user-state-check {
            margin: 0;
            width: 1.05rem;
            height: 1.05rem;
            border: 2px solid rgba(0, 0, 0, 0.25);
            cursor: pointer;
        }
        .admin-user-state-toggle-form {
            display: inline-flex;
            align-items: center;
            margin: 0;
        }
        .admin-user-state-check:disabled {
            cursor: default;
        }

        /* Tabella compatta: spazi minimi tra i record */
        .admin-users-table {
            border-collapse: separate !important;
            border-spacing: 0;
        }
        .admin-users-table tbody tr > td {
            /* crea stacco tra una riga e l'altra */
            border-top: 1px solid rgba(0, 0, 0, 0.06);
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }
        .admin-users-table tbody tr > td:first-child {
            border-left: 1px solid rgba(0, 0, 0, 0.06);
            border-top-left-radius: 0.5rem;
            border-bottom-left-radius: 0.5rem;
        }
        .admin-users-table tbody tr > td:last-child {
            border-right: 1px solid rgba(0, 0, 0, 0.06);
            border-top-right-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
        }

        /* Colore riga per stato */
        .admin-users-table tbody tr.admin-user-row--approved > td {
            background: rgba(13, 202, 240, 0.14) !important; /* azzurro */
        }
        .admin-users-table tbody tr.admin-user-row--awaiting > td {
            background: rgba(255, 193, 7, 0.22) !important; /* giallo */
        }
        .admin-users-table tbody tr.admin-user-row--suspended > td {
            background: rgba(220, 53, 69, 0.12) !important; /* rosso */
        }
        .admin-users-table tbody tr.admin-user-row--banned > td {
            background: rgba(214, 51, 132, 0.10) !important; /* rosa */
        }

        /* News: badge stessa larghezza del "Sì" */
        .admin-user-news-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.2rem;
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
        .admin-user-stats-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, max-content));
            gap: 0.35rem 0.5rem;
            align-items: center;
            line-height: 1;
            white-space: nowrap;
        }
        .admin-user-stats-grid__item {
            display: inline-flex;
            align-items: center;
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
        /* Barra nera: box "Nome" + ricerca con bordo giallo e sfondo grigio, testo nero */
        .admin-users-list-header .admin-user-finder .input-group-text,
        .admin-users-list-header .admin-user-finder .form-control,
        .admin-users-list-header .admin-user-finder .form-select {
            background: #e9ecef !important;
            color: #000 !important;
            border: 2px solid #ffc107 !important;
        }
        .admin-users-list-header .admin-user-finder .form-control::placeholder {
            color: rgba(0, 0, 0, 0.55) !important;
        }
        .admin-users-list-header .admin-user-finder .form-select:focus,
        .admin-users-list-header .admin-user-finder .form-control:focus {
            border-color: #ffc107 !important;
            box-shadow: 0 0 0 .2rem rgba(255, 193, 7, 0.25) !important;
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
            padding: 0.15rem 0.3rem;
        }
        .admin-users-table th.col-residenza,
        .admin-users-table td.col-residenza {
            max-width: 90px;
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
        .admin-users-table th.col-giorni {
            min-width: 70px;
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
                padding: 0.1rem 0.2rem;
            }
        }
        .admin-users-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #fff;
        }
        /* Barra (header tabella): sfondo verde, titolo nero */
        .admin-users-list-header {
            background: #A1F198 !important;
            color: #000 !important;
        }
        .admin-users-list-header h5 {
            color: #000 !important;
        }

        /* Intestazioni tabella: bordo grigio chiaro e testo non bold */
        .admin-users-table thead th {
            border: 2px solid #dee2e6;
        }
        .admin-users-table thead th .admin-sort {
            font-weight: 400 !important;
        }

        /* Select "Campo": icona che indica sottomenù */
        .admin-user-finder-select-wrap {
            position: relative;
            display: inline-flex;
            align-items: stretch;
        }
        .admin-user-finder-select-wrap .form-select {
            padding-right: 2rem;
        }
        .admin-user-finder-select-icon {
            position: absolute;
            right: 0.65rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            font-size: 0.85rem;
            color: rgba(0, 0, 0, 0.65);
        }
        .admin-users-table-wrapper {
            max-height: calc(100vh - 260px);
            overflow-y: auto;
        }
        tr[id^="admin-user-"] {
            scroll-margin-top: 0.75rem;
            scroll-margin-bottom: 0.75rem;
        }
        .admin-users-table tbody tr.admin-user-row--highlight > td {
            box-shadow: inset 0 0 0 2px #ffc107;
            transition: box-shadow 0.2s ease;
        }
        .btn-group .btn {
            margin-right: 0.25rem;
        }
        .btn-group .btn:last-child {
            margin-right: 0;
        }
    </style>

    @php
        $adminUsersFinderSuggestionsUrl = rtrim(request()->root(), '/') . route('admin.users.finder-suggestions', [], false);
        $adminUsersListUrl = $adminUsersListUrl ?? request()->fullUrl();
    @endphp
    <script>
        (function () {
            if ('scrollRestoration' in history) {
                history.scrollRestoration = 'manual';
            }

            var adminUsersListUrl = @json($adminUsersListUrl);

            function getUsersTableScroller() {
                return document.getElementById('adminUsersTableScroller')
                    || document.querySelector('.admin-users-table-wrapper');
            }

            function fillUsersListScrollFields(form) {
                if (!form) return 0;
                var scroller = getUsersTableScroller();
                var scrollInput = form.querySelector('input[name="_list_scroll"]');
                var winInput = form.querySelector('input[name="_list_win_scroll"]');
                var returnInput = form.querySelector('input[name="_list_return"]');
                var top = scroller ? scroller.scrollTop : 0;
                var winY = window.scrollY || window.pageYOffset || 0;
                if (scrollInput) scrollInput.value = String(top);
                if (winInput) winInput.value = String(winY);
                if (returnInput) returnInput.value = adminUsersListUrl;
                return top;
            }

            function ensureUsersListScrollFields(form) {
                if (!form) return;
                if (!form.querySelector('input[name="_list_scroll"]')) {
                    var scrollInput = document.createElement('input');
                    scrollInput.type = 'hidden';
                    scrollInput.name = '_list_scroll';
                    scrollInput.value = '';
                    form.appendChild(scrollInput);
                }
                if (!form.querySelector('input[name="_list_win_scroll"]')) {
                    var winInput = document.createElement('input');
                    winInput.type = 'hidden';
                    winInput.name = '_list_win_scroll';
                    winInput.value = '';
                    form.appendChild(winInput);
                }
                if (!form.querySelector('input[name="_list_return"]')) {
                    var returnInput = document.createElement('input');
                    returnInput.type = 'hidden';
                    returnInput.name = '_list_return';
                    returnInput.value = adminUsersListUrl;
                    form.appendChild(returnInput);
                }
            }

            function saveUsersListPosition(userId, scrollTop) {
                if (!userId) return;
                var scroller = getUsersTableScroller();
                var top = typeof scrollTop === 'number' ? scrollTop : (scroller ? scroller.scrollTop : 0);
                try {
                    sessionStorage.setItem('adminUsersListRestore', JSON.stringify({
                        userId: String(userId),
                        scrollTop: top,
                        windowScrollY: window.scrollY || window.pageYOffset || 0
                    }));
                } catch (e) { /* ignore */ }
            }

            function scrollRowIntoScroller(row, scroller) {
                if (!row || !scroller) return;
                var rowRect = row.getBoundingClientRect();
                var scrollerRect = scroller.getBoundingClientRect();
                var delta = rowRect.top - scrollerRect.top - (scroller.clientHeight / 2) + (row.offsetHeight / 2);
                scroller.scrollTop += delta;
            }

            function cleanUsersListRestoreQuery() {
                try {
                    var params = new URLSearchParams(window.location.search);
                    if (!params.has('_rs')) return;
                    params.delete('_rs');
                    params.delete('_rw');
                    params.delete('_ru');
                    var qs = params.toString();
                    var clean = window.location.pathname + (qs ? '?' + qs : '');
                    history.replaceState(null, '', clean);
                } catch (e) { /* ignore */ }
            }

            function restoreUsersListPosition() {
                var scroller = getUsersTableScroller();
                if (!scroller) return;

                var userId = null;
                var scrollTop = null;
                var windowScrollY = null;

                try {
                    var params = new URLSearchParams(window.location.search);
                    if (params.has('_rs')) {
                        scrollTop = parseInt(params.get('_rs') || '0', 10);
                        windowScrollY = parseInt(params.get('_rw') || '0', 10);
                        userId = params.get('_ru') || null;
                    }
                } catch (e) { /* ignore */ }

                if (scroller.hasAttribute('data-restore-scroll')) {
                    scrollTop = parseInt(scroller.getAttribute('data-restore-scroll') || '0', 10);
                    windowScrollY = parseInt(scroller.getAttribute('data-restore-window') || '0', 10);
                    userId = userId || String(scroller.getAttribute('data-restore-user') || '');
                    scroller.removeAttribute('data-restore-scroll');
                    scroller.removeAttribute('data-restore-window');
                    scroller.removeAttribute('data-restore-user');
                }

                try {
                    var raw = sessionStorage.getItem('adminUsersListRestore');
                    if (raw) {
                        var data = JSON.parse(raw);
                        sessionStorage.removeItem('adminUsersListRestore');
                        if (!userId && data.userId) userId = data.userId;
                        if (scrollTop === null && typeof data.scrollTop === 'number') scrollTop = data.scrollTop;
                        if (windowScrollY === null && typeof data.windowScrollY === 'number') windowScrollY = data.windowScrollY;
                    }
                } catch (e) { /* ignore */ }

                if (scrollTop === null && windowScrollY === null && !userId) return;

                var cleaned = false;
                function applyRestore() {
                    if (typeof windowScrollY === 'number' && !isNaN(windowScrollY)) {
                        window.scrollTo(0, windowScrollY);
                    }
                    if (scrollTop !== null && !isNaN(scrollTop) && scroller) {
                        scroller.scrollTop = scrollTop;
                    }
                    if (userId) {
                        var row = document.getElementById('admin-user-' + userId);
                        if (row) {
                            if ((scrollTop === null || isNaN(scrollTop)) && scroller) {
                                scrollRowIntoScroller(row, scroller);
                            }
                            row.classList.add('admin-user-row--highlight');
                            window.setTimeout(function () {
                                row.classList.remove('admin-user-row--highlight');
                            }, 2500);
                        }
                    }
                    if (!cleaned) {
                        cleaned = true;
                        cleanUsersListRestoreQuery();
                    }
                }

                [0, 50, 150, 350, 700, 1200].forEach(function (delay) {
                    window.setTimeout(applyRestore, delay);
                });
            }

            function prepareUsersListFormSubmit(form) {
                if (!form || !form.closest('.admin-users-table tbody')) return;
                ensureUsersListScrollFields(form);
                var top = fillUsersListScrollFields(form);
                var tr = form.closest('tr');
                if (tr && tr.id && tr.id.indexOf('admin-user-') === 0) {
                    saveUsersListPosition(tr.id.slice('admin-user-'.length), top);
                }
            }

            document.querySelectorAll('.admin-users-table tbody form').forEach(ensureUsersListScrollFields);

            document.addEventListener('submit', function (e) {
                var form = e.target && e.target.closest ? e.target.closest('.admin-users-table tbody form') : null;
                if (form) prepareUsersListFormSubmit(form);
            }, true);

            document.addEventListener('click', function (e) {
                var btn = e.target.closest('.admin-users-table tbody button[type="submit"], .admin-users-table tbody input[type="submit"]');
                if (!btn) return;
                var form = btn.closest('form');
                if (form) prepareUsersListFormSubmit(form);
            }, true);

            function adminUsersAjaxPost(form) {
                return fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': (form.querySelector('input[name="_token"]') || {}).value || ''
                    },
                    credentials: 'same-origin'
                }).then(function (res) {
                    return res.json().then(function (data) {
                        if (!res.ok || !data.success) {
                            var err = new Error((data && data.message) ? data.message : 'Operazione non riuscita');
                            err.data = data;
                            throw err;
                        }
                        return data;
                    });
                });
            }

            function applyUserStateUi(row, status) {
                var form = row.querySelector('.admin-user-state-toggle-form');
                var cb = form ? form.querySelector('input[type="checkbox"]') : null;
                var pill = row.querySelector('.admin-user-state-pill');
                if (!form || !pill) return;
                var suspendUrl = form.getAttribute('data-url-suspend') || '';
                var approveUrl = form.getAttribute('data-url-approve') || '';
                if (status === 'approved') {
                    row.className = 'admin-user-row admin-user-row--approved';
                    row.setAttribute('data-user-stato', 'approved');
                    row.setAttribute('data-user-stato-rank', '0');
                    pill.className = 'badge bg-success admin-user-state-pill';
                    pill.textContent = 'Attivo';
                    form.action = suspendUrl;
                    if (cb) cb.checked = true;
                } else if (status === 'suspended') {
                    row.className = 'admin-user-row admin-user-row--suspended';
                    row.setAttribute('data-user-stato', 'suspended');
                    row.setAttribute('data-user-stato-rank', '2');
                    pill.className = 'badge bg-danger admin-user-state-pill';
                    pill.textContent = 'Sospeso';
                    form.action = approveUrl;
                    if (cb) cb.checked = false;
                }
            }

            function flashUserRow(row) {
                if (!row) return;
                row.classList.add('admin-user-row--highlight');
                window.setTimeout(function () {
                    row.classList.remove('admin-user-row--highlight');
                }, 2000);
            }

            document.querySelectorAll('.admin-user-state-toggle-form').forEach(function (form) {
                var cb = form.querySelector('input[type="checkbox"]');
                if (!cb) return;
                cb.addEventListener('change', function (e) {
                    var msg = cb.checked
                        ? 'Rendere questo utente ATTIVO?'
                        : 'SOSPENDERE questo utente?';
                    if (!window.confirm(msg)) {
                        e.preventDefault();
                        cb.checked = !cb.checked;
                        return;
                    }
                    var row = form.closest('tr');
                    var prevChecked = !cb.checked;
                    cb.disabled = true;
                    adminUsersAjaxPost(form)
                        .then(function (data) {
                            if (row && data.status) {
                                applyUserStateUi(row, data.status);
                                flashUserRow(row);
                            }
                        })
                        .catch(function (err) {
                            cb.checked = prevChecked;
                            window.alert((err && err.message) ? err.message : 'Operazione non riuscita.');
                        })
                        .finally(function () {
                            cb.disabled = false;
                        });
                });
            });

            document.querySelectorAll('.admin-user-news-toggle-form').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    var btn = form.querySelector('button[type="submit"]');
                    var inviaInput = form.querySelector('input[name="invia"]');
                    if (!btn || !inviaInput) return;
                    var row = form.closest('tr');
                    btn.disabled = true;
                    adminUsersAjaxPost(form)
                        .then(function (data) {
                            var on = !!data.invia;
                            inviaInput.value = on ? '0' : '1';
                            btn.className = 'btn btn-sm ' + (on ? 'btn-success' : 'btn-outline-danger');
                            btn.title = on ? 'Disattiva newsletter' : 'Attiva newsletter';
                            btn.innerHTML = '<i class="fas ' + (on ? 'fa-envelope-open' : 'fa-envelope') + '"></i> ' + (on ? 'Sì' : 'No');
                            if (row) {
                                row.setAttribute('data-user-news-rank', on ? '1' : '0');
                                flashUserRow(row);
                            }
                        })
                        .catch(function (err) {
                            window.alert((err && err.message) ? err.message : 'Operazione non riuscita.');
                        })
                        .finally(function () {
                            btn.disabled = false;
                        });
                });
            });

            restoreUsersListPosition();
            window.addEventListener('load', restoreUsersListPosition);

            var input = document.getElementById('userFinder');
            var clearBtn = document.getElementById('userFinderClear');
            var fieldSel = document.getElementById('userFinderField');
            var datalist = document.getElementById('adminUsersFinderSuggestions');
            var finderSuggestionsUrl = @json($adminUsersFinderSuggestionsUrl);
            if (!input) return;

            var rows = Array.prototype.slice.call(document.querySelectorAll('.admin-users-table tbody tr'));

            var sugTimer = null;
            var sugLastKey = '';

            function clearFinderSuggestions() {
                if (!datalist) return;
                while (datalist.firstChild) datalist.removeChild(datalist.firstChild);
            }

            function setFinderSuggestions(items) {
                if (!datalist) return;
                clearFinderSuggestions();
                (items || []).forEach(function (v) {
                    var opt = document.createElement('option');
                    opt.value = String(v || '');
                    datalist.appendChild(opt);
                });
            }

            function fetchFinderSuggestions() {
                if (!datalist || !finderSuggestionsUrl) return;
                var q = (input.value || '').trim();
                var field = (fieldSel && fieldSel.value) ? fieldSel.value : 'nome';
                var key = field + '|' + q;
                if (key === sugLastKey) return;
                sugLastKey = key;
                if (q.length < 2) {
                    clearFinderSuggestions();
                    return;
                }
                var url = finderSuggestionsUrl + '?field=' + encodeURIComponent(field) + '&q=' + encodeURIComponent(q);
                fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                    .then(function (r) { return r.ok ? r.json() : []; })
                    .then(function (items) { setFinderSuggestions(Array.isArray(items) ? items : []); })
                    .catch(function () { clearFinderSuggestions(); });
            }

            function scheduleFinderSuggestions() {
                if (sugTimer) window.clearTimeout(sugTimer);
                sugTimer = window.setTimeout(fetchFinderSuggestions, 200);
            }

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

            input.addEventListener('input', function () {
                applyFilter();
                scheduleFinderSuggestions();
            });
            input.addEventListener('change', function () {
                applyFilter();
                scheduleFinderSuggestions();
            });
            input.addEventListener('focus', scheduleFinderSuggestions);
            input.addEventListener('compositionend', scheduleFinderSuggestions);
            if (fieldSel) {
                fieldSel.addEventListener('change', function () {
                    sugLastKey = '';
                    clearFinderSuggestions();
                    applyFilter();
                    scheduleFinderSuggestions();
                    input.focus();
                });
            }

            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    sugLastKey = '';
                    input.value = '';
                    clearFinderSuggestions();
                    applyFilter();
                    input.focus();
                });
            }

            function defaultSortDirForKey(sortKey) {
                return (sortKey === 'ultimo_accesso' || sortKey === 'giorni' || sortKey === 'iscr' || sortKey === 'datanascita' || sortKey === 'eventi')
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
                    if (key === 'giorni') {
                        var ag = getNumFromCell(a, '[data-sort-giorni]', 'data-sort-giorni');
                        var bg = getNumFromCell(b, '[data-sort-giorni]', 'data-sort-giorni');
                        return ag - bg;
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
