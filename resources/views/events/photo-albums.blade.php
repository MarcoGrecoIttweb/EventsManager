@extends('layouts.app')

@section('title', 'Album foto Eventi - Excursio')

@section('sidebar_after_my_events')
    @php
        $monkeyCam = 'upload_immagini/scimmia-con-la-macchina-fotografica-159377341354.jpg';
    @endphp
    @if(file_exists(public_path($monkeyCam)))
        <div class="card card-sidebar mb-3" style="border: 2px solid #000;">
            <div class="card-header py-2" style="background: rgba(0,0,0,0.06); border-bottom: 1px solid rgba(0,0,0,0.25);">
                <small class="fw-bold">
                    <i class="fas fa-images me-1"></i> Album foto
                </small>
            </div>
            <div class="card-body p-2 text-center">
                <img
                    src="{{ asset($monkeyCam) }}"
                    alt="Scimmietta con macchina fotografica"
                    style="max-width: 100%; height: auto; max-height: 210px; object-fit: contain;"
                    class="rounded bg-white p-1"
                >
            </div>
        </div>
    @endif
@endsection

@section('content')
    <style>
        .photo-albums-list .list-group-item {
            border: 2px solid #B8860B; /* giallo ocra */
        }
        .photo-albums-list .list-group-item + .list-group-item {
            border-top-width: 0; /* evita doppio bordo tra i box */
        }
        .photo-albums-pagination .pagination .page-link {
            border: 2px solid #868e96; /* grigio più evidente */
            color: #343a40;
        }
        .photo-albums-pagination .pagination .page-item.active .page-link {
            border-color: #6c757d;
        }
        .photo-albums-pagination .pagination .page-item.disabled .page-link {
            border-color: #adb5bd;
        }
    </style>
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h1 class="h3 mb-1 d-flex align-items-center gap-2 flex-wrap">
                    <i class="fas fa-images"></i> Album foto Eventi
                </h1>
            </div>
            <a href="{{ route('home') }}" class="btn btn-success text-white" style="border: 2px solid #B8860B;">
                <i class="fas fa-home"></i> Home
            </a>
        </div>

        <form method="GET" action="{{ route('photo-albums.index') }}" class="mb-3">
            <div class="input-group" style="max-width: 520px;">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="search"
                       name="q"
                       class="form-control"
                       placeholder="Cerca per titolo album…"
                       value="{{ $q ?? '' }}"
                       autocomplete="off">
                <button type="submit" class="btn btn-primary">
                    Cerca
                </button>
                @if(!empty($q))
                    <a href="{{ route('photo-albums.index') }}" class="btn btn-outline-secondary">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        @if($events->count() === 0)
            <div class="alert alert-light border shadow-sm">
                Nessun evento con album foto disponibile al momento.
            </div>
        @else
            <div class="list-group shadow-sm photo-albums-list">
                @foreach($events as $event)
                    @php
                        $albumUrl = $event->google_album_url;
                    @endphp
                    @if($albumUrl)
                        <div class="list-group-item">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div class="min-w-0">
                                    <div class="fw-semibold text-truncate">
                                        <a href="{{ route('events.show', $event) }}" class="text-decoration-none">
                                            {{ $event->title }}
                                        </a>
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fas fa-calendar"></i>
                                        {{ $event->italian_event_date ?? ($event->date ? $event->date->format('d/m/Y H:i') : '') }}
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 flex-shrink-0">
                                    @if(auth()->check() && auth()->user()->isAdmin())
                                        <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-pen"></i> Modifica
                                        </a>
                                        <form
                                            action="{{ route('photo-albums.destroy', $event) }}"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Vuoi davvero cancellare il link dell’album foto per questo evento?');"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="fas fa-trash"></i> Cancella
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ $albumUrl }}" class="btn btn-primary btn-sm" target="_blank" rel="noopener noreferrer">
                                        <i class="fas fa-external-link-alt"></i> Apri album
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            @if(method_exists($events, 'links'))
                <div class="d-flex justify-content-center mt-4 photo-albums-pagination">
                    {{ $events->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection

