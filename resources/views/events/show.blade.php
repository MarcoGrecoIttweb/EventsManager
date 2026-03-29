@extends('layouts.app')

@section('title', $event->title . ' - Excursio')

@section('content')
    <div class="container">
        <div class="mb-3">
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Torna alla lista
                    </a>
                @else
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Torna alla lista
                    </a>
                @endif
            @else
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Torna alla lista
                </a>
            @endauth
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <h2 class="mb-0">{{ $event->title }}</h2>
                            @auth
                                @if(auth()->user()->isAdmin() || auth()->id() === $event->id_organizzatore)
                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Modifica evento
                                        </a>
                                    @else
                                        <a href="{{ route('manage.events.edit', $event) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Modifica evento
                                        </a>
                                    @endif
                                @endif
                            @endauth
                        </div>
                    </div>
                    {{-- Cover Image --}}
                    @if($event->cover_image_url)
                        <div class="mb-4 event-cover-frame rounded shadow overflow-hidden">
                            <img
                                src="{{ $event->cover_image_url }}"
                                alt="{{ $event->title }}"
                                class="event-cover-img"
                            >
                        </div>
                    @endif

                    {{-- Gallery --}}
                    @if($event->images->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-images"></i> Gallery
                                    <span class="badge bg-primary">{{ $event->images->count() }}</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($event->images as $image)
                                        <div class="col-md-4 col-lg-3 mb-3">
                                            <a href="{{ Storage::disk('public')->url($image->path) }}" data-lightbox="event-gallery" data-title="{{ $event->title }}">
                                                <img src="{{ Storage::disk('public')->url($image->path) }}" alt="{{ $event->title }}"
                                                     class="img-fluid rounded shadow-sm" style="height: 200px; width: 100%; object-fit: cover;">
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    {{-- Banner evento al completo --}}
                    @if($event->isFull())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                                <div>
                                    <h4 class="alert-heading mb-1">Evento al completo!</h4>
                                    <p class="mb-0">Tutti i posti sono stati occupati. Non è più possibile iscriversi a questo evento.</p>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($event->is_past_event)
                        <div class="alert alert-secondary border-dark mb-0 rounded-0" role="alert">
                            <div class="d-flex flex-wrap align-items-center gap-2 justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-flag-checkered fa-lg"></i>
                                    <div>
                                        <strong>Evento concluso</strong>
                                        <span class="d-block small text-muted mb-0">La data dell'evento è nel passato.</span>
                                    </div>
                                </div>
                                @auth
                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-warning btn-sm flex-shrink-0">
                                            <i class="fas fa-edit"></i> Modifica evento
                                        </a>
                                        <span class="small text-muted d-none d-md-inline">Imposta una nuova data futura per ripristinarlo tra i prossimi eventi.</span>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    @endif

                    <div class="card-body">
                        <div class="mb-4">
                            @php
                                $iscrittiCount = $event->participants_count;
                                $postiTotali = $event->max_participants ? (int) $event->max_participants : null;
                                $postiLiberi = $postiTotali !== null ? max(0, $postiTotali - $iscrittiCount) : null;
                            @endphp
                            <div class="event-meta-stack mb-3">
                                <div class="event-meta-date-box">
                                    <i class="fas fa-calendar"></i>
                                    <span>{{ $event->italian_event_date ?? $event->date->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="event-meta-posti-box">
                                    <i class="fas fa-users"></i>
                                    <span class="event-meta-posti-line">
                                        <span class="event-meta-label-iscritti">Iscritti:</span> <strong>{{ $iscrittiCount }}</strong>
                                        <span class="event-meta-posti-sep"> / </span>
                                        <span class="event-meta-label-liberi">Liberi:</span> <strong>{{ $postiLiberi !== null ? $postiLiberi : '—' }}</strong>
                                        <span class="event-meta-posti-sep"> / </span>
                                        <span class="event-meta-label-totali">Totali:</span> <strong>{{ $postiTotali !== null ? $postiTotali : '—' }}</strong>
                                        @if($postiTotali === null)
                                            <small class="event-meta-posti-hint"> (posti illimitati)</small>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            @if($event->formatted_cost)
                                <div class="mb-3">
                                    <span class="badge bg-success fs-6">
                                        <i class="fas fa-euro-sign"></i> {{ $event->formatted_cost }}
                                    </span>
                                </div>
                            @endif

                            @if($event->deadline)
                                <div class="mb-3">
                                    <span class="badge bg-{{ $event->isRegistrationOpen() ? 'info' : 'danger' }} fs-6">
                                        <i class="fas fa-clock"></i>
                                        Iscrizioni {{ $event->isRegistrationOpen() ? 'entro il' : 'chiuse il' }}
                                        {{ $event->deadline->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            @endif

                            <div class="mb-3">
                                <h5><i class="fas fa-map-marker-alt"></i> Località</h5>
                                @if($event->dove)
                                    <p class="mb-1"><strong>Luogo:</strong> {{ $event->dove }}</p>
                                @endif
                                <p class="mb-1"><strong>Città:</strong> {{ $event->city }}</p>
                                @auth
                                    <p class="mb-0"><strong>Indirizzo:</strong> {{ $event->address }}</p>
                                @else
                                    <p class="text-muted">
                                        <a href="{{ route('login') }}">Accedi</a> per vedere l'indirizzo completo
                                    </p>
                                @endauth
                            </div>

                            <div class="mb-4">
                                <h5><i class="fas fa-info-circle"></i> Descrizione</h5>
                                <div class="event-description">
                                    {!! $event->safe_description !!}
                                </div>
                            </div>
                        </div>

                        @auth
                            @auth
                                @auth
                                    @if(auth()->user()->isApproved())
                                        <div class="d-grid gap-2 d-md-flex">
                                            @if($userParticipating)
                                                @php
                                                    // Calcola il numero di ospiti dell'utente corrente
                                                    $currentUserGuestsCount = 0;
                                                    $currentUserParticipation = $event->participants()->where('utente.userID', auth()->id())->first();
                                                    if ($currentUserParticipation) {
                                                        $currentUserGuestsCount = $currentUserParticipation->pivot->amici ?? 0;
                                                    }
                                                @endphp

                                                <div class="d-flex flex-nowrap gap-2 align-items-stretch event-participation-btns">
                                                    <button type="button" class="btn btn-success" disabled aria-disabled="true">
                                                        <i class="fas fa-check-circle"></i> Partecipo
                                                    </button>
                                                    <form action="{{ route('events.cancel', $event) }}" method="POST" class="mb-0 d-flex align-items-stretch">
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger w-100 h-100">
                                                            <i class="fas fa-times"></i> Toglimi
                                                        </button>
                                                    </form>
                                                </div>
                                                @if($currentUserGuestsCount > 0)
                                                    <small class="text-muted d-block mt-2">Porti con te {{ $currentUserGuestsCount }} ospite{{ $currentUserGuestsCount > 1 ? 'i' : '' }}</small>
                                                @endif
                                            @else
                                                @php
                                                    $cannotJoin = $event->isFull() || !$event->isRegistrationOpen();
                                                    $joinLabel = !$event->isRegistrationOpen() ? 'Iscrizioni chiuse' : ($event->isFull() ? 'Evento al completo' : 'Partecipa all\'evento');
                                                    $joinIcon = $cannotJoin ? 'lock' : 'check';
                                                @endphp
                                                <form action="{{ route('events.participate', $event) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-{{ $cannotJoin ? 'secondary' : 'success' }} btn-lg"
                                                        {{ $cannotJoin ? 'disabled' : '' }}>
                                                        <i class="fas fa-{{ $joinIcon }}"></i>
                                                        {{ $joinLabel }}
                                                    </button>
                                                </form>

                                                @if($event->isFull())
                                                    <div class="alert alert-warning ms-3 mb-0 py-2 d-flex align-items-center">
                                                        <i class="fas fa-users-slash fa-lg me-2"></i>
                                                        <div>
                                                            <strong>Posti esauriti</strong>
                                                            <br><small>Tutti i {{ $event->max_participants }} posti sono occupati</small>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <div class="alert alert-info">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-info-circle fa-lg me-3"></i>
                                            <div>
                                                <strong>Vuoi partecipare?</strong>
                                                <a href="{{ route('login') }}" class="btn btn-primary btn-sm ms-2">Accedi</a>
                                                per iscriverti a questo evento
                                                @if($event->isFull())
                                                    <br><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Attenzione: l'evento è al completo</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endauth
                            @else
                                <div class="alert alert-info">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle fa-lg me-3"></i>
                                        <div>
                                            <strong>Vuoi partecipare?</strong>
                                            <a href="{{ route('login') }}" class="btn btn-primary btn-sm ms-2">Accedi</a>
                                            per iscriverti a questo evento
                                            @if($event->isFull())
                                                <br><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Attenzione: l'evento è al completo</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endauth                        @else
                            <div class="alert alert-info">
                                <a href="{{ route('login') }}" class="btn btn-primary">Accedi</a>
                                per partecipare a questo evento
                            </div>
                        @endauth
                    </div>
                </div>

                <!-- Sezione Commenti -->
                @auth
                    @if(auth()->user()->isApproved())
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-comments"></i> Aggiungi un Commento</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('comments.store', $event) }}" method="POST" id="commentForm">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="commentContent" class="form-label">Il tuo commento</label>
                                        <textarea class="form-control" id="commentContent" name="content"
                                                  rows="5" placeholder="Scrivi il tuo commento..." required></textarea>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i> Puoi usare la formattazione base
                                        </small>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i> Invia Commento
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                @endif
                @endauth

            <!-- Lista Commenti -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-comments"></i> Commenti
                            <span class="badge bg-primary">{{ $comments->count() }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($comments->count() > 0)
                            @foreach($comments as $comment)
                                <div class="mb-4 border-bottom pb-3" id="comment-{{ $comment->id }}">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center">
                                            {{-- Avatar utente --}}
                                            @if($comment->user && $comment->user->photo_url)
                                                <img src="{{ $comment->user->photo_url }}"
                                                     alt="{{ $comment->user->name ?? 'Utente' }}"
                                                     class="rounded-circle me-2"
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2"
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <strong>
                                                    @if($comment->user)
                                                        <a href="{{ route('profile.show', $comment->user) }}" class="text-decoration-none">
                                                            {{ $comment->user->nickname }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">Utente cancellato</span>
                                                    @endif
                                                </strong>
                                                <br>
                                                <small class="text-muted">
                                                    {{ $comment->created_at->diffForHumans() }}
                                                    @if($comment->is_edited)
                                                        • <span class="text-warning" title="Modificato il {{ $comment->edited_at->format('d/m/Y H:i') }}">
                                    <i class="fas fa-edit"></i> modificato
                                </span>
                                                    @endif
                                                </small>
                                            </div>
                                        </div>

                                        {{-- Pulsanti azione --}}
                                        @auth
                                            <div class="btn-group" role="group">
                                                {{-- Pulsante modifica (solo proprietario) --}}
                                                @if(auth()->id() === $comment->id_utente)
                                                    <a href="{{ route('comments.edit', $comment) }}"
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="Modifica commento">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif

                                                {{-- Pulsante eliminazione (proprietario o admin) --}}
                                                @if(auth()->id() === $comment->id_utente || auth()->user()->isAdmin())
                                                    <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                onclick="return confirm('Sei sicuro di voler eliminare questo commento?')"
                                                                title="Elimina commento">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endauth
                                    </div>

                                    {{-- Contenuto del commento --}}
                                    <div class="comment-content">
                                        {!! $comment->safe_content !!}
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted">Nessun commento ancora. Sii il primo a commentare!</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Partecipanti -->
                <div class="card {{ $event->isFull() ? 'border-danger' : '' }}">
                    <div class="card-header {{ $event->isFull() ? 'bg-danger text-white' : 'bg-dark text-white' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 d-flex align-items-center flex-wrap gap-1">
                                <span>
                                    <i class="fas fa-users"></i> Partecipanti
                                </span>
                                <span class="badge rounded-pill bg-light text-dark"
                                      title="Iscritti all'evento più ospiti inseriti (amici)">
                                    {{ $event->participants_count }}
                                </span>
                                @auth
                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('events.print', $event) }}" class="btn btn-sm btn-outline-light ms-1" target="_blank" title="Stampa lista (solo admin)">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    @endif
                                @endauth
                            </h5>
                            @if($event->allow_guests && ($event->participants_count - $event->real_participants_count) > 0)
                                <span class="badge bg-success">
                                    +{{ $event->participants_count - $event->real_participants_count }} ospiti
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- Progress Bar --}}
                        @if($event->max_participants)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Posti occupati</small>
                                    <small class="text-muted">
                                        <strong>{{ $event->participants_count }}</strong> / {{ $event->max_participants }}
                                        ({{ round(($event->participants_count / $event->max_participants) * 100) }}%)
                                    </small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    @php
                                        $percentage = ($event->participants_count / $event->max_participants) * 100;
                                        $progressClass = $percentage >= 100 ? 'bg-danger' : ($percentage >= 80 ? 'bg-warning' : 'bg-success');
                                    @endphp
                                    <div class="progress-bar {{ $progressClass }}"
                                         role="progressbar"
                                         style="width: {{ min($percentage, 100) }}%"
                                         aria-valuenow="{{ $event->participants_count }}"
                                         aria-valuemin="0"
                                         aria-valuemax="{{ $event->max_participants }}">
                                    </div>
                                </div>
                                @if($event->isFull())
                                    <small class="text-danger mt-1 d-block">
                                        <i class="fas fa-exclamation-circle"></i> Tutti i posti sono stati occupati
                                    </small>
                                @elseif($percentage >= 80)
                                    <small class="text-warning mt-1 d-block">
                                        <i class="fas fa-info-circle"></i> Posti quasi esauriti!
                                    </small>
                                @endif
                            </div>
                        @endif

                        {{-- Lista partecipanti --}}
                        @php
                            $canSeeList = $event->elenco_visibile ||
                                (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->getKey() === $event->id_organizzatore));
                        @endphp
                        @if(!$canSeeList)
                            <p class="text-muted"><i class="fas fa-eye-slash"></i> L'elenco dei partecipanti non è visibile per questo evento.</p>
                        @elseif($event->participants->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($event->participants as $participant)
                                    @php
                                        $currentUserIsParticipant = auth()->check() && auth()->id() === $participant->getKey();
                                        $canAddMoreGuests = $currentUserIsParticipant && $event->canAddMoreGuests($participant);
                                        $hasGuests = $participant->pivot->amici > 0;
                                        $ospitiEntries = \App\Support\OspitiGuestStore::decode($participant->pivot->ospiti_inseriti_il ?? null);
                                        $showGuestRows = $hasGuests && ($canSeeList || $currentUserIsParticipant);
                                    @endphp

                                    <div class="mb-2 border rounded overflow-hidden" id="participant-{{ $participant->getKey() }}">
                                        <div class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2 border-0">
                                            <div>
                                                <a href="{{ route('profile.show', $participant) }}" class="text-decoration-none">
                                                    <i class="fas fa-user"></i> {{ $participant->nickname }}
                                                </a>
                                                @if($hasGuests)
                                                    <span class="badge bg-success ms-2">+{{ $participant->pivot->amici }}</span>
                                                @endif
                                                @if($currentUserIsParticipant)
                                                    <span class="badge bg-primary ms-1">Tu</span>
                                                @endif
                                            </div>

                                            @auth
                                                @if($currentUserIsParticipant && $event->allow_guests)
                                                    <form action="{{ route('events.add-guest', $event) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-success btn-sm"
                                                                title="Con il più aggiungi amico"
                                                                aria-label="Con il più aggiungi amico"
                                                            {{ !$canAddMoreGuests || $event->isFull() ? 'disabled' : '' }}>
                                                            <i class="fas fa-user-plus" aria-hidden="true"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endauth
                                        </div>

                                        @if($showGuestRows)
                                            @for($gi = 0; $gi < (int) $participant->pivot->amici; $gi++)
                                                @php
                                                    $gEntry = $ospitiEntries[$gi] ?? ['nome' => '', 'at' => ''];
                                                    $gNome = $gEntry['nome'] ?? '';
                                                    $giOld = old('guest_index');
                                                    $nomeFormError = $errors->has('nome') && $giOld !== null && (int) $giOld === $gi;
                                                    $showNomeForm = ($gNome === '' || $nomeFormError);
                                                @endphp
                                                <div class="list-group-item border-0 border-top bg-light py-2 ps-4 pe-3">
                                                    @if($currentUserIsParticipant && $event->allow_guests)
                                                        @if($showNomeForm)
                                                            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                                                <div class="flex-grow-1" style="min-width: 12rem;">
                                                                    <div class="small text-muted mb-1">
                                                                        Amico di <strong>{{ $participant->nickname }}</strong>
                                                                    </div>
                                                                    <form action="{{ route('events.update-guest-name', $event) }}" method="POST"
                                                                          class="d-flex flex-wrap align-items-center gap-2">
                                                                        @csrf
                                                                        <input type="hidden" name="guest_index" value="{{ $gi }}">
                                                                        <input type="text" name="nome"
                                                                               class="form-control form-control-sm flex-grow-1 @error('nome') is-invalid @enderror"
                                                                               style="min-width: 10rem; max-width: 18rem;"
                                                                               placeholder="Nominativo"
                                                                               value="{{ $nomeFormError ? old('nome', '') : '' }}"
                                                                               maxlength="120"
                                                                               autocomplete="name">
                                                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                                                            Salva
                                                                        </button>
                                                                    </form>
                                                                    @error('nome')
                                                                        @if($nomeFormError)
                                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                                        @endif
                                                                    @enderror
                                                                </div>
                                                                <form action="{{ route('events.remove-guest', $event) }}" method="POST" class="d-inline flex-shrink-0"
                                                                      onsubmit="return confirm('Rimuovere questo amico dall\'elenco?');">
                                                                    @csrf
                                                                    <input type="hidden" name="guest_index" value="{{ $gi }}">
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                            title="Con il meno togli amico"
                                                                            aria-label="Con il meno togli amico">
                                                                        <i class="fas fa-user-minus" aria-hidden="true"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        @else
                                                            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                                                <span class="small">
                                                                    <span class="fw-semibold">{{ $gNome }}</span>
                                                                    <span class="text-muted"> — amico di {{ $participant->nickname }}</span>
                                                                </span>
                                                                <form action="{{ route('events.remove-guest', $event) }}" method="POST" class="d-inline flex-shrink-0"
                                                                      onsubmit="return confirm('Rimuovere questo amico dall\'elenco?');">
                                                                    @csrf
                                                                    <input type="hidden" name="guest_index" value="{{ $gi }}">
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                            title="Con il meno togli amico"
                                                                            aria-label="Con il meno togli amico">
                                                                        <i class="fas fa-user-minus" aria-hidden="true"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        @endif
                                                    @else
                                                        <span class="small text-muted">
                                                            <span class="{{ $gNome !== '' ? 'fw-semibold text-dark' : '' }}">{{ $gNome !== '' ? $gNome : 'Ospite' }}</span>
                                                            <span class="text-muted"> — amico di {{ $participant->nickname }}</span>
                                                        </span>
                                                    @endif
                                                </div>
                                            @endfor
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">Nessun partecipante ancora.</p>
                        @endif

                        {{-- Invita un amico --}}
                        @auth
                            @if($userParticipating && auth()->user()->friends()->count() > 0)
                                <div class="mt-3 p-3 bg-light rounded">
                                    <h6><i class="fas fa-envelope"></i> Invita un amico</h6>
                                    <form action="{{ route('events.invite', $event) }}" method="POST" class="d-flex gap-2">
                                        @csrf
                                        <select name="friend_id" class="form-select form-select-sm">
                                            @foreach(auth()->user()->friends()->orderBy('nome')->get() as $friend)
                                                @if(!$event->participants->contains('userID', $friend->userID))
                                                    <option value="{{ $friend->getKey() }}">{{ $friend->nome }} {{ $friend->cognome }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @endauth

                        {{-- Informazioni ospiti --}}
                        @if($event->allow_guests)
                            <div class="mt-3 p-3 bg-light rounded">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle text-info me-2"></i>
                                    <div>
                                        <small class="text-muted">
                                            Con <strong>+</strong> aggiungi un amico: sotto compare <strong>Amico di</strong> (il tuo nickname) e il campo per il <strong>nominativo</strong> (con <strong>Salva</strong> solo finché il nome è vuoto). Accanto a ogni amico c’è <strong>−</strong> per togliere proprio quello. Il <strong>+</strong> resta sulla tua riga. Massimo <strong>{{ $event->max_guests_per_user }}</strong> ospiti.
                                            @if($event->isFull())
                                                <br><span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Evento al completo - non è possibile aggiungere nuovi ospiti</span>
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Informazioni Organizzatore -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Organizzatore</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">
                            <i class="fas fa-user"></i>
                            <a href="{{ route('profile.show', $event->user) }}">
                                {{ $event->user->nickname }}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @parent
    @include('partials.ckeditor4-description', ['field' => 'commentContent', 'height' => 250])

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Scroll al commento se specificato
            @if(session('scrollTo'))
            const commentId = '{{ session('scrollTo') }}';
            const commentElement = document.getElementById(commentId);
            if (commentElement) {
                setTimeout(() => {
                    commentElement.scrollIntoView({ behavior: 'smooth' });
                    commentElement.classList.add('highlight-comment');
                }, 500);
            }
            @endif
        });
    </script>

    <style>
        .event-cover-frame {
            position: relative;
            width: 100%;
            aspect-ratio: 16 / 9;
            height: auto;
            max-height: 400px;
            background: #f1f3f5;
        }

        .event-cover-img {
            display: block;
            position: absolute;
            inset: 0;
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            object-position: center;
        }

        @media (max-width: 767.98px) {
            .event-cover-frame {
                max-height: 240px;
            }
        }

        .event-meta-stack {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            max-width: 100%;
        }

        .event-meta-date-box,
        .event-meta-posti-box {
            display: flex;
            align-items: flex-start;
            gap: 0.35rem;
            width: 100%;
            box-sizing: border-box;
            border-radius: 0.375rem;
            padding: 0.65rem 0.9rem;
            font-size: 1rem;
            line-height: 1.45;
            border: 2px solid #000;
        }

        .event-meta-date-box > i,
        .event-meta-posti-box > i {
            margin-top: 0.15rem;
            flex-shrink: 0;
        }

        .event-meta-posti-line {
            flex: 1;
            min-width: 0;
        }

        .event-meta-date-box {
            background-color: #0d6efd;
            color: #fff;
        }

        .event-meta-posti-box {
            background-color: #dee2e6;
            color: #212529;
        }

        .event-meta-posti-box strong {
            color: #1a1d20;
        }

        .event-meta-posti-box > i {
            color: #495057;
        }

        .event-meta-label-iscritti {
            color: #e0113c;
            font-weight: 700;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.6);
        }

        .event-meta-label-liberi {
            color: #008f4a;
            font-weight: 700;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.6);
        }

        .event-meta-label-totali {
            color: #e65100;
            font-weight: 700;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.6);
        }

        .event-meta-posti-hint {
            color: #495057;
            font-weight: normal;
        }

        .event-meta-posti-sep {
            color: #6c757d;
        }

        .event-participation-btns > .btn,
        .event-participation-btns > form {
            flex: 1 1 0;
            min-width: 0;
        }

        .highlight-comment {
            background-color: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
            transition: background-color 2s ease;
        }

        .cke_chrome {
            border-radius: 8px !important;
            border: 2px solid #e9ecef !important;
        }
        .cke_focus .cke_chrome {
            border-color: #0d6efd !important;
        }
         .comment-content {
             line-height: 1.6;
             font-size: 14px;
         }

        .comment-content p {
            margin-bottom: 0.8rem;
        }

        .comment-content strong, .comment-content b {
            font-weight: 600;
        }

        .comment-content em, .comment-content i {
            font-style: italic;
        }

        .comment-content u {
            text-decoration: underline;
        }

        .comment-content a {
            color: #0d6efd;
            text-decoration: none;
        }

        .comment-content a:hover {
            text-decoration: underline;
        }

        .comment-content ul, .comment-content ol {
            margin-left: 1.5rem;
            margin-bottom: 0.8rem;
        }

        .comment-content li {
            margin-bottom: 0.3rem;
        }

        .comment-content code {
            background-color: #f8f9fa;
            padding: 0.1rem 0.3rem;
            border-radius: 0.25rem;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }

        .comment-content pre {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.375rem;
            overflow-x: auto;
            margin-bottom: 0.8rem;
        }

        .comment-content pre code {
            background: none;
            padding: 0;
        }
    </style>
@endsection
