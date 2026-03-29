@extends('layouts.app')

@section('title', 'Admin - Immagini Utenti')
@section('no_sidebar', '1')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-6 mb-0">
                <i class="fas fa-images"></i> Immagini Utenti Registrati
            </h1>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Torna utenti
            </a>
        </div>

        <div class="card">
            <div class="card-header bg-dark text-white">
                <strong>Galleria utenti ({{ $users->count() }})</strong>
            </div>
            <div class="card-body">
                @if($users->count() > 0)
                    <div class="users-gallery-grid">
                        @foreach($users as $user)
                            <div class="users-gallery-item text-center">
                                <a href="{{ route('profile.show', $user) }}" class="text-decoration-none">
                                    @if($user->photo_url)
                                        <img src="{{ $user->photo_url }}"
                                             alt="{{ $user->username }}"
                                             class="users-gallery-photo mb-1">
                                    @else
                                        <div class="users-gallery-photo bg-secondary d-inline-flex align-items-center justify-content-center mb-1">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                    @endif
                                    <div class="small fw-semibold text-truncate">{{ $user->username }}</div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        Nessun utente trovato.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .users-gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(96px, 1fr));
            gap: 0.75rem;
        }
        .users-gallery-photo {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: 50%;
            border: 1px solid #d0d7de;
            background: #f8f9fa;
        }
    </style>
@endsection
