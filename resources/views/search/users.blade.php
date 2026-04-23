@extends('layouts.app')

@section('title', 'Cerca utenti - Excursio')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="mb-0"><i class="fas fa-search"></i> Cerca utenti</h2>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-home"></i> Home
            </a>
            @if(strlen(trim($query)) >= 1)
                <a href="{{ route('users.search') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Torna a Cerca utenti
                </a>
            @endif
        </div>
    </div>

    <form method="GET" action="{{ route('users.search') }}" class="mb-4">
        <div class="input-group">
            <input type="text" name="q" class="form-control" placeholder="Cerca per username, nome o cognome..."
                   value="{{ $query }}" autofocus>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Cerca
            </button>
        </div>
        <small class="text-muted">Inserisci almeno 2 caratteri</small>
    </form>

    @if(strlen(trim($query)) >= 2)
        @if($users->count() > 0)
            <p class="text-muted">{{ $users->count() }} risultat{{ $users->count() === 1 ? 'o' : 'i' }} per "<strong>{{ $query }}</strong>"</p>
            <div class="row">
                @foreach($users as $user)
                    <div class="col-md-4 col-lg-3 mb-3">
                        <div class="card h-100 text-center p-3">
                            @if($user->photo_url)
                                <img src="{{ $user->photo_url }}" class="rounded-circle mx-auto mb-2"
                                     style="width:70px;height:70px;object-fit:cover;" alt="{{ $user->username }}">
                            @else
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto mb-2"
                                     style="width:70px;height:70px;">
                                    <i class="fas fa-user fa-2x text-white"></i>
                                </div>
                            @endif
                            <h6 class="mb-1">{{ $user->nome }} {{ $user->cognome }}</h6>
                            <small class="text-muted">{{ $user->username }}</small>
                            <div class="mt-2 d-flex gap-1 justify-content-center flex-wrap">
                                <a href="{{ route('profile.show', $user) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-user"></i> Profilo
                                </a>
                                @if($user->getKey() !== auth()->id())
                                    @if(auth()->user()->isFriendOf($user))
                                        <form action="{{ route('friends.remove', $user) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-user-minus"></i> Rimuovi
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('friends.add', $user) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-user-plus"></i> Aggiungi
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4 text-muted">
                <i class="fas fa-user-slash fa-2x mb-2"></i>
                <p>Nessun utente trovato per "<strong>{{ $query }}</strong>"</p>
            </div>
        @endif
    @endif
</div>
@endsection
