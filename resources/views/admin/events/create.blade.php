@extends('layouts.app')

@section('title', 'Crea Nuovo Evento - Admin')

@section('content')
    <!-- build-marker: admin-events-create datetime-split v1 -->
    <style>
        :root {
            /* Altezza unica per TUTTI i campi in create (incluso file input) */
            --event-create-field-h: calc(1.5em + 0.5rem + 2px);
        }
        .event-create-col-date {
            flex: 0 0 auto;
            max-width: 16.5rem;
        }
        /* Data + ora (usati nella riga con titolo) */
        .event-create-date {
            flex: 0 0 auto;
            width: 16.5rem;
            max-width: 100%;
        }
        .event-create-time {
            flex: 0 0 auto;
            width: 10rem;
            max-width: 100%;
        }
        .event-create-small-number {
            max-width: 4.75rem;
        }
        .event-create-small-number.form-control {
            text-align: center;
        }
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
        .event-create-media-row {
            /* allinea l'altezza dei box sopra gli switch */
            align-items: stretch;
        }
        .event-create-media-row > div {
            display: flex;
            flex-direction: column;
        }
        .event-create-media-row .form-control,
        .event-create-media-row .form-select {
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
            height: var(--event-create-field-h);
        }
        .event-create-media-row input[type="file"].form-control {
            line-height: 1.2;
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
        /* Switch "Evento attivo" + "Permetti ospiti" sempre in una riga */
        #event_create_switches_cell {
            flex-wrap: nowrap !important;
            white-space: nowrap;
        }
        .event-create-media-max {
            flex: 0 0 auto;
            width: 6.25rem;
            max-width: 100%;
        }

        /* Uniforma l'altezza di tutti i campi del form (non solo la media-row) */
        form[action="{{ route('admin.events.store') }}"] .form-control,
        form[action="{{ route('admin.events.store') }}"] .form-select {
            height: var(--event-create-field-h);
        }
        form[action="{{ route('admin.events.store') }}"] textarea.form-control {
            height: auto;
        }
        form[action="{{ route('admin.events.store') }}"] input[type="file"].form-control {
            height: var(--event-create-field-h);
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
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
                        <form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            {{-- Riga: Titolo + Data evento + Ora inizio --}}
                            <div class="row g-2 align-items-end">
                                <div class="col-12 col-lg">
                                    <div class="mb-3">
                                        <label for="title" class="form-label text-primary-emphasis">Titolo Evento *</label>
                                        <input type="text" class="form-control border border-2 border-primary @error('title') is-invalid @enderror"
                                               id="title" name="title" value="{{ old('title') }}" required>
                                        @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-6 col-lg-auto event-create-date">
                                    <div class="mb-3">
                                        <label for="date_only" class="form-label text-primary-emphasis">Data Evento *</label>
                                        <input type="date"
                                               class="form-control border border-2 border-primary @error('date') is-invalid @enderror"
                                               id="date_only"
                                               value="{{ old('date') ? \Carbon\Carbon::parse(old('date'))->format('Y-m-d') : '' }}"
                                               required>
                                        @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-6 col-lg-auto event-create-time">
                                    <div class="mb-3">
                                        <label for="time_only" class="form-label text-primary-emphasis">Ora inizio *</label>
                                        <input type="time"
                                               class="form-control border border-2 border-primary @error('date') is-invalid @enderror"
                                               id="time_only"
                                               value="{{ old('date') ? \Carbon\Carbon::parse(old('date'))->format('H:i') : '' }}"
                                               required>
                                    </div>
                                </div>

                                <input type="hidden" id="date" name="date" value="{{ old('date') }}" required>
                            </div>

                            {{-- Riga: Nome locale + Indirizzo + Civico + Città --}}
                            <div class="row g-2 align-items-end">
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="mb-3">
                                        <label for="venue" class="form-label text-primary-emphasis">Nome locale</label>
                                        <input type="text" class="form-control border border-2 border-primary @error('venue') is-invalid @enderror"
                                               id="venue" name="venue" value="{{ old('venue') }}" placeholder="es. Ristorante Da Mario" maxlength="35">
                                        @error('venue')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="mb-3">
                                        <label for="address" class="form-label text-primary-emphasis">Indirizzo *</label>
                                        <input type="text" class="form-control border border-2 border-primary @error('address') is-invalid @enderror"
                                               id="address" name="address" value="{{ old('address') }}" required maxlength="35">
                                        @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-6 col-md-3 col-lg-1">
                                    <div class="mb-3">
                                        <label for="civico" class="form-label text-primary-emphasis">Civico</label>
                                        <input type="text"
                                               class="form-control border border-2 border-primary @error('civico') is-invalid @enderror"
                                               id="civico"
                                               name="civico"
                                               value="{{ old('civico') }}"
                                               maxlength="10"
                                               placeholder="12">
                                        @error('civico')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-6 col-md-3 col-lg-3">
                                    <div class="mb-3">
                                        <label for="city" class="form-label text-primary-emphasis">Città *</label>
                                        <input type="text" class="form-control border border-2 border-primary @error('city') is-invalid @enderror"
                                               id="city" name="city" value="{{ old('city') }}" required maxlength="35">
                                        @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 align-items-end">
                                <div class="col-6 col-sm-3 col-lg-1">
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
                                <div class="col-12 col-lg-auto event-create-media-cover">
                                    <div class="mb-3">
                                        <label for="cover_image" class="form-label text-primary-emphasis mb-1">Immag. copertina</label>
                                        <input type="file" class="form-control form-control-sm border border-2 border-primary @error('cover_image') is-invalid @enderror"
                                               id="cover_image" name="cover_image" accept="image/*">
                                        @error('cover_image')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-lg event-create-media-google">
                                    <div class="mb-3">
                                        <label for="google_album_url" class="form-label text-primary-emphasis mb-1" title="Opzionale, sopra i commenti nell'evento.">Link album Google Foto</label>
                                        <input type="url"
                                               inputmode="url"
                                               autocomplete="off"
                                               class="form-control form-control-sm border border-2 border-primary @error('google_album_url') is-invalid @enderror"
                                               id="google_album_url"
                                               name="google_album_url"
                                               value="{{ old('google_album_url') }}"
                                               placeholder="https://photos.app.goo.gl/..."
                                               title="Opzionale, sopra i commenti nell'evento.">
                                        @error('google_album_url')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-6 col-lg-auto event-create-media-max">
                                    <div class="mb-3">
                                        <label for="max_participants" class="form-label text-primary-emphasis mb-1">Max part.</label>
                                        <input type="number" class="form-control form-control-sm border border-2 border-primary event-create-small-number @error('max_participants') is-invalid @enderror"
                                               id="max_participants" name="max_participants"
                                               value="{{ old('max_participants') }}" min="1" max="99">
                                        @error('max_participants')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div id="max_guests_container" class="col-6 col-lg-auto event-create-media-max {{ old('allow_guests', true) ? '' : 'd-none' }}">
                                    <div class="mb-3">
                                        <label for="max_guests_per_user" class="form-label text-primary-emphasis mb-1">Max ospiti</label>
                                        <input type="number" class="form-control form-control-sm border border-2 border-primary event-create-small-number @error('max_guests_per_user') is-invalid @enderror"
                                               id="max_guests_per_user" name="max_guests_per_user"
                                               value="{{ old('max_guests_per_user', 3) }}" min="1" max="99">
                                        @error('max_guests_per_user')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Switch subito sotto la riga scadenza/album/max --}}
                            <div class="mb-3">
                                <div id="event_create_switches_cell" class="event-create-media-switches d-flex flex-wrap align-items-center gap-2 gap-lg-3">
                                    <div class="form-check form-switch mb-0">
                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                            {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label text-primary-emphasis text-nowrap small" for="is_active">
                                            Evento attivo
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input type="hidden" name="elenco_visibile" value="0">
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
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                    <label for="description" class="form-label text-primary-emphasis mb-0">Descrizione *</label>
                                    @if(auth()->check() && auth()->user()->isAdmin())
                                        <button type="button"
                                                class="btn btn-outline-warning btn-sm"
                                                id="btnInsertCancellationRule"
                                                style="color:#8B4513; border-color:#8B4513;">
                                            <i class="fas fa-stamp me-1"></i> Gli eventi proposti
                                        </button>
                                    @endif
                                </div>
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
                                <a href="{{ route('admin.events.index') }}" class="btn btn-secondary me-md-2">
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
            // Data+ora: combina `date_only` + `time_only` nel campo hidden `date`
            (function setupDateTimeCombine() {
                var d = document.getElementById('date_only');
                var t = document.getElementById('time_only');
                var hidden = document.getElementById('date');
                if (!d || !t || !hidden) return;

                function sync() {
                    var dv = (d.value || '').trim();
                    var tv = (t.value || '').trim();
                    if (!dv || !tv) {
                        hidden.value = '';
                        return;
                    }
                    hidden.value = dv + 'T' + tv;
                }

                d.addEventListener('change', sync);
                d.addEventListener('input', sync);
                t.addEventListener('change', sync);
                t.addEventListener('input', sync);
                sync();
            })();

            // Titolo evento: forza MAIUSCOLO in tempo reale
            const titleInput = document.getElementById('title');
            if (titleInput) {
                titleInput.addEventListener('input', function () {
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    const upper = (this.value || '').toLocaleUpperCase('it-IT');
                    if (upper !== this.value) {
                        this.value = upper;
                        try {
                            this.setSelectionRange(start, end);
                        } catch (e) {}
                    }
                });
            }

            // Presentazione riassuntiva: forza MAIUSCOLO in tempo reale (anche incolla)
            const incipitInput = document.getElementById('incipit');
            if (incipitInput) {
                incipitInput.addEventListener('input', function () {
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    const upper = (this.value || '').toLocaleUpperCase('it-IT');
                    if (upper !== this.value) {
                        this.value = upper;
                        try {
                            this.setSelectionRange(start, end);
                        } catch (e) {}
                    }
                });
            }

            // Toggle per gli ospiti
            const allowGuestsCheckbox = document.getElementById('allow_guests');
            const maxGuestsContainer = document.getElementById('max_guests_container');
            function toggleMaxGuests() {
                var on = allowGuestsCheckbox.checked;
                if (maxGuestsContainer) {
                    maxGuestsContainer.classList.toggle('d-none', !on);
                }
            }

            allowGuestsCheckbox.addEventListener('change', toggleMaxGuests);
            toggleMaxGuests();

            // Inserisce la regola annullamento in fondo alla descrizione (solo admin)
            const btnRule = document.getElementById('btnInsertCancellationRule');
            const ruleHtml =
                '<blockquote>' +
                '<p>Gli eventi proposti sono momenti di piacevole convivialità se c\\u2019è partecipazione, mancando questa viene meno lo spirito del divertimento, quindi per tutti gli eventi proposti incluso questo vale la regola che, se non raggiunge un minimo di 6/8 partrcipanti nei 2 gg. che precedono l\\u2019evento lo stesso sarà annullato.</p>' +
                '<p>Pertanto se tua intenzione partecipare ti consiglio di iscriverti.</p>' +
                '</blockquote>';
            function appendRuleToDescription() {
                if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances && CKEDITOR.instances.description) {
                    const editor = CKEDITOR.instances.description;
                    const current = editor.getData() || '';
                    editor.setData((current ? current + '<p><br></p>' : '') + ruleHtml);
                    editor.focus();
                    return;
                }
                const ta = document.getElementById('description');
                if (ta) {
                    const current = ta.value || '';
                    const plain =
                        "\n\n" +
                        "Gli eventi proposti sono momenti di piacevole convivialità se c'è partecipazione, mancando questa viene meno lo spirito del divertimento, quindi per tutti gli eventi proposti incluso questo vale la regola che, se non raggiunge un minimo di 6/8 partrcipanti nei 2 gg. che precedono l'evento lo stesso sarà annullato.\n" +
                        "Pertanto se tua intenzione partecipare ti consiglio di iscriverti.\n";
                    ta.value = current + plain;
                    ta.focus();
                }
            }
            if (btnRule) {
                btnRule.addEventListener('click', function () {
                    if (!confirm('Inserire il testo standard in fondo alla descrizione?')) return;
                    appendRuleToDescription();
                });
            }
        });
    </script>
@endsection
