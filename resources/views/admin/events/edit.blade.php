@extends('layouts.app')

@section('title', 'Modifica Evento - Admin')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card admin-event-edit-shell">
                    <div class="card-header bg-warning text-white">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <h4 class="mb-0">
                                <i class="fas fa-edit"></i> Modifica Evento: {{ $event->title }}
                            </h4>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <form action="{{ route('admin.events.duplicate', $event) }}" method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Creare una copia di questo evento? Il titolo avrà suffisso (copia), stessi testi e immagini; le iscrizioni non verranno copiate.');">
                                    @csrf
                                    <button type="submit" class="btn btn-dark btn-sm">
                                        <i class="fas fa-copy"></i> Duplica
                                    </button>
                                </form>
                                <a href="{{ route('home') }}" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-left"></i> Torna alla Lista
                                </a>
                            </div>
                        </div>
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
                        @if($event->is_past_event)
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Evento concluso.</strong>
                                Impostando la <strong>data e ora</strong> nel futuro e salvando, l'evento viene <strong>ripubblicato</strong> e torna in <strong>homepage</strong> (Prossimi eventi). Se la scadenza iscrizioni era nel passato, viene allineata alla nuova data evento così le iscrizioni possono riaprirsi.
                            </div>
                        @endif
                        <form id="admin-event-edit-form" action="{{ route('admin.events.update', $event) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            {{-- Riga 1: Titolo, Data evento, Scadenza iscrizioni --}}
                            <div class="row g-2 event-edit-row-primary">
                                <div class="col-12 col-lg-4">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Titolo Evento *</label>
                                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                                               id="title" name="title" value="{{ old('title', $event->title) }}" required>
                                        @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <div class="mb-3">
                                        <label for="date" class="form-label">Data Evento *</label>
                                        <input type="datetime-local" class="form-control @error('date') is-invalid @enderror"
                                               id="date" name="date" value="{{ old('date', $event->date->format('Y-m-d\TH:i')) }}" required>
                                        @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <div class="mb-3">
                                        <label for="deadline" class="form-label text-primary-emphasis">Scadenza Iscrizioni</label>
                                        <input type="datetime-local" class="form-control border border-2 border-primary @error('deadline') is-invalid @enderror"
                                               id="deadline" name="deadline" value="{{ old('deadline', $event->deadline ? $event->deadline->format('Y-m-d\TH:i') : '') }}">
                                        <small class="form-text text-muted">Vuoto x nessuna scadenza</small>
                                        @error('deadline')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Riga 2: Nome locale, Indirizzo, Città, Prezzo --}}
                            <div class="row g-2 event-edit-row-place">
                                <div class="col-12 col-sm-6 col-xl-3">
                                    <div class="mb-3">
                                        <label for="venue" class="form-label text-primary-emphasis">Nome locale</label>
                                        <input type="text" class="form-control border border-2 border-primary @error('venue') is-invalid @enderror"
                                               id="venue" name="venue" value="{{ old('venue', $event->dove) }}" placeholder="es. Ristorante Da Mario">
                                        @error('venue')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-xl-3">
                                    <div class="mb-3">
                                        <label for="address" class="form-label text-primary-emphasis">Indirizzo *</label>
                                        <input type="text" class="form-control border border-2 border-primary @error('address') is-invalid @enderror"
                                               id="address" name="address" value="{{ old('address', $event->address) }}" required>
                                        @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-xl-3">
                                    <div class="mb-3">
                                        <label for="city" class="form-label text-primary-emphasis">Città *</label>
                                        <input type="text" class="form-control border border-2 border-primary @error('city') is-invalid @enderror"
                                               id="city" name="city" value="{{ old('city', $event->city) }}" required>
                                        @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-xl-3">
                                    <div class="mb-3">
                                        <label for="cost" class="form-label text-primary-emphasis mb-1">Prezzo (€)</label>
                                        <input type="number" step="0.01" min="0" class="form-control border border-2 border-primary @error('cost') is-invalid @enderror"
                                               id="cost" name="cost" value="{{ old('cost', $event->costo) }}" placeholder="0.00">
                                        @error('cost')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 admin-event-riassunto-block">
                                <label for="incipit" class="form-label text-primary-emphasis">Presentazione Riassuntiva</label>
                                <textarea class="form-control border border-2 border-primary @error('incipit') is-invalid @enderror"
                                          id="incipit" name="incipit" rows="2" maxlength="500"
                                          placeholder="Breve testo di anteprima mostrato nelle liste (max 500 caratteri). Se vuoto viene usata la descrizione.">{{ old('incipit', $event->incipit) }}</textarea>
                                @error('incipit')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Immagini: anteprima copertina, poi file + link Google sulla stessa riga --}}
                            <div class="mb-2">
                                <label class="form-label">Immagine di copertina</label>
                                <div class="mb-2" id="coverPreviewBox" style="{{ $event->cover_image_url ? '' : 'display:none;' }}">
                                    <div style="height: 140px; display:flex; align-items:center; justify-content:center; overflow:hidden; background:#f8f9fa;">
                                        <img
                                            id="coverPreviewImg"
                                            src="{{ $event->cover_image_url ?? '' }}"
                                            alt="Cover"
                                            class="img-thumbnail"
                                            style="max-height: 100%; max-width: 100%; width:auto; height:auto; object-fit: contain; background:#fff;"
                                        >
                                    </div>
                                    @if($event->cover_image_url)
                                        <div class="form-check mt-2">
                                            <input type="checkbox" class="form-check-input admin-remove-cover-checkbox" id="remove_cover" name="remove_cover" value="1">
                                            <label class="form-check-label text-danger" for="remove_cover">
                                                Rimuovi immagine copertina
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            Per <strong>sostituire</strong> la copertina basta scegliere un nuovo file qui sotto (non serve spuntare “Rimuovi”).
                                        </small>
                                    @endif
                                </div>
                                <input type="hidden" name="cover_image_selected" id="cover_image_selected" value="0">
                                <div class="row g-2 align-items-start event-edit-cover-google-row">
                                    <div class="col-12 col-md-6 col-xl-3">
                                        <label for="cover_image" class="form-label small text-muted mb-1">Scegli file copertina</label>
                                        <input type="file" class="form-control form-control-sm border border-2 border-primary @error('cover_image') is-invalid @enderror"
                                               id="cover_image" name="cover_image" accept="image/*">
                                        @error('cover_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-3">
                                        <label for="google_album_url" class="form-label text-primary-emphasis small mb-1" title="Opzionale, sopra i commenti nell'evento.">Link album Google Foto</label>
                                        <input type="url"
                                               inputmode="url"
                                               autocomplete="off"
                                               class="form-control form-control-sm border border-2 border-primary @error('google_album_url') is-invalid @enderror"
                                               id="google_album_url"
                                               name="google_album_url"
                                               value="{{ old('google_album_url', $event->url_galleria) }}"
                                               placeholder="https://photos.app.goo.gl/..."
                                               title="Opzionale, sopra i commenti nell'evento.">
                                        @error('google_album_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-6 col-md-4 col-xl-3">
                                        <div class="admin-max-participants-box mb-0">
                                            <label for="max_participants" class="form-label text-primary-emphasis small mb-1">Max. Partecipanti</label>
                                            <input type="number" class="form-control form-control-sm border border-2 border-primary @error('max_participants') is-invalid @enderror"
                                                   id="max_participants" name="max_participants"
                                                   value="{{ old('max_participants', $event->max_participants) }}" min="1">
                                            @error('max_participants')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4 col-xl-3" id="max_guests_container" style="{{ old('allow_guests', $event->allow_guests) ? '' : 'display: none;' }}">
                                        <div class="admin-max-guests-box mb-0">
                                            <label for="max_guests_per_user" class="form-label small mb-1">Max Ospiti</label>
                                            @php
                                                $guestsEnabled = (bool) old('allow_guests', $event->allow_guests);
                                                $maxGuestsVal = old('max_guests_per_user', $event->max_guests_per_user);
                                                $maxGuestsVal = max(1, (int) ($maxGuestsVal ?: 3));
                                            @endphp
                                            <input type="number" class="form-control form-control-sm @error('max_guests_per_user') is-invalid @enderror"
                                                   id="max_guests_per_user" name="max_guests_per_user"
                                                   value="{{ $guestsEnabled ? $maxGuestsVal : 3 }}" min="1" max="10"
                                                   @if(!$guestsEnabled) disabled @endif>
                                            @error('max_guests_per_user')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Sotto la riga file: Evento attivo, Permetti ospiti, Elenco partecipanti visibile --}}
                                <div class="mt-2 pt-2 border-top">
                                    <div class="d-flex flex-wrap align-items-center gap-3 gap-md-4 event-edit-event-switches-row">
                                        <div class="form-check form-switch mb-0">
                                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                                {{ old('is_active', $event->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">Evento attivo</label>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="checkbox" class="form-check-input" id="allow_guests" name="allow_guests" value="1"
                                                {{ old('allow_guests', $event->allow_guests) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="allow_guests">Permetti ospiti</label>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="checkbox" class="form-check-input" id="elenco_visibile" name="elenco_visibile" value="1"
                                                {{ old('elenco_visibile', $event->elenco_visibile) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="elenco_visibile">Visualizza partecipanti</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 admin-event-descrizione-block">
                                <label for="description" class="form-label">Descrizione *</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="8">{{ old('description', $event->description) }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Utilizza l'editor per formattare la descrizione dell'evento.
                                </small>
                            </div>

                            <div class="alert alert-info py-2 mb-0">
                                <span class="d-inline-flex flex-wrap align-items-baseline gap-1 small">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Informazioni evento:</strong>
                                    <span class="text-muted">/</span>
                                    <span>Creato da: <strong>{{ $event->user->name }}</strong> ({{ $event->user->nickname }})</span>
                                    <span class="text-muted">/</span>
                                    <span>Partecipanti attuali: <strong>{{ $event->participants_count }}</strong></span>
                                    <span class="text-muted">/</span>
                                    <span>Commenti: <strong>{{ $event->comments->count() }}</strong></span>
                                    <span class="text-muted">/</span>
                                    <span>Immagini: <strong>{{ $event->images_count }}</strong></span>
                                </span>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('home') }}" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-times"></i> Annulla
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save"></i> Aggiorna Evento
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
            var editForm = document.getElementById('admin-event-edit-form');
            if (editForm) {
                editForm.addEventListener('submit', function () {
                    if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances.description) {
                        CKEDITOR.instances.description.updateElement();
                    }
                });
            }

            // Toggle per gli ospiti
            const allowGuestsCheckbox = document.getElementById('allow_guests');
            const maxGuestsContainer = document.getElementById('max_guests_container');

            const maxGuestsInput = document.getElementById('max_guests_per_user');

            function toggleMaxGuests() {
                if (!allowGuestsCheckbox || !maxGuestsContainer) return;
                var on = allowGuestsCheckbox.checked;
                maxGuestsContainer.style.display = on ? 'block' : 'none';
                if (maxGuestsInput) {
                    maxGuestsInput.disabled = !on;
                    if (on && (!maxGuestsInput.value || parseInt(maxGuestsInput.value, 10) < 1)) {
                        maxGuestsInput.value = '3';
                    }
                }
            }

            if (allowGuestsCheckbox) {
                allowGuestsCheckbox.addEventListener('change', toggleMaxGuests);
                toggleMaxGuests();
            }

            // Anteprima nuove immagini gallery rimossa (campo non più presente)

            // UX copertina: se spunti "Rimuovi", nasconde anteprima e mette focus su scegli file
            const removeCover = document.getElementById('remove_cover');
            const coverPreviewBox = document.getElementById('coverPreviewBox');
            const coverPreviewImg = document.getElementById('coverPreviewImg');
            const coverInput = document.getElementById('cover_image');
            if (removeCover && coverPreviewBox && coverInput) {
                removeCover.addEventListener('change', function () {
                    if (this.checked) {
                        coverPreviewBox.style.display = 'none';
                        coverInput.classList.add('border', 'border-warning');
                        coverInput.focus();
                    } else {
                        coverPreviewBox.style.display = '';
                        coverInput.classList.remove('border', 'border-warning');
                    }
                });
            }

            // Anteprima copertina: scegli file → mostra subito e deseleziona "Rimuovi"
            if (coverInput && coverPreviewBox && coverPreviewImg) {
                coverInput.addEventListener('change', function () {
                    const file = this.files && this.files[0] ? this.files[0] : null;
                    if (!file) return;
                    if (!file.type || !file.type.startsWith('image/')) return;

                    const coverSelected = document.getElementById('cover_image_selected');
                    if (coverSelected) coverSelected.value = '1';

                    const reader = new FileReader();
                    reader.onload = function (e) {
                        coverPreviewImg.src = e.target.result;
                        coverPreviewBox.style.display = '';
                        coverInput.classList.remove('border', 'border-warning');
                        if (removeCover) removeCover.checked = false;
                    };
                    reader.readAsDataURL(file);
                });
            }
        });
    </script>
    <style>
        /* Titolo + Data: bordo marrone */
        #admin-event-edit-form #title.form-control,
        #admin-event-edit-form #date.form-control {
            border: 2px solid #8B4513 !important;
        }
        #admin-event-edit-form #title.form-control:focus,
        #admin-event-edit-form #date.form-control:focus {
            border-color: #8B4513 !important;
            box-shadow: 0 0 0 0.2rem rgba(139, 69, 19, 0.25);
        }

        /* Data, costo, scadenza, città: larghezza proporzionata al contenuto */
        #admin-event-edit-form #date.form-control,
        #admin-event-edit-form #deadline.form-control {
            max-width: 13rem;
            width: 100%;
        }
        #admin-event-edit-form #city.form-control {
            max-width: 12rem;
            width: 100%;
        }
        #admin-event-edit-form #cost.form-control {
            max-width: 6rem;
            width: 100%;
        }

        /* In griglia a più colonne gli input usano tutta la colonna */
        #admin-event-edit-form .event-edit-row-primary #date.form-control,
        #admin-event-edit-form .event-edit-row-primary #deadline.form-control {
            max-width: 100%;
        }
        #admin-event-edit-form .event-edit-row-place #venue.form-control,
        #admin-event-edit-form .event-edit-row-place #address.form-control,
        #admin-event-edit-form .event-edit-row-place #city.form-control {
            max-width: 100%;
        }
        #admin-event-edit-form .event-edit-row-place #cost.form-control {
            max-width: 5rem !important;
            width: 100%;
        }

        /* Riga copertina + Google + max: input a tutta colonna */
        #admin-event-edit-form .event-edit-cover-google-row #cover_image.form-control,
        #admin-event-edit-form .event-edit-cover-google-row #google_album_url.form-control {
            max-width: 100%;
        }

        /* Card esterna "Modifica Evento": bordo verde */
        .admin-event-edit-shell {
            border: 2px solid #198754 !important;
            border-radius: 0.5rem;
        }

        /* Box editor descrizione (CKEditor): bordo blu */
        #admin-event-edit-form .admin-event-descrizione-block {
            border: 2px solid #0d6efd;
            border-radius: 0.375rem;
            padding: 0.75rem 0.85rem;
            background: #fff;
        }

        /* Descrizione: bordo blu (textarea prima dell’inizializzazione CKEditor) */
        #admin-event-edit-form #description.form-control {
            border: 2px solid #0d6efd !important;
        }
        #admin-event-edit-form #description.form-control:focus {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        /* Forza bordo blu anche se Bootstrap sovrascrive */
        #admin-event-edit-form textarea#description {
            border: 2px solid #0d6efd !important;
        }

        /* Rimuovi copertina: bordo grigio sulla casella */
        #admin-event-edit-form .admin-remove-cover-checkbox {
            border: 2px solid #adb5bd !important;
        }
        #admin-event-edit-form .admin-remove-cover-checkbox:focus {
            border-color: #6c757d !important;
            box-shadow: 0 0 0 0.15rem rgba(108, 117, 125, 0.25);
        }

        /* Numero massimo ospiti per partecipante: bordo blu */
        #admin-event-edit-form #max_guests_per_user.form-control {
            border: 2px solid #0d6efd !important;
        }
        #admin-event-edit-form #max_guests_per_user.form-control:focus {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        /* Immagine copertina: bordo blu sul campo file */
        #admin-event-edit-form #cover_image.form-control:not(.is-invalid) {
            border: 2px solid #0d6efd !important;
        }
        #admin-event-edit-form #cover_image.form-control:not(.is-invalid):focus {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        /* Se il campo mantiene classi legacy border-primary, forza blu */
        #admin-event-edit-form input#cover_image {
            border: 2px solid #0d6efd !important;
        }

        /* Box compatti per numeri (max partecipanti / max ospiti) */
        .admin-max-participants-box,
        .admin-max-guests-box {
            border: 0 !important;
            border-radius: 0;
            padding: 0;
            background: transparent;
        }
        .admin-max-participants-box input[type="number"],
        .admin-max-guests-box input[type="number"] {
            max-width: 100%;
            width: 100%;
        }

        /* Modifica evento più compatta (Riassunto e Descrizione restano a spaziatura normale) */
        .admin-event-edit-shell > .card-header {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        .admin-event-edit-shell > .card-body {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }
        #admin-event-edit-form .mb-3:not(.admin-event-riassunto-block):not(.admin-event-descrizione-block) {
            margin-bottom: 0.5rem !important;
        }
        #admin-event-edit-form .row.g-3 {
            --bs-gutter-y: 0.5rem;
        }
    </style>
@endsection
