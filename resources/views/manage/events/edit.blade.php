@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Modifica Evento</h1>
        <a href="{{ route('manage.events.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Torna alla lista
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('manage.events.update', $event) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Dettagli Evento</h5>
            </div>
            <div class="card-body">
                {{-- Titolo --}}
                <div class="mb-3">
                    <label for="title" class="form-label">Titolo <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $event->title) }}" maxlength="120" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Data --}}
                <div class="mb-3">
                    <label for="date" class="form-label">Data e Ora <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $event->date->format('Y-m-d\TH:i')) }}" required>
                    @error('date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Città --}}
                <div class="mb-3">
                    <label for="city" class="form-label">Città <span class="text-danger">*</span></label>
                    <input type="text" name="city" id="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $event->city) }}" maxlength="15" required>
                    @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Luogo --}}
                <div class="mb-3">
                    <label for="venue" class="form-label">Nome Locale/Luogo</label>
                    <input type="text" name="venue" id="venue" class="form-control @error('venue') is-invalid @enderror" value="{{ old('venue', $event->dove) }}" maxlength="50">
                    @error('venue')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Indirizzo --}}
                <div class="mb-3">
                    <label for="address" class="form-label">Indirizzo <span class="text-danger">*</span></label>
                    <input type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $event->address) }}" maxlength="50" required>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Costo --}}
                <div class="mb-3">
                    <label for="cost" class="form-label">Costo (&euro;)</label>
                    <input type="number" name="cost" id="cost" class="form-control @error('cost') is-invalid @enderror" value="{{ old('cost', $event->costo) }}" step="0.01" min="0">
                    @error('cost')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="deadline" class="form-label">Scadenza Iscrizioni</label>
                            <input type="datetime-local" name="deadline" id="deadline" class="form-control @error('deadline') is-invalid @enderror" value="{{ old('deadline', $event->deadline ? $event->deadline->format('Y-m-d\TH:i') : '') }}">
                            <small class="form-text text-muted">Lascia vuoto per nessuna scadenza</small>
                            @error('deadline')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
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

                {{-- Descrizione --}}
                <div class="mb-3">
                    <label for="description" class="form-label">Descrizione <span class="text-danger">*</span></label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="10">{{ old('description', $event->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Partecipanti e Ospiti</h5>
            </div>
            <div class="card-body">
                {{-- Max Partecipanti --}}
                <div class="mb-3">
                    <label for="max_participants" class="form-label">Numero massimo partecipanti</label>
                    <input type="number" name="max_participants" id="max_participants" class="form-control @error('max_participants') is-invalid @enderror" value="{{ old('max_participants', $event->max_participants) }}" min="1">
                    <div class="form-text">Lascia vuoto per partecipanti illimitati.</div>
                    @error('max_participants')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Consenti Ospiti --}}
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="allow_guests" id="allow_guests" class="form-check-input" value="1" {{ old('allow_guests', $event->allow_guests) ? 'checked' : '' }}>
                        <label for="allow_guests" class="form-check-label">Consenti ospiti</label>
                    </div>
                    @error('allow_guests')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Max Ospiti per Utente --}}
                <div class="mb-3" id="max_guests_wrapper" style="{{ old('allow_guests', $event->allow_guests) ? '' : 'display: none;' }}">
                    <label for="max_guests_per_user" class="form-label">Numero massimo ospiti per utente</label>
                    <input type="number" name="max_guests_per_user" id="max_guests_per_user" class="form-control @error('max_guests_per_user') is-invalid @enderror" value="{{ old('max_guests_per_user', $event->max_guests_per_user) }}" min="1">
                    @error('max_guests_per_user')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Immagine di Copertina</h5>
            </div>
            <div class="card-body">
                @if($event->cover_image_url)
                    <div class="mb-3">
                        <label class="form-label">Immagine attuale</label>
                        <div>
                            <img src="{{ $event->cover_image_url }}" alt="Copertina evento" class="img-thumbnail" style="max-height: 200px;">
                        </div>
                    </div>
                @endif

                <div class="mb-3">
                    <label for="cover_image" class="form-label">{{ $event->cover_image_url ? 'Sostituisci immagine di copertina' : 'Immagine di copertina' }}</label>
                    <input type="file" name="cover_image" id="cover_image" class="form-control @error('cover_image') is-invalid @enderror" accept="image/*">
                    <div class="form-text">Formati accettati: JPG, PNG, GIF, WebP. Dimensione massima consigliata: 2MB.</div>
                    @error('cover_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('manage.events.index') }}" class="btn btn-outline-secondary">Annulla</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Salva Modifiche
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.tiny.cloud/1/{{ config('services.tinymce.api_key') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // TinyMCE
        tinymce.init({
            selector: '#description',
            language: 'it',
            height: 400,
            menubar: false,
            plugins: 'lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
            toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | removeformat | help',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px; }',
        });

        // Toggle ospiti
        const allowGuestsCheckbox = document.getElementById('allow_guests');
        const maxGuestsWrapper = document.getElementById('max_guests_wrapper');

        allowGuestsCheckbox.addEventListener('change', function () {
            maxGuestsWrapper.style.display = this.checked ? '' : 'none';
            if (!this.checked) {
                document.getElementById('max_guests_per_user').value = '';
            }
        });
    });
</script>
@endsection
