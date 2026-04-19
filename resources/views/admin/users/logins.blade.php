@extends('layouts.app')

@section('title', 'Ingressi giornalieri utenti ult. 10 gg. - Admin')
@section('no_sidebar', '1')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h4 mb-0 fw-semibold">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Ingressi giornalieri utenti ult. 10 gg.
                    </h1>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-users-cog"></i> Gestione utenti
                        </a>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i> Lista ingressi — ultimi 10 giorni
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($events->count() > 0)
                            <div class="table-responsive admin-users-table-wrapper">
                                <table class="table table-striped table-hover table-sm align-middle admin-users-table">
                                    <thead>
                                    <tr>
                                        <th class="d-none d-lg-table-cell">ID</th>
                                        <th class="col-foto">Foto</th>
                                        <th>Nickname</th>
                                        <th>Nome</th>
                                        <th>Cognome</th>
                                        <th>Stato</th>
                                        <th>Ultimo accesso (data e ora)</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($events as $event)
                                        <tr>
                                            <td class="text-muted d-none d-lg-table-cell">{{ $event->user?->userID }}</td>
                                            <td class="col-foto">
                                                @if($event->user && $event->user->photo_url)
                                                    <img src="{{ $event->user->photo_url }}" alt="{{ $event->user->nickname }}"
                                                         style="width:32px;height:32px;object-fit:cover;border-radius:50%;border:1px solid rgba(0,0,0,0.15);">
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            <td>{{ $event->user?->nickname ?? '—' }}</td>
                                            <td>{{ $event->user?->nome ?? '—' }}</td>
                                            <td>{{ $event->user?->cognome ?? '—' }}</td>
                                            <td>
                                                @if($event->user && $event->user->status === 'awaiting')
                                                    <span class="badge bg-warning text-dark">In attesa</span>
                                                @elseif($event->user && $event->user->status === 'suspended')
                                                    <span class="badge bg-danger">Sospeso</span>
                                                @elseif($event->user && $event->user->status === 'approved')
                                                    <span class="badge bg-success">Attivo</span>
                                                @elseif($event->user && $event->user->status === 'banned')
                                                    <span class="badge bg-danger">Bannato</span>
                                                @else
                                                    <span class="badge bg-secondary">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $event->logged_in_at ? $event->logged_in_at->format('d/m/Y H:i') : '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">Nessun accesso registrato.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

