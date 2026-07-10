@extends('layouts.app')

@section('title', 'Elenco Utenti')

@section('content')
    <div class="container-fluid py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <h1 class="h2 mb-0">
                <i class="fas fa-users text-primary"></i> Elenco Utenti
            </h1>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-success btn-sm" onclick="window.print()">
                    <i class="fas fa-print"></i> Stampa
                </button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Torna alla Dashboard
                </a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Username</th>
                                <th>Nome</th>
                                <th>Cognome</th>
                                <th>Email</th>
                                <th>Attivo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($user->photo_url)
                                                <img src="{{ $user->photo_url }}" alt="Avatar"
                                                     class="rounded-circle"
                                                     style="width: 32px; height: 32px; object-fit: cover;">
                                            @else
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary text-white"
                                                      style="width: 32px; height: 32px; font-size: 14px; font-weight: 600;">
                                                    {{ strtoupper(substr($user->nome, 0, 1)) }}
                                                </span>
                                            @endif
                                            <a href="{{ route('profile.show', $user) }}" class="fw-semibold text-decoration-none">
                                                {{ $user->username }}
                                            </a>
                                        </div>
                                    </td>
                                    <td>{{ $user->nome }}</td>
                                    <td>{{ $user->cognome }}</td>
                                    <td>
                                        @if($user->email)
                                            <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusLabels = [
                                                0 => ['label' => 'Sospeso', 'class' => 'bg-warning text-dark'],
                                                1 => ['label' => 'Attivo', 'class' => 'bg-success'],
                                                2 => ['label' => 'Bannato', 'class' => 'bg-danger'],
                                                3 => ['label' => 'In attesa', 'class' => 'bg-secondary'],
                                            ];
                                            $status = $statusLabels[$user->abilitato] ?? ['label' => 'Sconosciuto', 'class' => 'bg-dark'];
                                        @endphp
                                        <span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Nessun utente trovato.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3 text-muted small">
            Totale utenti: <strong>{{ $users->count() }}</strong>
        </div>
    </div>
@endsection