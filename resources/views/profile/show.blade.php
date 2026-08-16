@extends('layouts.app')

@section('title', 'Profilo di ' . $user->nickname)

@section('content')
    <div class="container">
        @php
            $isAdminViewer = auth()->check() && auth()->user()->isAdmin();
            $canSeePrivateContacts = auth()->check() && (auth()->id() === $user->id || $isAdminViewer);
            $adminViewingOtherUser = $isAdminViewer && auth()->check() && (int) auth()->id() !== (int) $user->getKey();
            $isProfileOwner = auth()->check() && (int) auth()->id() === (int) $user->getKey();
            $canSeeParticipatedEvents = !$user->nascondi_eventi_partecipati || $isProfileOwner || $isAdminViewer;
        @endphp
        <div class="mb-3">
            @if(!empty($profileReturnUrl))
                <a href="{{ $profileReturnUrl }}" class="btn btn-success btn-sm">
                    <i class="fas fa-arrow-left"></i> Torna all'elenco
                </a>
            @else
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.users.index') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-arrow-left"></i> Torna all'elenco
                        </a>
                    @else
                            <a href="{{ url()->previous() }}" class="btn btn-success btn-sm">
                            <i class="fas fa-arrow-left"></i> Torna all'elenco
                        </a>
                    @endif
                @else
                    <a href="{{ url()->previous() }}" class="btn btn-success btn-sm">
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
                        @if($adminViewingOtherUser)
                            @php
                                $adminPwdOpenToolbar = $errors->has('password');
                            @endphp
                            <div class="profile-admin-actions-toolbar d-flex flex-wrap align-items-center gap-2 mb-3">
                                @if(!$user->isAdmin() && !session()->has('impersonator_id'))
                                    <form action="{{ route('admin.users.impersonate', $user) }}" method="POST" class="d-inline m-0 flex-shrink-0">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-outline-primary btn-sm profile-action-chip-btn"
                                                onclick="return confirm('Impersonare {{ $user->username }}? Vedrai il sito come questo utente fino a «Torna admin».')">
                                            <i class="fas fa-user-secret me-1"></i>Impersona utente
                                        </button>
                                    </form>
                                @endif
                                @if(auth()->user()->isFriendOf($user))
                                    <form action="{{ route('friends.remove', $user) }}" method="POST" class="d-inline-flex m-0 flex-shrink-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm profile-action-chip-btn">
                                            <i class="fas fa-user-minus me-1"></i>Rimuovi dagli amici
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('friends.add', $user) }}" method="POST" class="d-inline-flex m-0 flex-shrink-0">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary btn-sm profile-action-chip-btn">
                                            <i class="fas fa-user-plus me-1"></i>Aggiungi agli amici
                                        </button>
                                    </form>
                                @endif
                                @if(!$user->isAdmin())
                                    @if($user->status === 'banned')
                                        <form action="{{ route('admin.users.unban', $user) }}" method="POST" class="d-inline m-0 flex-shrink-0">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-sm profile-action-chip-btn">
                                                <i class="fas fa-unlock me-1"></i>Ripristina utente
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.users.ban', $user) }}" method="POST" class="d-inline m-0 flex-shrink-0">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm profile-action-chip-btn">
                                                <i class="fas fa-ban me-1"></i>Banna utente
                                            </button>
                                        </form>
                                    @endif
                                @endif
                                <button type="button"
                                        class="btn btn-outline-secondary btn-sm profile-action-chip-btn flex-shrink-0"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#adminPasswordCollapse"
                                        aria-expanded="{{ $adminPwdOpenToolbar ? 'true' : 'false' }}"
                                        aria-controls="adminPasswordCollapse"
                                        id="adminPasswordToolbarToggle">
                                    <i class="fas fa-key me-1"></i>Modifica password
                                </button>
                            </div>
                            <div class="collapse {{ $adminPwdOpenToolbar ? 'show' : '' }} mb-3" id="adminPasswordCollapse">
                                <div class="border rounded bg-light px-3 py-3 small">
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
                                                       required autocomplete="new-password" minlength="4">
                                                @error('password')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label for="admin_new_password_confirmation" class="form-label mb-0">Conferma password</label>
                                                <input type="password" id="admin_new_password_confirmation" name="password_confirmation"
                                                       class="form-control form-control-sm"
                                                       required autocomplete="new-password" minlength="4">
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-outline-secondary btn-sm mt-2">
                                            <i class="fas fa-save"></i> Salva password
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                        <div class="row g-3 align-items-start">
                            <div class="col-12 col-md-6">
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
                                    <div class="row g-2 profile-dob-row">
                                        <div class="col-6">
                                            <div class="profile-field">
                                                <span class="profile-label">
                                                    @if($user->sesso === 'm') Nato:
                                                    @elseif($user->sesso === 'f') Nata:
                                                    @else Nato/Nata:
                                                    @endif
                                                </span>
                                                <span class="profile-value">{{ $isAdminViewer && $user->datanascita ? $user->datanascita->format('d/m/Y') : '—' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="profile-field">
                                                <span class="profile-label">Età:</span>
                                                <span class="profile-value">{{ $isAdminViewer && $user->datanascita ? $user->datanascita->age : '—' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-2 profile-dob-row">
                                        <div class="col-6">
                                            <div class="profile-field">
                                                <span class="profile-label">Sesso:</span>
                                                <span class="profile-value">
                                                    @if($user->sesso === 'f') Donna
                                                    @elseif($user->sesso === 'm') Uomo
                                                    @else —
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="profile-field">
                                                <span class="profile-label">Iscritto dal:</span>
                                                <span class="profile-value">{{ $user->created_at ? $user->created_at->format('d/m/Y') : '—' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="profile-field">
                                        <span class="profile-label profile-label--email">E-mail:</span>
                                        <span class="profile-value">
                                            @if($canSeePrivateContacts && $user->email)
                                                <a href="mailto:{{ $user->email }}" class="text-decoration-none fw-semibold">
                                                    <i class="fas fa-envelope me-1"></i>{{ $user->email }}
                                                </a>
                                            @else
                                                {{ $canSeePrivateContacts ? '—' : '—' }}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="profile-field">
                                        <span class="profile-label">Cellulare:</span>
                                        <span class="profile-value">{{ $canSeePrivateContacts ? ($user->telefono ?: '—') : '—' }}</span>
                                    </div>
                                    <div class="profile-field">
                                        <span class="profile-label">Residenza:</span>
                                        <span class="profile-value">{{ $user->residenza ?: '—' }}</span>
                                    </div>
                                </div>
                                @auth
                                    @if($isAdminViewer && !$adminViewingOtherUser)
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
                                                <span>Modifica password</span>
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
                                                                       required autocomplete="new-password" minlength="4">
                                                                @error('password')
                                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <label for="admin_new_password_confirmation" class="form-label mb-0">Conferma password</label>
                                                                <input type="password" id="admin_new_password_confirmation" name="password_confirmation"
                                                                       class="form-control form-control-sm"
                                                                       required autocomplete="new-password" minlength="4">
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
                                    <div class="d-flex align-items-center gap-2 flex-nowrap w-100 profile-main-actions-row">
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
                                            <span class="badge profile-action-chip {{ $user->isOrganizer() ? 'badge-organizzatore' : 'bg-' . $roleClass }}">
                                                {{ $user->role_name }}
                                            </span>
                                        @endif
                                    @php
                                        $isBanned = $user->status === 'banned';
                                        $isApproved = $user->status === 'approved';
                                        $isAwaiting = $user->status === 'awaiting';
                                        $isSuspended = $user->status === 'suspended';
                                        $statusLabel = $isApproved ? 'Attivo' : ($isBanned ? 'Disattivato' : ($isAwaiting ? 'In attesa di approvazione' : ($isSuspended ? 'Sospeso' : 'Non attivo')));
                                        $statusBg = match (true) {
                                            $isApproved => 'success',
                                            $isBanned => 'danger',
                                            $isAwaiting => 'warning',
                                            $isSuspended => 'secondary',
                                            default => 'secondary',
                                        };
                                    @endphp
                                        @auth
                                            @if(auth()->id() === $user->id || $isAdminViewer)
                                                <div class="d-flex align-items-center gap-2 flex-nowrap">
                                                    <a href="{{ route('profile.edit', $user) }}" class="btn btn-primary btn-sm profile-main-action-btn">
                                                        <i class="fas fa-edit"></i> Modifica Profilo
                                                    </a>
                                                     @if(auth()->id() === $user->id)
                                                         <a href="{{ route('profile.edit', $user) }}?openPassword=1" class="btn btn-outline-secondary btn-sm profile-main-action-btn">
                                                             <i class="fas fa-key me-1"></i> Modifica passw
                                                         </a>
                                                     @elseif($isAdminViewer && !$adminViewingOtherUser)
                                                        <button type="button"
                                                                class="btn btn-outline-secondary btn-sm profile-main-action-btn"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#adminPasswordCollapse"
                                                                aria-controls="adminPasswordCollapse"
                                                                aria-expanded="false">
                                                            <i class="fas fa-key me-1"></i> Modifica passw
                                                        </button>
                                                    @endif
                                                </div>
                                            @endif
                                            @if(auth()->id() !== $user->id && !$adminViewingOtherUser)
                                                @if(auth()->user()->isFriendOf($user))
                                                    <form action="{{ route('friends.remove', $user) }}" method="POST" class="d-inline-flex">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm profile-action-chip-btn flex-shrink-0">
                                                            <i class="fas fa-user-minus"></i> Rimuovi dagli amici
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('friends.add', $user) }}" method="POST" class="d-inline-flex">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-primary btn-sm profile-action-chip-btn flex-shrink-0">
                                                            <i class="fas fa-user-plus"></i> Aggiungi agli amici
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        @endauth
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap small w-100">
                                        <span class="badge profile-action-chip bg-{{ $statusBg }}">
                                            {{ $statusLabel }}
                                        </span>
                                        <span class="d-inline-block px-2 py-1 rounded text-primary border border-primary">
                                            Ultimo collegamento: {{ $user->ultimo_accesso ? $user->ultimo_accesso->format('d/m/Y H:i') : '—' }}
                                        </span>
                                    </div>
                                    @auth
                                        @if(auth()->id() === $user->id)
                                            <div class="d-flex align-items-center gap-2 flex-wrap w-100">
                                                <form action="{{ route('profile.delete-request', $user) }}" method="POST" class="d-inline-flex m-0"
                                                      onsubmit="return confirm('Sei sicuro di voler richiedere la cancellazione del tuo account? L\'account verrà eliminato da un amministratore dopo aver ricevuto la richiesta.');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger btn-sm profile-main-action-btn">
                                                        <i class="fas fa-user-slash me-1"></i> Cancella account
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    @endauth
                                    @auth
                                        @if(auth()->user()->isAdmin() && !$user->isAdmin() && !$adminViewingOtherUser)
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
                                </div>
                            </div>
                        </div>
                        <div class="profile-field profile-field--descr mt-3">
                            <div class="profile-label">Descrizione</div>
                            <div class="profile-value profile-value--descr">{!! $user->safe_descr !== '' ? $user->safe_descr : '—' !!}</div>
                        </div>
                        @if($isAdminViewer)
                            <div class="profile-field profile-field--admin-note mt-3">
                                <form action="{{ route('profile.admin-note.update', $user) }}" method="POST" class="profile-admin-note-form">
                                    @csrf
                                    <div class="profile-admin-note-layout">
                                        <div class="profile-admin-note-left">
                                            <div class="profile-label">
                                                <i class="fas fa-sticky-note me-1"></i> Note relative
                                            </div>
                                            <button type="submit" class="btn btn-success btn-sm mt-2 w-auto">
                                                <i class="fas fa-save me-1"></i> Salva nota
                                            </button>
                                        </div>
                                        <div class="profile-admin-note-right">
                                            <textarea name="note_utente"
                                                      rows="3"
                                                      class="form-control form-control-sm w-100 @error('note_utente', 'adminNote') is-invalid @enderror"
                                                      placeholder="Inserisci una nota interna visibile solo agli amministratori...">{{ old('note_utente', $user->note_utente ?? '') }}</textarea>
                                            @error('note_utente', 'adminNote')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h4 class="mb-0">
                            <i class="fas fa-calendar"></i> Eventi Partecipati
                            @if($canSeeParticipatedEvents)
                                <span class="badge bg-primary">{{ $allParticipatedEvents->count() }}</span>
                            @endif
                        </h4>
                        @if($isProfileOwner)
                            <form action="{{ route('profile.toggle-hide-participated-events', $user) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm">
                                    @if($user->nascondi_eventi_partecipati)
                                        <i class="fas fa-eye me-1"></i> Mostra elenco agli altri utenti
                                    @else
                                        <i class="fas fa-eye-slash me-1"></i> Nascondi elenco agli altri utenti
                                    @endif
                                </button>
                            </form>
                        @endif
                    </div>
                    <div class="card-body">
                        @if(!$canSeeParticipatedEvents)
                            <div class="text-center py-4">
                                <i class="fas fa-eye-slash fa-3x text-muted mb-3"></i>
                                <h5>Elenco nascosto</h5>
                                <p class="text-muted mb-0">
                                    {{ $user->username }} ha scelto di nascondere il proprio elenco eventi partecipati.
                                </p>
                            </div>
                        @elseif($allParticipatedEvents->count() > 0)
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
            max-width: 100%;
        }
        .profile-field {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.55rem;
            border: 1px solid #adb5bd;
            background: #f2f4f6;
            border-radius: 8px;
            min-height: 36px;
        }
        /* Nato/Nata + Età: stessa altezza e più compatti */
        .profile-dob-row > [class*="col-"] {
            display: flex;
        }
        .profile-dob-row .profile-field {
            flex: 1;
            min-height: 36px;
            padding: 0.25rem 0.55rem;
        }
        .profile-dob-row .profile-label {
            min-width: 0;
        }
        .profile-fields-compact .profile-field {
            border: 1px solid #adb5bd;
            border-radius: 8px;
            padding: 0.25rem 0.55rem;
            min-height: 36px;
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
        .profile-label--email {
            min-width: 52px;
        }
        .profile-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #212529;
            word-break: break-word;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            min-width: 0;
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
            min-height: 24px;
            padding: 0.16rem 0.42rem;
            font-size: 0.76rem;
            line-height: 1;
            border: 1px solid #adb5bd;
        }
        .profile-action-chip-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            min-height: 28px;
            padding: 0.2rem 0.5rem;
            line-height: 1;
        }
        .profile-main-actions-row {
            min-width: 0;
        }
        .profile-main-action-btn {
            padding: 0.16rem 0.42rem !important;
            font-size: 0.76rem !important;
            line-height: 1.05 !important;
            white-space: nowrap;
        }
        /* Box admin nota: titolo -> pulsante salva -> textarea a piena larghezza */
        .profile-field--admin-note {
            flex-direction: column;
            align-items: flex-start;
            width: 100%;
        }
        .profile-admin-note-form,
        .profile-admin-note-layout,
        .profile-admin-note-right {
            width: 100%;
        }
        .profile-admin-note-layout {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 0.75rem;
            align-items: start;
        }
        .profile-admin-note-left .profile-label {
            min-width: 0;
            white-space: normal;
        }
        .profile-admin-note-right {
            min-width: 0;
        }
        .profile-admin-note-right textarea,
        .profile-admin-note-right .form-control {
            width: 100% !important;
        }
        .badge-organizzatore {
            background: #ffc107 !important;
            color: #7a4a00 !important;
            border: 1px solid #adb5bd !important;
        }
        .profile-upcoming-events .list-group-item {
            border: 2px solid #0dcaf0 !important;
            --bs-list-group-border-color: #0dcaf0;
            border-radius: 10px;
            margin-bottom: 1.1rem;
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
            box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.08);
            width: 100%;
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
            width: 100%;
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
