@extends('layouts.app')

@section('title', 'Ingressi giornalieri - Admin')
@section('no_sidebar', '1')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h4 mb-0 fw-semibold d-flex flex-wrap align-items-center gap-2">
                        <span class="d-inline-flex align-items-center">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Ingressi giornalieri
                        </span>
                        @php
                            $giorniSel = (int) ($days ?? 1);
                        @endphp
                        <span class="badge bg-dark rounded-pill fs-6 fw-semibold"
                              title="Utenti con almeno un ingresso registrato negli ultimi {{ $giorniSel }} {{ $giorniSel === 1 ? 'giorno' : 'giorni' }}">
                            {{ $users->count() }}
                        </span>
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
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i> Lista ingressi — ultimi {{ (int) ($days ?? 1) }} giorni
                            </h5>

                            <form method="GET" action="{{ route('admin.users.logins') }}" class="d-flex align-items-center gap-2">
                                <label for="days" class="small mb-0">Giorni</label>
                                <select id="days" name="days" class="form-select form-select-sm" style="width: 6rem;" onchange="this.form.submit()">
                                    @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}" {{ (int) ($days ?? 1) === $i ? 'selected' : '' }}>
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0 align-middle">
                                        <thead class="table-success">
                                        <tr>
                                            <th colspan="2" class="text-center">
                                                Sito nuovo (Laravel / amici.excursio.org)
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td class="fw-semibold">Visite distinte</td>
                                            <td class="text-end">
                                                <span class="badge bg-success fs-6">{{ number_format((int) ($distinctLaravel ?? 0), 0, ',', '.') }}</span>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0 align-middle">
                                        <thead class="table-primary">
                                        <tr>
                                            <th colspan="2" class="text-center">
                                                Sito vecchio (excursio.org)
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td class="fw-semibold">Visite distinte</td>
                                            <td class="text-end">
                                                <span class="badge bg-primary fs-6">{{ number_format((int) ($distinctLegacy ?? 0), 0, ',', '.') }}</span>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        @if($users->count() > 0)
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
                                        <th>Sito nuovo (Laravel)</th>
                                        <th>Sito vecchio (excursio.org)</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php
                                        $loginsListReturn = request()->fullUrl();
                                    @endphp
                                    @foreach($users as $user)
                                        <tr>
                                            <td class="text-muted d-none d-lg-table-cell">{{ $user->userID }}</td>
                                            <td class="col-foto">
                                                @if($user->photo_url)
                                                    <img src="{{ $user->photo_url }}" alt="{{ $user->nickname }}"
                                                         style="width:32px;height:32px;object-fit:cover;border-radius:50%;border:1px solid rgba(0,0,0,0.15);">
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($user->nickname)
                                                    <a href="{{ route('profile.show', ['user' => $user, 'return' => $loginsListReturn]) }}"
                                                       class="text-decoration-none fw-semibold">
                                                        {{ $user->nickname }}
                                                    </a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>{{ $user->nome ?? '—' }}</td>
                                            <td>{{ $user->cognome ?? '—' }}</td>
                                            <td>
                                                @if($user->status === 'awaiting')
                                                    <span class="badge bg-warning text-dark">In attesa</span>
                                                @elseif($user->status === 'suspended')
                                                    <span class="badge bg-danger">Sospeso</span>
                                                @elseif($user->status === 'approved')
                                                    <span class="badge bg-success">Attivo</span>
                                                @elseif($user->status === 'banned')
                                                    <span class="badge bg-danger">Bannato</span>
                                                @else
                                                    <span class="badge bg-secondary">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($user->last_login_laravel)
                                                    <span class="badge bg-success">{{ \Carbon\Carbon::parse($user->last_login_laravel)->format('d/m/Y H:i') }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($user->last_login_legacy)
                                                    <span class="badge bg-primary">{{ \Carbon\Carbon::parse($user->last_login_legacy)->format('d/m/Y H:i') }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
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
