@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card manage-event-edit-shell">
                    <div class="card-header bg-warning text-white">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <h4 class="mb-0">
                                <i class="fas fa-edit"></i> Modifica Evento: {{ $event->title }}
                            </h4>
                            <a href="{{ route('manage.events.index') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left"></i> Torna ai miei eventi
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <strong>Correggi i seguenti errori:</strong>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form id="manage-event-edit-form" action="{{ route('manage.events.update', $event) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

        <div class="card mb-3 manage-event-details-card">
            <div class="card-header">
                <h5 class="mb-0">Dettagli Evento</h5>
            </div>
            <div class="card-body">
                {{-- Riga 1: Titolo, Data evento, Scadenza iscrizioni --}}
                <div class="row g-2 event-edit-row-primary">
                    <div class="col-12 col-lg-4">
                        <div class="mb-3">
                            <label for="title" class="form-label text-primary-emphasis">Titolo Evento <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control border border-2 border-primary @error('title') is-invalid @enderror" value="{{ old('title', $event->title) }}" maxlength="120" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="mb-3">
                            <label for="date" class="form-label text-primary-emphasis">Data Evento <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="date" id="date" class="form-control border border-2 border-primary @error('date') is-invalid @enderror" value="{{ old('date', $event->date->format('Y-m-d\TH:i')) }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="mb-3">
                            <label for="deadline" class="form-label text-primary-emphasis">Scadenza Iscrizioni</label>
                            <input type="datetime-local" name="deadline" id="deadline" class="form-control border border-2 border-primary @error('deadline') is-invalid @enderror" value="{{ old('deadline', $event->deadline ? $event->deadline->format('Y-m-d\TH:i') : '') }}">
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
                            <input type="text" name="venue" id="venue" class="form-control form-control-sm border border-2 border-primary @error('venue') is-invalid @enderror" value="{{ old('venue', $event->dove) }}" maxlength="35">
                            @error('venue')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-4">
                        <div class="mb-3">
                            <label for="address" class="form-label text-primary-emphasis">Indirizzo <span class="text-danger">*</span></label>
                            <input type="text" name="address" id="address" class="form-control form-control-sm border border-2 border-primary @error('address') is-invalid @enderror" value="{{ old('address', $event->address) }}" maxlength="35" required>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-1">
                        <div class="mb-3">
                            <label for="civico" class="form-label text-primary-emphasis">Numero civico</label>
                            <input type="text" name="civico" id="civico"
                                   class="form-control form-control-sm border border-2 border-primary @error('civico') is-invalid @enderror"
                                   value="{{ old('civico', $event->civico) }}" maxlength="10"
                                   style="max-width: 6.5rem;">
                            @error('civico')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-2">
                        <div class="mb-3">
                            <label for="city" class="form-label text-primary-emphasis">Città <span class="text-danger">*</span></label>
                            <input type="text" name="city" id="city" class="form-control form-control-sm border border-2 border-primary @error('city') is-invalid @enderror" value="{{ old('city', $event->city) }}" maxlength="35" required>
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-2">
                        <div class="mb-3">
                            <label for="cost" class="form-label text-primary-emphasis mb-1">Prezzo (&euro;)</label>
                            <input type="number" name="cost" id="cost" class="form-control form-control-sm border border-2 border-primary @error('cost') is-invalid @enderror" value="{{ old('cost', $event->costo) }}" step="0.01" min="0">
                            @error('cost')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="card mb-4 manage-event-text-card">
            <div class="card-header">
                <h5 class="mb-0">Testo Evento</h5>
            </div>
            <div class="card-body">
                {{-- Presentazione Riassuntiva --}}
                <div class="mb-3">
                    <label for="incipit" class="form-label text-primary-emphasis">Presentazione Riassuntiva</label>
                    <textarea name="incipit" id="incipit" class="form-control border border-2 border-primary @error('incipit') is-invalid @enderror" rows="2" maxlength="500"
                              placeholder="Breve testo di anteprima mostrato nelle liste (max 500 caratteri). Se vuoto viene usata la descrizione.">{{ old('incipit', $event->incipit) }}</textarea>
                    @error('incipit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Descrizione (editor): box bordo blu --}}
                <div class="mb-3 manage-event-description-editor-box">
                    <label for="description" class="form-label text-primary-emphasis">Descrizione <span class="text-danger">*</span></label>
                    <textarea name="description" id="description" class="form-control border border-2 border-primary @error('description') is-invalid @enderror" rows="10">{{ old('description', $event->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

            </div>
        </div>

        <div class="card mb-3 manage-event-cover-card">
            <div class="card-header">
                <h5 class="mb-0">Immagine di Copertina e album</h5>
            </div>
            <div class="card-body">
                @if($event->cover_image_url)
                    <div class="mb-3">
                        <label class="form-label text-primary-emphasis">Immagine attuale</label>
                        <div style="height: 140px; display:flex; align-items:center; justify-content:center; overflow:hidden; background:#f8f9fa;">
                            <img src="{{ $event->cover_image_url }}" alt="Copertina evento" class="img-thumbnail" style="max-height: 100%; max-width: 100%; width:auto; height:auto; object-fit: contain; background:#fff;">
                        </div>
                    </div>
                @endif

                <div class="row g-2 align-items-start event-edit-cover-google-row">
                    <div class="col-12 col-md-6 col-xl-3">
                        <label for="cover_image" class="form-label text-primary-emphasis small mb-1">{{ $event->cover_image_url ? 'Sostituisci copertina' : 'Scegli file copertina' }}</label>
                        <input type="file" name="cover_image" id="cover_image" class="form-control form-control-sm border border-2 border-primary @error('cover_image') is-invalid @enderror" accept="image/*">
                        <div class="form-text" style="font-size: 0.75rem;">JPG, PNG, GIF, WebP · max ~2MB</div>
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
                        <div class="manage-max-participants-box mb-0">
                            <label for="max_participants" class="form-label text-primary-emphasis small mb-1">Max partecipanti</label>
                            <input type="number" name="max_participants" id="max_participants" class="form-control form-control-sm border border-2 border-primary @error('max_participants') is-invalid @enderror" value="{{ old('max_participants', $event->max_participants) }}" min="1">
                            @error('max_participants')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-3" id="max_guests_wrapper" style="{{ old('allow_guests', $event->allow_guests) ? '' : 'display: none;' }}">
                        <div class="manage-max-guests-box mb-0">
                            <label for="max_guests_per_user" class="form-label text-primary-emphasis small mb-1">Max Ospiti</label>
                            @php
                                $mGuestsOn = (bool) old('allow_guests', $event->allow_guests);
                                $mMaxGuests = old('max_guests_per_user', $event->max_guests_per_user);
                                $mMaxGuests = max(1, (int) ($mMaxGuests ?: 3));
                            @endphp
                            <input type="number" name="max_guests_per_user" id="max_guests_per_user" class="form-control form-control-sm @error('max_guests_per_user') is-invalid @enderror" value="{{ $mGuestsOn ? $mMaxGuests : 3 }}" min="1" max="10"
                                @if(!$mGuestsOn) disabled @endif>
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
                            <label class="form-check-label text-primary-emphasis" for="is_active">Evento attivo</label>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input type="checkbox" name="allow_guests" id="allow_guests" class="form-check-input" value="1" {{ old('allow_guests', $event->allow_guests) ? 'checked' : '' }}>
                            <label for="allow_guests" class="form-check-label text-primary-emphasis">Permetti ospiti</label>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input type="hidden" name="elenco_visibile" value="1">
                        </div>
                    </div>
                    @error('allow_guests')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-2">
            <a href="{{ route('manage.events.index') }}" class="btn btn-outline-secondary">Annulla</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Salva Modifiche
            </button>
        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@include('partials.ckeditor4-description', ['height' => 400])
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var manageForm = document.getElementById('manage-event-edit-form');
        if (manageForm) {
            manageForm.addEventListener('submit', function () {
                if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances.description) {
                    CKEDITOR.instances.description.updateElement();
                }
            });
        }

        var allowGuestsCheckbox = document.getElementById('allow_guests');
        var maxGuestsWrapper = document.getElementById('max_guests_wrapper');
        var maxGuestsInput = document.getElementById('max_guests_per_user');

        function syncManageGuests() {
            if (!allowGuestsCheckbox || !maxGuestsWrapper) return;
            var on = allowGuestsCheckbox.checked;
            maxGuestsWrapper.style.display = on ? '' : 'none';
            if (maxGuestsInput) {
                maxGuestsInput.disabled = !on;
                if (on && (!maxGuestsInput.value || parseInt(maxGuestsInput.value, 10) < 1)) {
                    maxGuestsInput.value = '3';
                }
            }
        }

        if (allowGuestsCheckbox) {
            allowGuestsCheckbox.addEventListener('change', syncManageGuests);
            syncManageGuests();
        }
    });
</script>
<style>
    /* Dettagli Evento: bordo verde su tutto il box */
    .manage-event-details-card {
        border: 2px solid #198754 !important;
        border-radius: 0.5rem;
    }
    .manage-event-details-card > .card-header {
        border-bottom: 1px solid rgba(25, 135, 84, 0.35);
        background: rgba(25, 135, 84, 0.08);
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
    .manage-event-details-card > .card-body {
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
    .manage-event-details-card .mb-3 {
        margin-bottom: 0.5rem !important;
    }

    /* Box copertina (quello grigio con immagine): bordo verde */
    .manage-event-cover-card {
        border: 2px solid #0d6efd !important;
        border-radius: 0.5rem;
    }
    .manage-event-cover-card.card {
        border: 2px solid #0d6efd !important;
    }
    .manage-event-cover-card > .card-header {
        border-bottom: 1px solid rgba(13, 110, 253, 0.35);
        background: rgba(13, 110, 253, 0.08);
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
    .manage-event-cover-card > .card-body {
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }

    /* Box Testo Evento (descrizione): bordo blu */
    .manage-event-text-card {
        border: 2px solid #0d6efd !important;
        border-radius: 0.5rem;
    }
    .manage-event-text-card.card {
        border: 2px solid #0d6efd !important;
    }
    .manage-event-text-card > .card-header {
        border-bottom: 1px solid rgba(13, 110, 253, 0.35);
        background: rgba(13, 110, 253, 0.08);
    }

    /* Solo il blocco con CKEditor: bordo blu */
    #manage-event-edit-form .manage-event-description-editor-box {
        border: 2px solid #0d6efd;
        border-radius: 0.375rem;
        padding: 0.75rem 0.85rem;
        background: #fff;
    }

    /* Riassunto e Descrizione: spaziatura verticale normale; resto del box Testo più compatto */
    .manage-event-text-card .card-body > .mb-3:nth-child(1),
    .manage-event-text-card .card-body > .mb-3:nth-child(2) {
        margin-bottom: 1rem !important;
    }

    /* Box Max ospiti per partecipante: nessun bordo */
    .manage-max-guests-box {
        border: 0 !important;
        border-radius: 0;
        padding: 0;
        background: transparent;
    }

    /* Box Max partecipanti: nessun bordo (solo layout compatto) */
    .manage-max-participants-box {
        border: 0 !important;
        border-radius: 0;
        padding: 0;
        background: transparent;
    }

    /* Input numerici (riga con album Google: larghezza colonna) */
    .manage-max-participants-box input[type="number"],
    .manage-max-guests-box input[type="number"] {
        max-width: 100%;
        width: 100%;
    }

    /* Card esterna "Modifica Evento": bordo verde */
    .manage-event-edit-shell {
        border: 2px solid #198754 !important;
        border-radius: 0.5rem;
    }
    .manage-event-edit-shell > .card-body {
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }

    /* Titolo + Data: bordo marrone */
    #manage-event-edit-form #title.form-control,
    #manage-event-edit-form #date.form-control {
        border-color: #8B4513 !important;
    }
    #manage-event-edit-form #title.form-control:focus,
    #manage-event-edit-form #date.form-control:focus {
        border-color: #8B4513 !important;
        box-shadow: 0 0 0 0.2rem rgba(139, 69, 19, 0.25);
    }

    /* Data, costo, scadenza, città: larghezza proporzionata al contenuto */
    #manage-event-edit-form #date.form-control,
    #manage-event-edit-form #deadline.form-control {
        max-width: 13rem;
        width: 100%;
    }
    #manage-event-edit-form #city.form-control {
        max-width: 12rem;
        width: 100%;
    }
    #manage-event-edit-form #cost.form-control {
        max-width: 6rem;
        width: 100%;
    }

    #manage-event-edit-form .event-edit-row-primary #date.form-control,
    #manage-event-edit-form .event-edit-row-primary #deadline.form-control {
        max-width: 100%;
    }
    #manage-event-edit-form .event-edit-row-place #venue.form-control,
    #manage-event-edit-form .event-edit-row-place #address.form-control,
    #manage-event-edit-form .event-edit-row-place #city.form-control {
        max-width: 100%;
    }
    #manage-event-edit-form .event-edit-row-place #cost.form-control {
        max-width: 5rem !important;
        width: 100%;
    }

    #manage-event-edit-form .event-edit-cover-google-row #cover_image.form-control,
    #manage-event-edit-form .event-edit-cover-google-row #google_album_url.form-control {
        max-width: 100%;
    }
</style>
@endsection
