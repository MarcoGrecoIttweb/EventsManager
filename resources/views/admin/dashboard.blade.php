@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <style>
        /* Box statistiche: altezza compatibile col pulsante .btn (es. «Crea nuovo evento») */
        .admin-dashboard-stat-card .admin-dashboard-stat-card__body {
            min-height: calc(1.5em + 0.75rem + 2px);
            padding-top: 0.375rem !important;
            padding-bottom: 0.375rem !important;
        }
        .admin-dashboard-stat-card__label {
            font-size: 0.7rem;
            line-height: 1.1;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            font-weight: 800;
        }
        .admin-dashboard-stat-card__value {
            font-size: 1.35rem;
            line-height: 1.1;
        }
        .admin-dashboard-stat-card__icon {
            font-size: 1.15rem;
            opacity: 0.9;
        }
        .admin-dashboard-stats .admin-dashboard-stat-card {
            height: 100%;
        }
        .admin-dashboard-stats > [class*='col-'] {
            display: flex;
        }
        .admin-dashboard-stats > [class*='col-'] > .card {
            flex: 1 1 auto;
        }
        .admin-dashboard-stat-label-dark {
            color: #495057 !important; /* grigio leggermente più chiaro */
            opacity: 0.95 !important;
        }
        .admin-dashboard-stat-pill {
            font-size: 0.95rem;
            line-height: 1;
            padding: 0.28rem 0.55rem;
        }
        .admin-dashboard-panel-border {
            border: 2px solid #adb5bd !important; /* grigio */
        }
        .admin-visibility-sections-box {
            border: 2px solid #adb5bd !important; /* grigio */
            padding: 0.55rem !important;
            max-width: 29.5rem; /* 2 pulsanti + gap */
            margin-left: 0;
            margin-right: auto;
        }
        .admin-visibility-sections-box .admin-feature-toggle-form {
            padding: 0.35rem 0.5rem;
            border-radius: 0.6rem;
        }
        .admin-visibility-sections-box .form-check-label,
        .admin-visibility-sections-box small {
            font-size: 0.85rem;
        }
        .admin-visibility-sections-box .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.45rem;
        }
        .admin-visibility-sections-box .form-check-input {
            transform: scale(0.9);
            transform-origin: left center;
        }
        .admin-quick-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: flex-start;
        }
        .admin-quick-actions .btn {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }
        .admin-quick-actions-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: space-between;
        }
        .admin-quick-actions-row .btn {
            flex: 1 1 100%;
            max-width: 100%;
        }
        @media (max-width: 575.98px) {
            .admin-quick-actions-row .btn {
                flex: 1 1 100%;
                max-width: 100%;
            }
            .admin-quick-actions-row {
                flex-wrap: wrap;
            }
        }
        .admin-quick-actions-col {
            display: flex;
        }
        .admin-quick-actions-card {
            max-width: 29.5rem; /* mantiene layout pulsanti come prima */
            width: 100%;
        }
        .admin-dashboard-stats-narrow {
            max-width: 29.5rem; /* stessa larghezza del box "Azioni rapide" */
            margin-left: auto;
            margin-right: auto;
        }
        @media (min-width: 992px) {
            .admin-quick-actions-card {
                width: auto;
            }
        }
        /* Azioni rapide: rendi "outline" sempre pieno (come hover) */
        .admin-quick-actions .btn.btn-outline-warning {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
            color: #000 !important;
        }
        .admin-quick-actions .btn.btn-outline-primary {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #fff !important;
        }
        .admin-quick-actions .btn.btn-outline-info {
            background-color: #0dcaf0 !important;
            border-color: #0dcaf0 !important;
            color: #000 !important;
        }
        .admin-dashboard-stat-card {
            border: 2px solid #8B4513 !important; /* stesso bordo "lista di attesa" */
            background-color: #fff3cd !important; /* stesso giallo "lista di attesa" */
            color: #212529 !important;
        }
        .admin-dashboard-stat-btn {
            padding: 0.15rem 0.45rem !important;
            font-size: 0.82rem !important;
            line-height: 1.15 !important;
            border-radius: 0.4rem !important;
            white-space: nowrap;
            border-width: 2px !important;
        }

        /* Toggle feature (Mercatino/Salottino): bordi neri come richiesto */
        .admin-feature-toggle-form {
            border: 2px solid #000;
            border-radius: 10px;
            padding: 0.6rem 0.75rem;
            background: rgba(255, 255, 255, 0.6);
        }
        .admin-feature-toggle-form .form-check-input {
            border: 2px solid #000 !important;
        }
        .admin-feature-toggle-form .badge {
            border: 2px solid #000;
        }

        .admin-pending-approvals-box {
            border: 2px solid #8B4513;
            background: #fff3cd; /* giallo (bootstrap warning) */
            border-radius: 0.65rem;
            /* override bootstrap alert padding per renderlo "alto come un bottone" */
            padding: 0.375rem 0.6rem !important; /* ~btn padding */
            max-width: 58rem; /* riduce larghezza su schermi grandi */
            margin-left: auto;
            margin-right: auto;
        }
        .admin-pending-approvals-box.alert {
            margin-bottom: 0 !important;
        }
        .admin-pending-approvals-box .alert-heading {
            margin: 0 !important;
        }
        .admin-pending-approvals-btn {
            border: 2px solid #0d6efd !important; /* blu */
        }
        .admin-pending-approvals-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.45rem;
            height: 1.45rem;
            padding: 0 0.35rem;
            font-weight: 800;
            line-height: 1;
            border: 2px solid #8B0000;
        }
        .admin-pending-approvals-close-btn {
            border: 2px solid #8B4513 !important;
            background: rgba(255, 255, 255, 0.92) !important;
            color: #5a3a1b !important;
            padding: 0.1rem 0.35rem !important;
            font-size: 0.8rem !important;
            line-height: 1 !important;
            border-radius: 0.45rem !important;
        }
        .admin-pending-approvals-close-btn:focus {
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25) !important;
        }
        .admin-pending-approvals-box .alert-heading {
            font-size: 0.95rem;
            line-height: 1.05;
        }
        .admin-pending-approvals-box .admin-pending-approvals-btn {
            padding: 0.15rem 0.4rem;
            font-size: 0.8rem;
            line-height: 1.15;
            border-radius: 0.4rem;
            white-space: nowrap;
        }
        .admin-pending-approvals-box.alert-dismissible .admin-pending-approvals-close-btn {
            position: static !important; /* evita sovrapposizione bootstrap (absolute) */
        }
    </style>
    <div class="container-fluid py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <h1 class="h2 mb-0">
                <i class="fas fa-tachometer-alt text-primary"></i> Dashboard Admin
            </h1>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-home"></i> Home
            </a>
        </div>

        @if($pendingUsers > 0)
            <div class="mb-4" role="alert">
                <div class="admin-pending-approvals-box alert alert-dismissible fade show mb-0" role="alert">
                    <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap">
                        <div class="d-flex align-items-center gap-2 flex-nowrap min-w-0">
                            <h5 class="alert-heading mb-0 text-truncate min-w-0">
                                <i class="fas fa-bell me-2"></i>
                                Iscrizioni in lista di attesa
                                <span class="badge rounded-pill bg-danger admin-pending-approvals-count ms-2">{{ $pendingUsers }}</span>
                            </h5>
                            <a href="{{ route('admin.users.index', ['registrations' => 'pending']) }}"
                               class="btn btn-success btn-sm fw-semibold admin-pending-approvals-btn">
                                <i class="fas fa-user-check me-1"></i> Vai alle iscrizioni lista di attesa
                            </a>
                        </div>
                        <button type="button"
                                class="btn btn-sm admin-pending-approvals-close-btn flex-shrink-0"
                                data-bs-dismiss="alert"
                                aria-label="Chiudi"
                                title="Chiudi">
                            ×
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="row g-3 mb-4 admin-dashboard-stats admin-dashboard-stats-narrow">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 bg-warning text-dark admin-dashboard-stat-card">
                    <div class="card-body admin-dashboard-stat-card__body d-flex flex-wrap align-items-center justify-content-between gap-2 px-3">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <i class="fas fa-clock admin-dashboard-stat-card__icon" aria-hidden="true"></i>
                            <div class="min-w-0">
                                <div class="admin-dashboard-stat-card__label admin-dashboard-stat-label-dark text-truncate">
                                    Utenti in attesa
                                    <span class="badge rounded-pill bg-danger text-white ms-2 admin-dashboard-stat-pill">{{ $pendingUsers }}</span>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('admin.users.index', ['registrations' => 'pending']) }}" class="btn btn-danger btn-sm flex-shrink-0 admin-dashboard-stat-btn">
                            Approva iscrizioni
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 bg-primary text-white admin-dashboard-stat-card">
                    <div class="card-body admin-dashboard-stat-card__body d-flex flex-wrap align-items-center justify-content-between gap-2 px-3">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <i class="fas fa-users admin-dashboard-stat-card__icon" aria-hidden="true"></i>
                            <div class="min-w-0">
                                <div class="admin-dashboard-stat-card__label admin-dashboard-stat-label-dark text-truncate">
                                    Utenti totali <span class="admin-dashboard-stat-card__value fw-bold ms-1">{{ $usersCount }}</span>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm text-white fw-semibold flex-shrink-0 admin-dashboard-stat-btn">
                            Gestisci utenti
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 bg-success text-white admin-dashboard-stat-card">
                    <div class="card-body admin-dashboard-stat-card__body d-flex flex-wrap align-items-center justify-content-between gap-2 px-3">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <i class="fas fa-calendar admin-dashboard-stat-card__icon" aria-hidden="true"></i>
                            <div class="min-w-0">
                                <div class="admin-dashboard-stat-card__label admin-dashboard-stat-label-dark text-truncate">
                                    Eventi pubblicati <span class="admin-dashboard-stat-card__value fw-bold ms-1">{{ $eventsCount }}</span>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('admin.events.index') }}" class="btn btn-secondary btn-sm text-white fw-semibold flex-shrink-0 admin-dashboard-stat-btn">
                            Gestisci eventi
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6 admin-quick-actions-col">
                <div class="card border-0 shadow-sm rounded-3 h-100 admin-dashboard-panel-border admin-quick-actions-card">
                    <div class="card-header bg-white border-bottom py-3 fw-semibold">
                        <i class="fas fa-bolt text-primary me-2"></i>Azioni rapide
                    </div>
                    <div class="card-body">
                        <div class="admin-quick-actions flex-column">
                            <div class="admin-quick-actions-row">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-users me-2"></i>Elenco utenti
                                </a>
                                <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Crea nuovo evento
                                </a>
                            </div>
                            <div class="admin-quick-actions-row mt-2">
                                <a href="{{ route('admin.users.index', ['registrations' => 'pending']) }}" class="btn btn-outline-warning">
                                    <i class="fas fa-user-clock me-2"></i>Iscrizioni in attesa
                                </a>
                                <a href="{{ route('admin.newsletter.create') }}" class="btn btn-outline-info">
                                    <i class="fas fa-envelope me-2"></i>Invia newsletter
                                </a>
                            </div>
                            <div class="admin-quick-actions-row mt-2">
                                <a href="{{ route('admin.common-event.form') }}" class="btn btn-outline-dark">
                                    <i class="fas fa-random me-2"></i>Trova evento in comune
                                </a>
                            </div>
                        </div>

                        <div class="p-3 border rounded-3 bg-light admin-visibility-sections-box mt-3">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                <div class="fw-semibold">
                                    <i class="fas fa-toggle-on text-primary me-1"></i>Visibilità sezioni
                                </div>
                                <small class="text-muted">Non cancella nulla: solo nasconde/mostra.</small>
                            </div>

                            <div class="d-grid gap-2">
                                <form method="POST" action="{{ route('admin.site-settings.feature.toggle', ['featureKey' => 'mercatino']) }}" class="d-flex align-items-center justify-content-between gap-2 flex-wrap admin-feature-toggle-form">
                                    @csrf
                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                        <input class="form-check-input admin-feature-toggle-input" type="checkbox" role="switch" id="admin_toggle_mercatino" {{ ($featureMercatinoEnabled ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="admin_toggle_mercatino">
                                            <i class="fas fa-store me-1"></i>Mercatino
                                        </label>
                                    </div>
                                    @if(($featureMercatinoEnabled ?? true))
                                        <span class="badge bg-success">ATTIVO</span>
                                    @else
                                        <span class="badge bg-secondary">NASCOSTO</span>
                                    @endif
                                </form>

                                <form method="POST" action="{{ route('admin.site-settings.feature.toggle', ['featureKey' => 'chat_salottino']) }}" class="d-flex align-items-center justify-content-between gap-2 flex-wrap admin-feature-toggle-form">
                                    @csrf
                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                        <input class="form-check-input admin-feature-toggle-input" type="checkbox" role="switch" id="admin_toggle_chat_salottino" {{ ($featureChatSalottinoEnabled ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="admin_toggle_chat_salottino">
                                            <i class="fas fa-comments me-1"></i>Salottino chat
                                        </label>
                                    </div>
                                    @if(($featureChatSalottinoEnabled ?? true))
                                        <span class="badge bg-success">ATTIVO</span>
                                    @else
                                        <span class="badge bg-secondary">NASCOSTO</span>
                                    @endif
                                </form>

                                <form method="POST" action="{{ route('admin.site-settings.feature.toggle', ['featureKey' => 'albums_foto']) }}" class="d-flex align-items-center justify-content-between gap-2 flex-wrap admin-feature-toggle-form">
                                    @csrf
                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                        <input class="form-check-input admin-feature-toggle-input" type="checkbox" role="switch" id="admin_toggle_albums_foto" {{ ($featureAlbumsFotoEnabled ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="admin_toggle_albums_foto">
                                            <i class="fas fa-images me-1"></i>Album foto
                                        </label>
                                    </div>
                                    @if(($featureAlbumsFotoEnabled ?? true))
                                        <span class="badge bg-success">ATTIVO</span>
                                    @else
                                        <span class="badge bg-secondary">NASCOSTO</span>
                                    @endif
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 h-100 admin-dashboard-panel-border">
                    <div class="card-header bg-white border-bottom py-3 fw-semibold">
                        <i class="fas fa-toolbox text-secondary me-2"></i>Strumenti
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.groups.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-users-cog me-2"></i>Gruppi
                            </a>
                            <a href="{{ route('admin.users.gallery') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-images me-2"></i>Galleria avatar utenti
                            </a>
                            <a href="{{ route('admin.newsletter.stats') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-chart-bar me-2"></i>Statistiche newsletter
                            </a>
                            <a href="{{ route('admin.mail-test') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-paper-plane me-2"></i>Test e-mail
                            </a>
                            <a href="{{ route('chat.index') }}" class="btn btn-outline-dark" target="_blank" rel="noopener">
                                <i class="fas fa-comments me-2"></i>Apri chat
                                @if(!($featureChatSalottinoEnabled ?? true))
                                    <span class="ms-1 badge bg-secondary">OFF per utenti</span>
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.admin-feature-toggle-input').forEach(function (input) {
                input.addEventListener('change', function () {
                    var form = input.closest('form');
                    if (form) form.submit();
                });
            });
        });
    </script>
@endsection
