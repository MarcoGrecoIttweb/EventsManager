@extends('layouts.app')

@section('title', 'Profilo di ' . $user->nickname)

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        @if($user->photo_url)
                            <img src="{{ $user->photo_url }}"
                                 alt="{{ $user->name }}"
                                 class="rounded-circle mb-3"
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3"
                                 style="width: 150px; height: 150px;">
                                <i class="fas fa-user fa-3x text-white"></i>
                            </div>
                        @endif

                        <h3>{{ $user->nome }} {{ $user->cognome }}</h3>
                        <p class="text-muted">{{ '@' . $user->username }}</p>

                        <span class="badge bg-{{ $user->isAdmin() ? 'danger' : ($user->isOrganizer() ? 'warning' : 'info') }} mb-2">
                            {{ $user->role_name }}
                        </span>

                        @if($user->descr)
                            <p class="card-text mt-2">{{ $user->descr }}</p>
                        @endif

                        @auth
                            @if(auth()->id() === $user->id)
                                <a href="{{ route('profile.edit', $user) }}" class="btn btn-primary mt-3">
                                    <i class="fas fa-edit"></i> Modifica Profilo
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>

                {{-- Info personali --}}
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informazioni</h5>
                    </div>
                    <ul class="list-group list-group-flush">
                        @if($user->sesso)
                            <li class="list-group-item">
                                <i class="fas fa-{{ $user->sesso === 'f' ? 'venus' : 'mars' }}"></i>
                                {{ $user->sesso === 'f' ? 'Donna' : 'Uomo' }}
                            </li>
                        @endif
                        @if($user->residenza)
                            <li class="list-group-item">
                                <i class="fas fa-home"></i> {{ $user->residenza }}
                            </li>
                        @endif
                        @if($user->datanascita)
                            <li class="list-group-item">
                                <i class="fas fa-birthday-cake"></i> {{ $user->datanascita->format('d/m/Y') }}
                            </li>
                        @endif
                        @auth
                            @if($user->mail_visibile || auth()->id() === $user->id || auth()->user()->isAdmin())
                                <li class="list-group-item">
                                    <i class="fas fa-envelope"></i> {{ $user->email }}
                                </li>
                            @endif
                            @if($user->telefono && (auth()->id() === $user->id || auth()->user()->isAdmin()))
                                <li class="list-group-item">
                                    <i class="fas fa-phone"></i> {{ $user->telefono }}
                                </li>
                            @endif
                        @endauth
                        <li class="list-group-item">
                            <i class="fas fa-calendar-check"></i> Iscritto dal {{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/D' }}
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-md-8">
                {{-- Friends section --}}
                @auth
                    @if(auth()->id() !== $user->id)
                        <div class="mb-3">
                            @if(auth()->user()->isFriendOf($user))
                                <form action="{{ route('friends.remove', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-user-minus"></i> Rimuovi dagli amici
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('friends.add', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-user-plus"></i> Aggiungi agli amici
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                @endauth

                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-calendar"></i> Prossimi Eventi
                            <span class="badge bg-primary">{{ $upcomingEvents->count() }}</span>
                        </h4>
                    </div>
                    <div class="card-body">
                        @if($upcomingEvents->count() > 0)
                            <div class="list-group">
                                @foreach($upcomingEvents as $event)
                                    <a href="{{ route('events.show', $event) }}"
                                       class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1">{{ $event->title }}</h5>
                                            <small class="text-muted">
                                                {{ $event->date->format('d/m/Y H:i') }}
                                            </small>
                                        </div>
                                        <p class="mb-1">
                                            <i class="fas fa-map-marker-alt"></i>
                                            {{ $event->city }}
                                            @if($event->dove) - {{ $event->dove }} @endif
                                        </p>
                                        <small class="text-muted">
                                            {{ $event->participants_count }} partecipanti
                                        </small>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5>Nessun evento in programma</h5>
                                <p class="text-muted">
                                    {{ $user->username }} non partecipa a nessun evento futuro.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                @auth
                    @if(auth()->user()->isAdmin() && !$user->isAdmin())
                        <div class="card mt-4">
                            <div class="card-header bg-admin">
                                <h5 class="mb-0 text-white">
                                    <i class="fas fa-shield-alt"></i> Azioni Amministrative
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2 d-md-flex">
                                    @if($user->status === 'pending')
                                        <form action="{{ route('admin.users.approve', $user) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-check"></i> Approva Utente
                                            </button>
                                        </form>
                                    @endif

                                    @if($user->status !== 'banned')
                                        <form action="{{ route('admin.users.ban', $user) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-ban"></i> Banna Utente
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    <style>
        .bg-admin {
            background: linear-gradient(45deg, #6c5ce7, #a29bfe);
        }
    </style>
@endsection
