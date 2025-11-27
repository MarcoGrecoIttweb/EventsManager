@extends('layouts.app')

@section('title', 'Gestione Utenti - Admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="display-4">
                        <i class="fas fa-users-cog"></i> Gestione Utenti
                    </h1>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Torna alla Dashboard
                    </a>
                </div>

                <!-- Statistiche -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title"><i class="fas fa-clock"></i> In Attesa</h5>
                                <h3 class="card-text">{{ $pendingCount }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title"><i class="fas fa-check-circle"></i> Approvati</h5>
                                <h3 class="card-text">{{ $approvedCount }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title"><i class="fas fa-ban"></i> Bannati</h5>
                                <h3 class="card-text">{{ $bannedCount }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabella Utenti -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Lista Utenti</h5>
                    </div>
                    <div class="card-body">
                        @if($users->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Nickname</th>
                                        <th>Email</th>
                                        <th>Stato</th>
                                        <th>Eventi</th>
                                        <th>Registrato il</th>
                                        <th>Azioni</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($users as $user)
                                        <tr>
                                            <td>{{ $user->name }}</td>
                                            <td>
                                                <a href="{{ route('profile.show', $user) }}" target="_blank">
                                                    {{ $user->nickname }}
                                                </a>
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @if($user->status === 'pending')
                                                    <span class="badge bg-warning">In Attesa</span>
                                                @elseif($user->status === 'approved')
                                                    <span class="badge bg-success">Approvato</span>
                                                @else
                                                    <span class="badge bg-danger">Bannato</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $user->events_count }} eventi</span>
                                            </td>
                                            <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @if($user->status === 'pending')
                                                        <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-sm" title="Approva">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if($user->status !== 'banned')
                                                        <form action="{{ route('admin.users.ban', $user) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm" title="Banna">
                                                                <i class="fas fa-ban"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('admin.users.unban', $user) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-warning btn-sm" title="Sbanna">
                                                                <i class="fas fa-unlock"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm"
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
        .btn-group .btn {
            margin-right: 0.25rem;
        }
        .btn-group .btn:last-child {
            margin-right: 0;
        }
    </style>
@endsection
