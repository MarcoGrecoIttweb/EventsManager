@extends('layouts.app')

@section('title', 'Modifica Evento - Admin')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
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
                                <a href="{{ route('admin.events.index') }}" class="btn btn-light btn-sm">
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

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="date" class="form-label">Data Evento *</label>
                                        <input type="datetime-local" class="form-control @error('date') is-invalid @enderror"
                                               id="date" name="date" value="{{ old('date', $event->date->format('Y-m-d\TH:i')) }}" required>
                                        @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Titolo Evento *</label>
                                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                                               id="title" name="title" value="{{ old('title', $event->title) }}" required>
                                        @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="city" class="form-label text-primary-emphasis">Città *</label>
                                        <input type="text" class="form-control border border-2 border-primary @error('city') is-invalid @enderror"
                                               id="city" name="city" value="{{ old('city', $event->city) }}" required>
                                        @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="venue" class="form-label text-primary-emphasis">Nome locale</label>
                                        <input type="text" class="form-control border border-2 border-primary @error('venue') is-invalid @enderror"
                                               id="venue" name="venue" value="{{ old('venue', $event->dove) }}" placeholder="es. Ristorante Da Mario">
                                        @error('venue')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="address" class="form-label text-primary-emphasis">Indirizzo *</label>
                                        <input type="text" class="form-control border border-2 border-primary @error('address') is-invalid @enderror"
                                               id="address" name="address" value="{{ old('address', $event->address) }}" required>
                                        @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="cost" class="form-label text-primary-emphasis">Costo (€)</label>
                                        <input type="number" step="0.01" min="0" class="form-control border border-2 border-primary @error('cost') is-invalid @enderror"
                                               id="cost" name="cost" value="{{ old('cost', $event->costo) }}" placeholder="0.00">
                                        <small class="form-text text-muted">Lascia vuoto se gratuito</small>
                                        @error('cost')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="deadline" class="form-label text-primary-emphasis">Scadenza Iscrizioni</label>
                                        <input type="datetime-local" class="form-control border border-2 border-primary @error('deadline') is-invalid @enderror"
                                               id="deadline" name="deadline" value="{{ old('deadline', $event->deadline ? $event->deadline->format('Y-m-d\TH:i') : '') }}">
                                        <small class="form-text text-muted">Lascia vuoto per nessuna scadenza</small>
                                        @error('deadline')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3 pt-4">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="elenco_visibile" name="elenco_visibile" value="1"
                                                {{ old('elenco_visibile', $event->elenco_visibile) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="elenco_visibile">
                                                Elenco partecipanti visibile
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="incipit" class="form-label text-primary-emphasis">Riassunto</label>
                                <textarea class="form-control border border-2 border-primary @error('incipit') is-invalid @enderror"
                                          id="incipit" name="incipit" rows="2" maxlength="500"
                                          placeholder="Breve testo di anteprima mostrato nelle liste (max 500 caratteri). Se vuoto viene usata la descrizione.">{{ old('incipit', $event->incipit) }}</textarea>
                                @error('incipit')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Immagini: Copertina + Gallery subito sotto il Riassunto --}}
                            <div class="row">
                                {{-- Cover Image --}}
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="cover_image" class="form-label">Immagine Cope</label>

                                        <div class="mb-2" id="coverPreviewBox" style="{{ $event->cover_image_url ? '' : 'display:none;' }}">
                                            <div style="height: 200px; display:flex; align-items:center; justify-content:center; overflow:hidden; background:#f8f9fa;">
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
                                                    <input type="checkbox" class="form-check-input" id="remove_cover" name="remove_cover" value="1">
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
                                        <input type="file" class="form-control border border-2 border-primary @error('cover_image') is-invalid @enderror"
                                               id="cover_image" name="cover_image" accept="image/*">
                                        @error('cover_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Gallery Images --}}
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-primary-emphasis">Immagini Gallery</label>

                                        {{-- Immagini esistenti --}}
                                        @if($event->images->count() > 0)
                                            <div class="row mb-3">
                                                @foreach($event->images as $image)
                                                    <div class="col-6 col-md-6 mb-3">
                                                        <div class="card">
                                                            <div style="height: 150px; display:flex; align-items:center; justify-content:center; overflow:hidden; background:#f8f9fa;">
                                                                <img src="{{ Storage::disk('public')->url($image->path) }}" class="card-img-top" style="max-height: 100%; max-width: 100%; width:auto; height:auto; object-fit: contain;">
                                                            </div>
                                                            <div class="card-body text-center">
                                                                <div class="form-check">
                                                                    <input type="checkbox" class="form-check-input"
                                                                           id="delete_image_{{ $image->id }}"
                                                                           name="delete_images[]" value="{{ $image->id }}">
                                                                    <label class="form-check-label text-danger small" for="delete_image_{{ $image->id }}">
                                                                        Elimina
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        {{-- Nuove immagini --}}
                                        <input type="file" class="form-control border border-2 border-primary @error('gallery_images') is-invalid @enderror"
                                               id="gallery_images" name="gallery_images[]" multiple accept="image/*">
                                        @error('gallery_images')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Seleziona nuove immagini da aggiungere alla gallery
                                        </small>

                                        {{-- Anteprima nuove immagini accanto al box file --}}
                                        <div id="imagePreviews" class="row mt-3" style="display: none;"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="google_album_url" class="form-label text-primary-emphasis">Link album Google Foto</label>
                                <input type="url"
                                       inputmode="url"
                                       autocomplete="off"
                                       class="form-control border border-2 border-primary @error('google_album_url') is-invalid @enderror"
                                       id="google_album_url"
                                       name="google_album_url"
                                       value="{{ old('google_album_url', $event->url_galleria) }}"
                                       placeholder="https://photos.app.goo.gl/... oppure https://photos.google.com/...">
                                <small class="form-text text-muted">
                                    Opzionale. Comparirà sopra ai commenti nella pagina pubblica dell’evento.
                                </small>
                                @error('google_album_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Dati partecipanti e stato evento (sopra editor descrizione) --}}
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="max_participants" class="form-label text-primary-emphasis">Max. Partecipanti</label>
                                        <input type="number" class="form-control border border-2 border-primary @error('max_participants') is-invalid @enderror"
                                               id="max_participants" name="max_participants"
                                               value="{{ old('max_participants', $event->max_participants) }}" min="1">
                                        <small class="form-text text-muted">Lascia vuoto per illimitato</small>
                                        @error('max_participants')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="allow_guests" name="allow_guests" value="1"
                                                {{ old('allow_guests', $event->allow_guests) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="allow_guests">
                                                Permetti ospiti
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                                {{ old('is_active', $event->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                Evento attivo
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3" id="max_guests_container" style="{{ old('allow_guests', $event->allow_guests) ? 'display: block;' : 'display: none;' }}">
                                <label for="max_guests_per_user" class="form-label">Numero massimo di ospiti per partecipante</label>
                                @php
                                    $guestsEnabled = (bool) old('allow_guests', $event->allow_guests);
                                    $maxGuestsVal = old('max_guests_per_user', $event->max_guests_per_user);
                                    $maxGuestsVal = max(1, (int) ($maxGuestsVal ?: 3));
                                @endphp
                                <input type="number" class="form-control @error('max_guests_per_user') is-invalid @enderror"
                                       id="max_guests_per_user" name="max_guests_per_user"
                                       value="{{ $guestsEnabled ? $maxGuestsVal : 3 }}" min="1" max="10"
                                       @if(!$guestsEnabled) disabled @endif>
                                @error('max_guests_per_user')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
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

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Informazioni evento:</strong><br>
                                Creato da: <strong>{{ $event->user->name }}</strong> ({{ $event->user->nickname }})<br>
                                Partecipanti attuali: <strong>{{ $event->participants_count }}</strong><br>
                                Commenti: <strong>{{ $event->comments->count() }}</strong><br>
                                Immagini: <strong>{{ $event->images_count }}</strong>
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

            // Anteprima nuove immagini
            const imageInput = document.getElementById('gallery_images');
            const previewContainer = document.getElementById('imagePreviews');

            if (imageInput) {
                imageInput.addEventListener('change', function() {
                    previewContainer.innerHTML = '';
                    previewContainer.style.display = 'none';

                    if (this.files.length > 0) {
                        previewContainer.style.display = 'flex';

                        Array.from(this.files).forEach((file) => {
                            if (file.type.startsWith('image/')) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    const col = document.createElement('div');
                                    col.className = 'col-md-3 mb-3';
                                    col.innerHTML = `
                                <div class="card">
                                    <div style="height: 150px; display:flex; align-items:center; justify-content:center; overflow:hidden; background:#f8f9fa;">
                                        <img src="${e.target.result}" class="card-img-top" style="max-height: 100%; max-width: 100%; width:auto; height:auto; object-fit: contain;">
                                    </div>
                                    <div class="card-body">
                                        <small class="text-muted">${file.name}</small>
                                    </div>
                                </div>
                            `;
                                    previewContainer.appendChild(col);
                                };
                                reader.readAsDataURL(file);
                            }
                        });
                    }
                });
            }

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
@endsection
