@extends('layouts.app')

@section('title', 'Crea Nuovo Evento - Organizzatore')

@section('content')
    <style>
        .event-create-col-date {
            flex: 0 0 auto;
            max-width: 16.5rem;
        }
        /* Copertina + link Google + switch: una riga su desktop */
        .event-create-media-row {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 0.5rem 0.75rem;
        }
        @media (min-width: 992px) {
            .event-create-media-row {
                flex-wrap: nowrap;
            }
        }
        .event-create-media-cover {
            flex: 0 0 auto;
            width: 11rem;
            max-width: 100%;
        }
        .event-create-media-cover .form-control {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .event-create-media-google {
            flex: 1 1 9rem;
            min-width: 0;
            max-width: 100%;
        }
        @media (min-width: 992px) {
            .event-create-media-google {
                max-width: 15rem;
            }
        }
        .event-create-media-google .form-control {
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
        }
        .event-create-media-switches {
            flex: 0 1 auto;
            min-width: 0;
        }
    </style>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-plus"></i> Crea Nuovo Evento
                        </h4>
                    </div>
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <strong>Errore caricamento:</strong>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('manage.events.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label for="title" class="form-label text-primary-emphasis">Titolo Evento *</label>
                                <input type="text" class="form-control border border-2 border-primary @error('title') is-invalid @enderror"
                                       id="title" name="title" value="{{ old('title') }}" required>
                                @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-2 align-items-end">
                                <div class="col-12 col-md-auto col-lg-2 event-create-col-date">
                                    <div class="mb-3">
                                        <label for="date" class="form-label text-primary-emphasis">Data Evento *</label>
                                        <input type="datetime-local" class="form-control border border-2 border-primary @error('date') is-invalid @enderror"
                                               id="date" name="date" value="{{ old('date') }}" required>
                                        @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="mb-3">
                                        <label for="venue" class="form-label text-primary-emphasis">Nome locale</label>
                                        <input type="text" class="form-control border border-2 border-primary @error('venue') is-invalid @enderror"
                                               id="venue" name="venue" value="{{ old('venue') }}" placeholder="es. Ristorante Da Mario" maxlength="35">
                                        @error('venue')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-5">
                                    <div class="mb-3">
                                        <label for="address" class="form-label text-primary-emphasis">Indirizzo *</label>
                                        <input type="text" class="form-control border border-2 border-primary @error('address') is-invalid @enderror"
                                               id="address" name="address" value="{{ old('address') }}" required maxlength="35">
                                        @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-2">
                                    <div class="mb-3">
                                        <label for="civico" class="form-label text-primary-emphasis">Numero civico</label>
                                        <input type="text"
                                               class="form-control border border-2 border-primary @error('civico') is-invalid @enderror"
                                               id="civico"
                                               name="civico"
                                               value="{{ old('civico') }}"
                                               maxlength="10"
                                               placeholder="es. 12">
                                        @error('civico')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 align-items-end">
                                <div class="col-6 col-lg-2">
                                    <div class="mb-3">
                                        <label for="city" class="form-label text-primary-emphasis">Città *</label>
                                        <input type="text" class="form-control border border-2 border-primary @error('city') is-invalid @enderror"
                                               id="city" name="city" value="{{ old('city') }}" required maxlength="35">
                                        @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-6 col-lg-2">
                                    <div class="mb-3">
                                        <label for="cost" class="form-label text-primary-emphasis">Costo (€)</label>
                                        <input type="number" step="0.01" min="0" class="form-control border border-2 border-primary @error('cost') is-invalid @enderror"
                                               id="cost" name="cost" value="{{ old('cost') }}" placeholder="0.00">
                                        @error('cost')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-lg-3">
                                    <div class="mb-3">
                                        <label for="deadline" class="form-label text-primary-emphasis">Scadenza Iscrizioni</label>
                                        <input type="datetime-local" class="form-control border border-2 border-primary @error('deadline') is-invalid @enderror"
                                               id="deadline" name="deadline" value="{{ old('deadline') }}">
                                        @error('deadline')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div id="max_participants_cell" class="col-6 col-sm-6 {{ old('allow_guests', true) ? 'col-lg-3' : 'col-lg-5' }}">
                                    <div class="mb-3">
                                        <label for="max_participants" class="form-label text-primary-emphasis">Max partecipanti</label>
                                        <input type="number" class="form-control border border-2 border-primary @error('max_participants') is-invalid @enderror"
                                               id="max_participants" name="max_participants"
                                               value="{{ old('max_participants') }}" min="1">
                                        @error('max_participants')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div id="max_guests_container" class="col-6 col-sm-6 col-lg-2 {{ old('allow_guests', true) ? '' : 'd-none' }}">
                                    <div class="mb-3">
                                        <label for="max_guests_per_user" class="form-label text-primary-emphasis">Max ospiti</label>
                                        <input type="number" class="form-control border border-2 border-primary @error('max_guests_per_user') is-invalid @enderror"
                                               id="max_guests_per_user" name="max_guests_per_user"
                                               value="{{ old('max_guests_per_user', 3) }}" min="1" max="10">
                                        @error('max_guests_per_user')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="event-create-media-row mb-3">
                                <div class="event-create-media-cover">
                                    <label for="cover_image" class="form-label text-primary-emphasis mb-1">Immagine di copertina</label>
                                    <input type="file" class="form-control border border-2 border-primary @error('cover_image') is-invalid @enderror"
                                           id="cover_image" name="cover_image" accept="image/*">
                                    @error('cover_image')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="event-create-media-google">
                                    <label for="google_album_url" class="form-label text-primary-emphasis mb-1" title="Opzionale, sopra i commenti nell'evento.">Link album Google Foto</label>
                                    <input type="url"
                                           inputmode="url"
                                           autocomplete="off"
                                           class="form-control border border-2 border-primary @error('google_album_url') is-invalid @enderror"
                                           id="google_album_url"
                                           name="google_album_url"
                                           value="{{ old('google_album_url') }}"
                                           placeholder="https://photos.app.goo.gl/..."
                                           title="Opzionale, sopra i commenti nell'evento.">
                                    @error('google_album_url')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div id="event_create_switches_cell" class="event-create-media-switches d-flex flex-wrap align-items-center gap-2 gap-lg-3 pb-1">
                                    <div class="form-check form-switch mb-0">
                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                            {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label text-primary-emphasis text-nowrap small" for="is_active">
                                            Evento attivo
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input type="hidden" name="elenco_visibile" value="1">
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input type="checkbox" class="form-check-input" id="allow_guests" name="allow_guests" value="1"
                                            {{ old('allow_guests', true) ? 'checked' : '' }}>
                                        <label class="form-check-label text-primary-emphasis text-nowrap small" for="allow_guests">
                                            Permetti ospiti
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="incipit" class="form-label text-primary-emphasis">Presentazione Riassuntiva</label>
                                <textarea class="form-control border border-2 border-primary @error('incipit') is-invalid @enderror"
                                          id="incipit" name="incipit" rows="2" maxlength="500"
                                          placeholder="Breve testo di anteprima mostrato nelle liste (max 500 caratteri). Se vuoto viene usata la descrizione.">{{ old('incipit') }}</textarea>
                                @error('incipit')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label text-primary-emphasis">Descrizione *</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="8">{{ old('description') }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Utilizza l'editor per formattare la descrizione dell'evento.
                                </small>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('manage.events.index') }}" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-times"></i> Annulla
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Crea Evento
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('partials.ckeditor4-description', ['height' => 400])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle per gli ospiti
            const allowGuestsCheckbox = document.getElementById('allow_guests');
            const maxGuestsContainer = document.getElementById('max_guests_container');
            const maxParticipantsCell = document.getElementById('max_participants_cell');
            function toggleMaxGuests() {
                var on = allowGuestsCheckbox.checked;
                if (maxGuestsContainer) {
                    maxGuestsContainer.classList.toggle('d-none', !on);
                }
                if (maxParticipantsCell) {
                    maxParticipantsCell.classList.remove('col-lg-3', 'col-lg-5');
                    maxParticipantsCell.classList.add(on ? 'col-lg-3' : 'col-lg-5');
                }
            }

            allowGuestsCheckbox.addEventListener('change', toggleMaxGuests);
            toggleMaxGuests();
        });
    </script>
@endsection
