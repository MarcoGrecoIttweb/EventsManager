@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="container-fluid py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <h1 class="h2 mb-0">
                <i class="fas fa-tachometer-alt text-primary"></i> Dashboard Admin
            </h1>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-home"></i> Vai al sito
            </a>
        </div>

        @if($pendingUsers > 0)
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
                <h5 class="alert-heading"><i class="fas fa-bell me-2"></i>Attenzione: iscrizioni in sospeso</h5>
                <p class="mb-2">
                    {{ $pendingUsers === 1 ? 'È presente 1 nuova iscrizione' : "Sono presenti {$pendingUsers} nuove iscrizioni" }}
                    in attesa della tua approvazione prima che possano accedere al sito.
                </p>
                <a href="{{ route('admin.users.index', ['registrations' => 'pending']) }}" class="btn btn-light btn-sm text-danger fw-semibold">
                    <i class="fas fa-user-check me-1"></i> Vai alle iscrizioni in attesa
                </a>
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-primary text-white">
                    <div class="card-body text-center py-4">
                        <h5 class="card-title"><i class="fas fa-users me-2"></i>Utenti totali</h5>
                        <p class="display-5 fw-bold mb-2">{{ $usersCount }}</p>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm text-primary fw-semibold">
                            Gestisci utenti
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-success text-white">
                    <div class="card-body text-center py-4">
                        <h5 class="card-title"><i class="fas fa-calendar me-2"></i>Eventi pubblicati</h5>
                        <p class="display-5 fw-bold mb-2">{{ $eventsCount }}</p>
                        <a href="{{ route('admin.events.index') }}" class="btn btn-light btn-sm text-success fw-semibold">
                            Gestisci eventi
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-warning text-dark">
                    <div class="card-body text-center py-4">
                        <h5 class="card-title"><i class="fas fa-clock me-2"></i>Utenti in attesa</h5>
                        <p class="display-5 fw-bold mb-2">{{ $pendingUsers }}</p>
                        <a href="{{ route('admin.users.index', ['registrations' => 'pending']) }}" class="btn btn-dark btn-sm">
                            Approva iscrizioni
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3 fw-semibold">
                        <i class="fas fa-bolt text-primary me-2"></i>Azioni rapide
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Crea nuovo evento
                            </a>
                            <a href="{{ route('admin.users.index', ['registrations' => 'pending']) }}" class="btn btn-outline-warning">
                                <i class="fas fa-user-clock me-2"></i>Iscrizioni in attesa
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-users me-2"></i>Elenco utenti
                            </a>
                            <a href="{{ route('admin.newsletter.create') }}" class="btn btn-outline-info">
                                <i class="fas fa-envelope me-2"></i>Invia newsletter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
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
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
