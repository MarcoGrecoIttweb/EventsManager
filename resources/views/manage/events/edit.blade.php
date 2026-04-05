@extends('layouts.app')

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

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Dettagli Evento</h5>
            </div>
            <div class="card-body">
                {{-- Titolo --}}
                <div class="mb-3">
                    <label for="title" class="form-label text-primary-emphasis">Titolo Evento <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="title" class="form-control border border-2 border-primary @error('title') is-invalid @enderror" value="{{ old('title', $event->title) }}" maxlength="120" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Data --}}
                <div class="mb-3">
                    <label for="date" class="form-label text-primary-emphasis">Data Evento <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="date" id="date" class="form-control border border-2 border-primary @error('date') is-invalid @enderror" value="{{ old('date', $event->date->format('Y-m-d\TH:i')) }}" required>
                    @error('date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Città --}}
                <div class="mb-3">
                    <label for="city" class="form-label text-primary-emphasis">Città <span class="text-danger">*</span></label>
                    <input type="text" name="city" id="city" class="form-control border border-2 border-primary @error('city') is-invalid @enderror" value="{{ old('city', $event->city) }}" maxlength="15" required>
                    @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Luogo --}}
                <div class="mb-3">
                    <label for="venue" class="form-label text-primary-emphasis">Nome locale</label>
                    <input type="text" name="venue" id="venue" class="form-control border border-2 border-primary @error('venue') is-invalid @enderror" value="{{ old('venue', $event->dove) }}" maxlength="50">
                    @error('venue')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Indirizzo --}}
                <div class="mb-3">
                    <label for="address" class="form-label text-primary-emphasis">Indirizzo <span class="text-danger">*</span></label>
                    <input type="text" name="address" id="address" class="form-control border border-2 border-primary @error('address') is-invalid @enderror" value="{{ old('address', $event->address) }}" maxlength="50" required>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Costo --}}
                <div class="mb-3">
                    <label for="cost" class="form-label text-primary-emphasis">Costo (&euro;)</label>
                    <input type="number" name="cost" id="cost" class="form-control border border-2 border-primary @error('cost') is-invalid @enderror" value="{{ old('cost', $event->costo) }}" step="0.01" min="0">
                    @error('cost')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="deadline" class="form-label text-primary-emphasis">Scadenza Iscrizioni</label>
                            <input type="datetime-local" name="deadline" id="deadline" class="form-control border border-2 border-primary @error('deadline') is-invalid @enderror" value="{{ old('deadline', $event->deadline ? $event->deadline->format('Y-m-d\TH:i') : '') }}">
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

            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Partecipanti e Ospiti</h5>
            </div>
            <div class="card-body">
                {{-- Max partecipanti --}}
                <div class="mb-3">
                    <label for="max_participants" class="form-label text-primary-emphasis">Max partecipanti</label>
                    <input type="number" name="max_participants" id="max_participants" class="form-control border border-2 border-primary @error('max_participants') is-invalid @enderror" value="{{ old('max_participants', $event->max_participants) }}" min="1">
                    <div class="form-text">Lascia vuoto per partecipanti illimitati.</div>
                    @error('max_participants')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Evento attivo --}}
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                               {{ old('is_active', $event->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label text-primary-emphasis" for="is_active">
                            Evento attivo
                        </label>
                    </div>
                </div>

                {{-- Permetti ospiti --}}
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="allow_guests" id="allow_guests" class="form-check-input" value="1" {{ old('allow_guests', $event->allow_guests) ? 'checked' : '' }}>
                        <label for="allow_guests" class="form-check-label text-primary-emphasis">Permetti ospiti</label>
                    </div>
                    @error('allow_guests')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Max Ospiti per Utente (disabilitato se ospiti off: evita value=0 con min=1 che blocca l'invio del form nel browser) --}}
                <div class="mb-3" id="max_guests_wrapper" style="{{ old('allow_guests', $event->allow_guests) ? '' : 'display: none;' }}">
                    <label for="max_guests_per_user" class="form-label text-primary-emphasis">Max ospiti per partecipante</label>
                    @php
                        $mGuestsOn = (bool) old('allow_guests', $event->allow_guests);
                        $mMaxGuests = old('max_guests_per_user', $event->max_guests_per_user);
                        $mMaxGuests = max(1, (int) ($mMaxGuests ?: 3));
                    @endphp
                    <input type="number" name="max_guests_per_user" id="max_guests_per_user" class="form-control @error('max_guests_per_user') is-invalid @enderror" value="{{ $mGuestsOn ? $mMaxGuests : 3 }}" min="1" max="10"
                        @if(!$mGuestsOn) disabled @endif>
                    @error('max_guests_per_user')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Testo Evento</h5>
            </div>
            <div class="card-body">
                {{-- Riassunto --}}
                <div class="mb-3">
                    <label for="incipit" class="form-label text-primary-emphasis">Riassunto</label>
                    <textarea name="incipit" id="incipit" class="form-control border border-2 border-primary @error('incipit') is-invalid @enderror" rows="2" maxlength="500"
                              placeholder="Breve testo di anteprima mostrato nelle liste (max 500 caratteri). Se vuoto viene usata la descrizione.">{{ old('incipit', $event->incipit) }}</textarea>
                    @error('incipit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Descrizione --}}
                <div class="mb-3">
                    <label for="description" class="form-label text-primary-emphasis">Descrizione <span class="text-danger">*</span></label>
                    <textarea name="description" id="description" class="form-control border border-2 border-primary @error('description') is-invalid @enderror" rows="10">{{ old('description', $event->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Immagine di Copertina</h5>
            </div>
            <div class="card-body">
                @if($event->cover_image_url)
                    <div class="mb-3">
                        <label class="form-label text-primary-emphasis">Immagine attuale</label>
                        <div style="height: 200px; display:flex; align-items:center; justify-content:center; overflow:hidden; background:#f8f9fa;">
                            <img src="{{ $event->cover_image_url }}" alt="Copertina evento" class="img-thumbnail" style="max-height: 100%; max-width: 100%; width:auto; height:auto; object-fit: contain; background:#fff;">
                        </div>
                    </div>
                @endif

                <div class="mb-3">
                    <label for="cover_image" class="form-label text-primary-emphasis">{{ $event->cover_image_url ? 'Sostituisci immagine di copertina' : 'Immagine Cope.' }}</label>
                    <input type="file" name="cover_image" id="cover_image" class="form-control border border-2 border-primary @error('cover_image') is-invalid @enderror" accept="image/*">
                    <div class="form-text">Formati accettati: JPG, PNG, GIF, WebP. Dimensione massima consigliata: 2MB.</div>
                    @error('cover_image')
                        <div class="invalid-feedback">{{ $message }}</div>
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
@endsection
