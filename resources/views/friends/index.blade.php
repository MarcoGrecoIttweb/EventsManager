@extends('layouts.app')

@section('title', 'I miei amici - Excursio')

@section('content')
<div class="container">
    <h2 class="mb-4"><i class="fas fa-user-friends"></i> I miei amici <span class="badge bg-primary">{{ $friends->count() }}</span></h2>

    @if($friends->count() > 0)
        <div class="row">
            @foreach($friends as $friend)
                <div class="col-md-4 col-lg-3 mb-3">
                    <div class="card h-100 text-center p-3">
                        @if($friend->photo_url)
                            <img src="{{ $friend->photo_url }}" class="rounded-circle mx-auto mb-2"
                                 style="width:70px;height:70px;object-fit:cover;" alt="{{ $friend->username }}">
                        @else
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto mb-2"
                                 style="width:70px;height:70px;">
                                <i class="fas fa-user fa-2x text-white"></i>
                            </div>
                        @endif
                        <h6 class="mb-1">{{ $friend->nome }} {{ $friend->cognome }}</h6>
                        <small class="text-muted">{{ '@' . $friend->username }}</small>
                        <div class="mt-2 d-flex gap-1 justify-content-center flex-wrap">
                            <a href="{{ route('profile.show', $friend) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-user"></i> Profilo
                            </a>
                            <form action="{{ route('friends.remove', $friend) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-user-minus"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
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
