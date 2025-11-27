@extends('layouts.app')

@section('title', 'Modifica Evento - Admin')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">
                                <i class="fas fa-edit"></i> Modifica Evento: {{ $event->title }}
                            </h4>
                            <a href="{{ route('admin.events.index') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left"></i> Torna alla Lista
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.events.update', $event) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
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
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="date" class="form-label">Data e Ora *</label>
                                        <input type="datetime-local" class="form-control @error('date') is-invalid @enderror"
                                               id="date" name="date" value="{{ old('date', $event->date->format('Y-m-d\TH:i')) }}" required>
                                        @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="city" class="form-label">Città *</label>
                                        <input type="text" class="form-control @error('city') is-invalid @enderror"
                                               id="city" name="city" value="{{ old('city', $event->city) }}" required>
                                        @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Indirizzo Completo *</label>
                                        <input type="text" class="form-control @error('address') is-invalid @enderror"
                                               id="address" name="address" value="{{ old('address', $event->address) }}" required>
                                        @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
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

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="max_participants" class="form-label">Numero Massimo Partecipanti</label>
                                        <input type="number" class="form-control @error('max_participants') is-invalid @enderror"
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

                            <div class="mb-3" id="max_guests_container" style="{{ $event->allow_guests ? 'display: block;' : 'display: none;' }}">
                                <label for="max_guests_per_user" class="form-label">Numero massimo di ospiti per partecipante</label>
                                <input type="number" class="form-control @error('max_guests_per_user') is-invalid @enderror"
                                       id="max_guests_per_user" name="max_guests_per_user"
                                       value="{{ old('max_guests_per_user', $event->max_guests_per_user) }}" min="1" max="10">
                                @error('max_guests_per_user')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Cover Image --}}
                            <div class="mb-3">
                                <label for="cover_image" class="form-label">Immagine Copertina</label>

                                @if($event->cover_image)
                                    <div class="mb-2">
                                        <img src="{{ Storage::disk('public')->url($event->cover_image) }}" alt="Cover" class="img-thumbnail" style="max-height: 200px;">
                                        <div class="form-check mt-2">
                                            <input type="checkbox" class="form-check-input" id="remove_cover" name="remove_cover" value="1">
                                            <label class="form-check-label text-danger" for="remove_cover">
                                                Rimuovi immagine copertina
                                            </label>
                                        </div>
                                    </div>
                                @endif

                                <input type="file" class="form-control @error('cover_image') is-invalid @enderror"
                                       id="cover_image" name="cover_image" accept="image/*">
                                @error('cover_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Gallery Images --}}
                            <div class="mb-3">
                                <label class="form-label">Immagini Gallery</label>

                                {{-- Immagini esistenti --}}
                                @if($event->images->count() > 0)
                                    <div class="row mb-3">
                                        @foreach($event->images as $image)
                                            <div class="col-md-3 mb-3">
                                                <div class="card">
                                                    <img src="{{ Storage::disk('public')->url($image->path) }}" class="card-img-top" style="height: 150px; object-fit: cover;">
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
                                <input type="file" class="form-control @error('gallery_images') is-invalid @enderror"
                                       id="gallery_images" name="gallery_images[]" multiple accept="image/*">
                                @error('gallery_images')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Seleziona nuove immagini da aggiungere alla gallery
                                </small>
                            </div>

                                {{-- Nuove immagini --}}
                                <input type="file" class="form-control @error('gallery_images') is-invalid @enderror"
                                       id="gallery_images" name="gallery_images[]" multiple accept="image/*">
                                @error('gallery_images')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Seleziona nuove immagini da aggiungere alla gallery
                                </small>
                            </div>

                            <div id="imagePreviews" class="row mb-3" style="display: none;"></div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Informazioni evento:</strong><br>
                                Creato da: <strong>{{ $event->user->name }}</strong> ({{ $event->user->nickname }})<br>
                                Creato il: {{ $event->created_at->format('d/m/Y H:i') }}<br>
                                Partecipanti attuali: <strong>{{ $event->participants_count }}</strong><br>
                                Commenti: <strong>{{ $event->comments->count() }}</strong><br>
                                Immagini: <strong>{{ $event->images_count }}</strong>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('admin.events.index') }}" class="btn btn-secondary me-md-2">
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

@section('scripts')
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/bklljwbpvidz9oqemanmswdq49st98dpznthjvl77p3rfaf1/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inizializza TinyMCE per la descrizione
            tinymce.init({
                selector: '#description',
                plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
                toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | code help',
                menubar: 'edit view insert format tools table help',
                height: 400,
                branding: false,
                statusbar: true,
                promotion: false,
                placeholder: 'Descrivi il tuo evento in dettaglio...',
                content_style: `
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                font-size: 14px;
                line-height: 1.6;
            }
            h1, h2, h3 { margin-top: 1rem; margin-bottom: 0.5rem; }
            p { margin-bottom: 0.8rem; }
            ul, ol { margin-left: 1.5rem; margin-bottom: 0.8rem; }
        `,
                setup: function (editor) {
                    editor.on('change', function () {
                        editor.save();
                    });

                    // Carica il contenuto esistente nell'editor
                    editor.on('init', function() {
                        editor.setContent(document.getElementById('description').value);
                    });
                }
            });

            // Toggle per gli ospiti
            const allowGuestsCheckbox = document.getElementById('allow_guests');
            const maxGuestsContainer = document.getElementById('max_guests_container');

            function toggleMaxGuests() {
                maxGuestsContainer.style.display = allowGuestsCheckbox.checked ? 'block' : 'none';
            }

            allowGuestsCheckbox.addEventListener('change', toggleMaxGuests);

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
                                    <img src="${e.target.result}" class="card-img-top" style="height: 150px; object-fit: cover;">
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
        });
    </script>
@endsection
@endsection
