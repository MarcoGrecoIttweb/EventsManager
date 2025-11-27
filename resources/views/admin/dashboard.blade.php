@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="container-fluid">
        <h1 class="display-4 mb-4"><i class="fas fa-tachometer-alt"></i> Dashboard Admin</h1>

        <div class="row">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-users"></i> Utenti Totali</h5>
                        <h2>{{ $usersCount }}</h2>
                        <a href="{{ route('admin.users.index') }}" class="text-white">Gestisci utenti</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-calendar"></i> Eventi Attivi</h5>
                        <h2>{{ $eventsCount }}</h2>
                        <a href="{{ route('admin.events.index') }}" class="text-white">Gestisci eventi</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-clock"></i> Utenti in Attesa</h5>
                        <h2>{{ $pendingUsers }}</h2>
                        <a href="{{ route('admin.users.index') }}" class="text-white">Approva utenti</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Azioni Rapide</div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Crea Nuovo Evento
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-warning">
                                <i class="fas fa-user-check"></i> Gestisci Utenti in Attesa
                            </a>
                            <a href="{{ route('admin.newsletter.create') }}" class="btn btn-info">
                                <i class="fas fa-envelope"></i> Invia Newsletter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
