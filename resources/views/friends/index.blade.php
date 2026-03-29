@extends('layouts.app')

@section('title', 'I miei amici - Excursio')

@section('content')
<div class="container">
    <h2 class="mb-4"><i class="fas fa-user-friends"></i> I miei amici <span class="badge bg-primary">{{ $friends->count() }}</span></h2>

    @if($friends->count() > 0)
        <div class="card">
            <div class="list-group list-group-flush">
                @foreach($friends as $friend)
                    <div class="list-group-item d-flex align-items-center justify-content-between gap-3 py-3">
                        <div class="d-flex align-items-center gap-3 min-w-0">
                            @if($friend->photo_url)
                                <img src="{{ $friend->photo_url }}" class="rounded-circle"
                                     style="width:56px;height:56px;object-fit:cover;flex-shrink:0;" alt="{{ $friend->username }}">
                            @else
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                                     style="width:56px;height:56px;flex-shrink:0;">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                            @endif
                            <div class="min-w-0">
                                <div class="fw-semibold text-truncate">{{ $friend->nome }} {{ $friend->cognome }}</div>
                                <small class="text-muted text-truncate d-block">{{ $friend->username }}</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <a href="{{ route('profile.show', $friend) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-user"></i> Profilo
                            </a>
                            <form action="{{ route('friends.remove', $friend) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Rimuovi dagli amici">
                                    <i class="fas fa-user-minus"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
            <h5>Non hai ancora amici aggiunti</h5>
            <a href="{{ route('users.search') }}" class="btn btn-primary mt-2">
                <i class="fas fa-search"></i> Cerca utenti
            </a>
        </div>
    @endif
</div>
@endsection
