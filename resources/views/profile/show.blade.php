@extends('layouts.app')

@section('title', 'Profilo di ' . $user->nickname)

@section('content')
    <div class="container">
        <div class="mb-3">
            @if(!empty($profileReturnUrl))
                <a href="{{ $profileReturnUrl }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Torna all'elenco
                </a>
            @else
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Torna all'elenco
                        </a>
                    @else
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Torna all'elenco
                        </a>
                    @endif
                @else
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Torna all'elenco
                    </a>
                @endauth
            @endif
        </div>
        @if($user->status === 'banned')
            <div class="alert alert-danger border-2 fw-semibold mb-3" role="alert">
                <i class="fas fa-user-slash"></i> Disattivato
            </div>
        @endif
        <div class="row">
            <div class="col-12 mb-3">
                <div class="card profile-upcoming-events">
                    <div class="card-body">
                        <div class="row g-3 align-items-start">
                            <div class="col-12 col-md-6">
                                @php
                                    $isAdminViewer = auth()->check() && auth()->user()->isAdmin();
                                @endphp
                                <div class="d-flex flex-column gap-2 profile-fields-compact">
                                    <div class="profile-field pt-0">
                                        <span class="profile-label">Username:</span>
                                        <span class="profile-value">{{ $user->username ?: '—' }}</span>
                                    </div>
                                    <div class="profile-field">
                                        <span class="profile-label">Nome:</span>
                                        <span class="profile-value">{{ $user->nome ?: '—' }}</span>
                                    </div>
                                    <div class="profile-field">
                                        <span class="profile-label">Cognome:</span>
                                        <span class="profile-value">{{ $isAdminViewer ? ($user->cognome ?: '—') : '—' }}</span>
                                    </div>
                                    <div class="profile-field">
                                        <span class="profile-label">Sesso:</span>
                                        <span class="profile-value">
                                            @if($user->sesso === 'f') Donna
                                            @elseif($user->sesso === 'm') Uomo
                                            @else —
                                            @endif
                                        </span>
                                    </div>
                                    <div class="profile-field">
                                        <span class="profile-label">E-mail:</span>
                                        <span class="profile-value">{{ $isAdminViewer ? ($user->email ?: '—') : '—' }}</span>
                                    </div>
                                    <div class="profile-field">
                                        <span class="profile-label">Telefono:</span>
                                        <span class="profile-value">{{ $isAdminViewer ? ($user->telefono ?: '—') : '—' }}</span>
                                    </div>
                                    <div class="profile-field">
                                        <span class="profile-label">Residenza:</span>
                                        <span class="profile-value">{{ $user->residenza ?: '—' }}</span>
                                    </div>
                                    <div class="profile-field">
                                        <span class="profile-label">Descrizione:</span>
                                        <span class="profile-value">
                                            {{ \App\Support\StrLimit::limit(strip_tags($user->safe_descr ?? ''), 90, '…') ?: '—' }}
                                        </span>
                                    </div>
                                    <div class="profile-field">
                                        <span class="profile-label">Età:</span>
                                        <span class="profile-value">{{ $isAdminViewer && $user->datanascita ? $user->datanascita->age : '—' }}</span>
                                    </div>
                                    <div class="profile-field pb-0">
                                        <span class="profile-label">Data di nascita:</span>
                                        <span class="profile-value">{{ $isAdminViewer && $user->datanascita ? $user->datanascita->format('d-m-Y') : '—' }}</span>
                                    </div>
                                    <div class="profile-field">
                                        <span class="profile-label">Ultimo collegamento:</span>
                                        <span class="profile-value">{{ $user->ultimo_accesso ? $user->ultimo_accesso->format('d/m/Y H:i') : '—' }}</span>
                                    </div>
                                </div>

                                @auth
                                    @if(auth()->id() === $user->id || $isAdminViewer)
                                        <a href="{{ route('profile.edit', $user) }}" class="btn btn-primary btn-sm mt-3">
                                            <i class="fas fa-edit"></i> Modifica Profilo
                                        </a>
                                    @endif
                                    @if($isAdminViewer)
                                        @php
                                            $adminPwdOpen = $errors->has('password');
                                        @endphp
                                        <div class="mt-3 admin-pwd-discrete">
                                            <button type="button"
                                                    class="btn btn-link btn-sm text-muted text-decoration-none p-0 d-inline-flex align-items-center gap-1"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#adminPasswordCollapse"
                                                    aria-expanded="{{ $adminPwdOpen ? 'true' : 'false' }}"
                                                    aria-controls="adminPasswordCollapse"
                                                    id="adminPasswordToggle">
                                                <i class="fas fa-key" style="font-size:0.85em;"></i>
                                                <span>Imposta password utente</span>
                                                <i class="fas fa-chevron-down small opacity-75" id="adminPasswordChevron" aria-hidden="true"></i>
                                            </button>
                                            <div class="collapse {{ $adminPwdOpen ? 'show' : '' }}" id="adminPasswordCollapse">
                                                <div class="border rounded bg-light px-3 py-3 mt-2 small">
                                                    <p class="text-muted mb-2 mb-md-3">
                                                        Account: <strong>{{ $user->username }}</strong>
                                                    </p>
                                                    <form method="POST" action="{{ route('profile.password.update', $user) }}">
                                                        @csrf
                                                        <div class="row g-2">
                                                            <div class="col-12 col-md-6">
                                                                <label for="admin_new_password" class="form-label mb-0">Nuova password</label>
                                                                <input type="password" id="admin_new_password" name="password"
                                                                       class="form-control form-control-sm @error('password') is-invalid @enderror"
                                                                       required autocomplete="new-password" minlength="8">
                                                                @error('password')
                                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <label for="admin_new_password_confirmation" class="form-label mb-0">Conferma password</label>
                                                                <input type="password" id="admin_new_password_confirmation" name="password_confirmation"
                                                                       class="form-control form-control-sm"
                                                                       required autocomplete="new-password" minlength="8">
                                                            </div>
                                                        </div>
                                                        <button type="submit" class="btn btn-outline-secondary btn-sm mt-2">
                                                            <i class="fas fa-save"></i> Salva password
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endauth
                            </div>

                            <div class="col-12 col-md-6 text-center text-md-start">
                                <div class="text-muted small mb-2">La foto</div>
                                @if($user->photo_url)
                                    <img src="{{ $user->photo_url }}"
                                         alt="{{ $user->name }}"
                                         class="profile-photo-full"
                                         loading="lazy">
                                @else
                                    <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center"
                                         style="width: 150px; height: 150px;">
                                        <i class="fas fa-user fa-3x text-white"></i>
                                    </div>
                                @endif

                                <div class="mt-3 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                    @php
                                        $roleClass = $user->isAdmin() ? 'danger' : ($user->isOrganizer() ? 'warning' : 'info');
                                    @endphp
                                    @if($isAdminViewer)
                                        <form action="{{ route('admin.users.update-role', $user) }}" method="POST" class="d-inline-flex align-items-center gap-1">
                                            @csrf
                                            <label for="role-select-{{ $user->id }}" class="small me-1 mb-0">Ruolo:</label>
                                            <select id="role-select-{{ $user->id }}" name="ruolo"
                                                    class="form-select form-select-sm w-auto">
                                                <option value="2" {{ (int)$user->ruolo === 2 ? 'selected' : '' }}>Utente</option>
                                                <option value="1" {{ (int)$user->ruolo === 1 ? 'selected' : '' }}>Organizzatore</option>
                                                <option value="0" {{ (int)$user->ruolo === 0 ? 'selected' : '' }}>Amministratore</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge profile-action-chip bg-{{ $roleClass }}">
                                            {{ $user->role_name }}
                                        </span>
                                    @endif
                                    @php
                                        $isBanned = $user->status === 'banned';
                                        $isApproved = $user->status === 'approved';
                                        $statusLabel = $isApproved ? 'Attivo' : ($isBanned ? 'Disattivato' : 'Sospeso');
                                        $statusBg = $isApproved ? 'success' : 'danger';
                                    @endphp
                                    <span class="badge profile-action-chip bg-{{ $statusBg }}">
                                        {{ $statusLabel }}
                                    </span>
                                    @auth
                                        @if(auth()->user()->isAdmin() && !$user->isAdmin())
                                            @if($user->status === 'banned')
                                                <form action="{{ route('admin.users.unban', $user) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-warning btn-sm profile-action-chip-btn">
                                                        <i class="fas fa-unlock"></i> Ripristina Utente
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.users.ban', $user) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm profile-action-chip-btn">
                                                        <i class="fas fa-ban"></i> Banna Utente
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    @endauth
                                    @auth
                                        @if(auth()->id() !== $user->id)
                                            @if(auth()->user()->isFriendOf($user))
                                                <form action="{{ route('friends.remove', $user) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm profile-action-chip-btn">
                                                        <i class="fas fa-user-minus"></i> Rimuovi dagli amici
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('friends.add', $user) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-primary btn-sm profile-action-chip-btn">
                                                        <i class="fas fa-user-plus"></i> Aggiungi agli amici
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    @endauth
                                </div>
                            </div>
                        </div>
                        <div class="profile-field profile-field--descr mt-3">
                            <div class="profile-label">Descrizione</div>
                            <div class="profile-value profile-value--descr">{!! $user->safe_descr !== '' ? $user->safe_descr : '—' !!}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-calendar"></i> Eventi Partecipati
                            <span class="badge bg-primary">{{ $allParticipatedEvents->count() }}</span>
                        </h4>
                    </div>
                    <div class="card-body">
                        @if($allParticipatedEvents->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-hover align-middle mb-0 profile-events-table">
                                    <thead>
                                    <tr>
                                        <th>Titolo</th>
                                        <th>Data</th>
                                        <th>Luogo</th>
                                        <th>Stato</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($allParticipatedEvents as $event)
                                        @php
                                            $eventDate = $event->date ?? $event->dataevento;
                                            $isPast = $eventDate ? $eventDate->lte(now()) : false;
                                        @endphp
                                        <tr onclick="window.location='{{ route('events.show', $event) }}'" style="cursor:pointer;">
                                            <td class="fw-semibold">{{ $event->title }}</td>
                                            <td>{{ $eventDate ? $eventDate->format('d/m/Y H:i') : '—' }}</td>
                                            <td>{{ $event->city ?: '—' }}@if($event->dove) - {{ $event->dove }} @endif</td>
                                            <td>
                                                <span class="badge bg-{{ $isPast ? 'secondary' : 'success' }}">
                                                    {{ $isPast ? 'Passato' : 'In programma' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5>Nessun evento partecipato</h5>
                                <p class="text-muted">
                                    {{ $user->username }} non ha ancora partecipato a eventi.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        .profile-photo-full {
            max-width: 240px;
            width: 100%;
            height: auto;
            object-fit: contain;
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.12);
            background: #fff;
        }
        .profile-fields-compact {
            max-width: 380px;
        }
        .profile-field {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.55rem;
            border-bottom: 1px solid rgba(0,0,0,0.08);
            background: #f2f4f6;
            border-radius: 8px;
        }
        .profile-fields-compact .profile-field {
            border: 1px solid #cfe8f3;
            border-radius: 8px;
            padding: 0.22rem 0.55rem;
            margin-bottom: 0.32rem;
            background: #f2f4f6;
        }
        .profile-fields-compact .profile-field:last-child {
            margin-bottom: 0;
        }
        .profile-field--descr {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.35rem;
            width: 100%;
        }
        .profile-field:last-child {
            border-bottom: 0;
        }
        .profile-label {
            font-size: 0.83rem;
            color: #5c6670;
            font-weight: 600;
            white-space: nowrap;
            min-width: 98px;
        }
        .profile-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #212529;
            word-break: break-word;
        }
        .profile-value--descr {
            border: 0;
            background: transparent;
            border-radius: 0;
            padding: 0;
            font-weight: 500;
            width: 100%;
            display: block;
            white-space: normal;
            word-break: break-word;
        }
        .profile-value--descr p {
            margin-bottom: 0.5rem;
        }
        .profile-value--descr p:last-child {
            margin-bottom: 0;
        }
        .profile-value--descr ul,
        .profile-value--descr ol {
            margin: 0.25rem 0 0.5rem 1.25rem;
        }
        .profile-action-chip {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 0.2rem 0.5rem;
            font-size: 0.78rem;
            line-height: 1;
        }
        .profile-action-chip-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            min-height: 28px;
            padding: 0.2rem 0.5rem;
            line-height: 1;
        }
        .profile-upcoming-events .list-group-item {
            border: 2px solid #0dcaf0 !important;
            --bs-list-group-border-color: #0dcaf0;
            border-radius: 10px;
            margin-bottom: 1.1rem;
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
            box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.08);
        }
        .profile-upcoming-events .card-header,
        .profile-upcoming-events .card-body {
            border-color: #0dcaf0;
        }
        .profile-upcoming-events .list-group-item:last-child {
            margin-bottom: 0;
        }
        .profile-upcoming-events .list-group {
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
        }
        .profile-events-table th,
        .profile-events-table td {
            padding-top: 0.2rem;
            padding-bottom: 0.2rem;
            font-size: 0.8rem;
            line-height: 1.05;
            white-space: nowrap;
            vertical-align: middle;
        }
        .profile-events-table td:first-child {
            max-width: 320px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .admin-pwd-discrete #adminPasswordToggle:hover {
            color: #495057 !important;
        }
        .admin-pwd-discrete #adminPasswordChevron {
            transition: transform 0.2s ease;
        }
        .admin-pwd-discrete #adminPasswordToggle[aria-expanded="true"] #adminPasswordChevron {
            transform: rotate(180deg);
        }
    </style>
@endsection
