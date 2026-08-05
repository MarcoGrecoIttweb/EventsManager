@extends('layouts.app')

@section('title', 'Profili - Excursio')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="mb-0"><i class="fas fa-users"></i> Profili</h2>
        <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-home"></i> Home
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="width: 80px;">Foto</th>
                            <th scope="col">Username</th>
                            <th scope="col">Nome</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <a href="{{ route('profile.show', $user) }}" class="text-decoration-none">
                                        @if($user->photo_url)
                                            <img src="{{ $user->photo_url }}" alt="Foto di {{ $user->username }}"
                                                 class="rounded-circle"
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary text-white"
                                                  style="width: 40px; height: 40px; font-size: 16px; font-weight: 600;">
                                                {{ strtoupper(substr($user->nome ?: $user->username, 0, 1)) }}
                                            </span>
                                        @endif
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('profile.show', $user) }}" class="fw-semibold text-decoration-none">
                                        {{ $user->username }}
                                    </a>
                                </td>
                                <td>{{ $user->nome }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    Nessun utente attivo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3 text-muted small">
        Totale utenti attivi: <strong>{{ $users->count() }}</strong>
    </div>
</div>
@endsection