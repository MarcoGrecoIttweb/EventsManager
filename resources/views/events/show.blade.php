@extends('layouts.app')

@section('title', $event->title . ' - EventSite')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">{{ $event->title }}</h2>
                    </div>
                    {{-- Cover Image --}}
                    @if($event->cover_image)
                        <div class="mb-4">
                            <img src="{{ Storage::disk('public')->url($event->cover_image) }}" alt="{{ $event->title }}"
                                 class="img-fluid rounded shadow" style="max-height: 400px; width: 100%; object-fit: cover;">
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

                    <div class="card-body">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-primary fs-6">
                                <i class="fas fa-calendar"></i>
                                {{ $event->date->format('d/m/Y H:i') }}
                            </span>
                                <span class="badge bg-secondary fs-6">
                                <i class="fas fa-users"></i>
                                {{ $event->participants_count }}
                                    @if($event->max_participants)
                                        / {{ $event->max_participants }}
                                    @endif
                            </span>
                            </div>

                            <div class="mb-3">
                                <h5><i class="fas fa-map-marker-alt"></i> Località</h5>
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
                                                    $currentUserParticipation = $event->participants()->where('user_id', auth()->id())->first();
                                                    if ($currentUserParticipation) {
                                                        $currentUserGuestsCount = $currentUserParticipation->pivot->guests_count;
                                                    }
                                                @endphp

                                                <form action="{{ route('events.cancel', $event) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="fas fa-times"></i> Lascia Evento
                                                    </button>
                                                </form>

                                                {{-- Indicatore partecipazione --}}
                                                <div class="alert alert-success ms-3 mb-0 py-2 d-flex align-items-center">
                                                    <i class="fas fa-check-circle fa-lg me-2"></i>
                                                    <div>
                                                        <strong>Sei iscritto a questo evento!</strong>
                                                        @if($currentUserGuestsCount > 0)
                                                            <br><small>Porti con te {{ $currentUserGuestsCount }} ospite{{ $currentUserGuestsCount > 1 ? 'i' : '' }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <form action="{{ route('events.participate', $event) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-{{ $event->isFull() ? 'secondary' : 'success' }} btn-lg"
                                                        {{ $event->isFull() ? 'disabled' : '' }}>
                                                        <i class="fas fa-{{ $event->isFull() ? 'lock' : 'check' }}"></i>
                                                        {{ $event->isFull() ? 'Evento al completo' : 'Partecipa all\'evento' }}
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
                                            @if($comment->user->photo)
                                                <img src="{{ Storage::disk('public')->url($comment->user->photo) }}"
                                                     alt="{{ $comment->user->name }}"
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
                                                    <a href="{{ route('profile.show', $comment->user) }}" class="text-decoration-none">
                                                        {{ $comment->user->nickname }}
                                                    </a>
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
                                                @if(auth()->id() === $comment->user_id)
                                                    <a href="{{ route('comments.edit', $comment) }}"
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="Modifica commento">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif

                                                {{-- Pulsante eliminazione (proprietario o admin) --}}
                                                @if(auth()->id() === $comment->user_id || auth()->user()->isAdmin())
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
                            <h5 class="mb-0">
                                <i class="fas fa-users"></i> Partecipanti
                            </h5>
                            <div>
                <span class="badge bg-{{ $event->isFull() ? 'warning' : 'light text-dark' }}">
                    {{ $event->participants_count }}
                    @if($event->max_participants)
                        / {{ $event->max_participants }}
                        @if($event->isFull())
                            <i class="fas fa-lock ms-1"></i>
                        @else
                            <i class="fas fa-user-plus ms-1"></i>
                        @endif
                    @endif
                </span>
                                @if($event->allow_guests && ($event->participants_count - $event->real_participants_count) > 0)
                                    <span class="badge bg-success ms-1">
                        +{{ $event->participants_count - $event->real_participants_count }} ospiti
                    </span>
                                @endif
                            </div>
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
                        @if($event->participants->count() > 0)
                            <div class="list-group">
                                @foreach($event->participants as $participant)
                                    @php
                                        $currentUserIsParticipant = auth()->check() && auth()->id() === $participant->id;
                                        $canAddMoreGuests = $currentUserIsParticipant && $event->canAddMoreGuests($participant);
                                        $hasGuests = $participant->pivot->guests_count > 0;
                                    @endphp

                                    <div class="list-group-item d-flex justify-content-between align-items-center"
                                         id="participant-{{ $participant->id }}">
                                        <div>
                                            <a href="{{ route('profile.show', $participant) }}" class="text-decoration-none">
                                                <i class="fas fa-user"></i> {{ $participant->nickname }}
                                            </a>
                                            @if($hasGuests)
                                                <span class="badge bg-success ms-2">+{{ $participant->pivot->guests_count }}</span>
                                            @endif
                                            @if($currentUserIsParticipant)
                                                <span class="badge bg-primary ms-1">Tu</span>
                                            @endif
                                        </div>

                                        @auth
                                            @if($currentUserIsParticipant && $event->allow_guests)
                                                <div class="btn-group btn-group-sm">
                                                    <form action="{{ route('events.add-guest', $event) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-success btn-sm"
                                                                title="Porta un amico"
                                                            {{ !$canAddMoreGuests || $event->isFull() ? 'disabled' : '' }}>
                                                            <i class="fas fa-user-plus"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('events.remove-guest', $event) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-warning btn-sm"
                                                                title="Togli un amico"
                                                            {{ !$hasGuests ? 'disabled' : '' }}>
                                                            <i class="fas fa-user-minus"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        @endauth
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">Nessun partecipante ancora.</p>
                        @endif

                        {{-- Informazioni ospiti --}}
                        @if($event->allow_guests)
                            <div class="mt-3 p-3 bg-light rounded">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle text-info me-2"></i>
                                    <div>
                                        <small class="text-muted">
                                            È possibile portare fino a <strong>{{ $event->max_guests_per_user }}</strong> ospiti per partecipante.
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
    <script src="https://cdn.tiny.cloud/1/bklljwbpvidz9oqemanmswdq49st98dpznthjvl77p3rfaf1/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            tinymce.init({
                selector: '#commentContent',
                plugins: 'advlist autolink lists link image charmap preview anchor',
                toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link',
                menubar: false,
                height: 250,
                branding: false,
                statusbar: true,
                placeholder: 'Scrivi il tuo commento qui...',
                content_style: `
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                font-size: 14px;
                line-height: 1.6;
            }
        `,
                setup: function (editor) {
                    editor.on('change', function () {
                        editor.save();
                    });

                    editor.on('init', function() {
                        console.log('TinyMCE inizializzato con successo');
                    });
                }
            });

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
        .highlight-comment {
            background-color: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
            transition: background-color 2s ease;
        }

        /* Migliora l'aspetto dell'editor */
        .tox-tinymce {
            border-radius: 8px !important;
            border: 2px solid #e9ecef !important;
            transition: border-color 0.3s ease;
        }

        .tox-tinymce:focus-within {
            border-color: #0d6efd !important;
        }

        .tox-toolbar {
            background: #f8f9fa !important;
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
