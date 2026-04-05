@extends('layouts.app')

@section('title', 'Mercatino - Excursio')

@section('content')
    <style>
        .mercatino-page {
            --mercatino-bordo: #0dcaf0;
            --mercatino-bordo-trasparente: rgba(13, 202, 240, 0.55);
        }
        .mercatino-page .form-control:not(.is-invalid),
        .mercatino-page .form-select:not(.is-invalid) {
            border: 2px solid var(--mercatino-bordo-trasparente) !important;
        }
        .mercatino-page .form-control:focus:not(.is-invalid),
        .mercatino-page .form-select:focus:not(.is-invalid) {
            border-color: var(--mercatino-bordo) !important;
            box-shadow: 0 0 0 0.2rem rgba(13, 202, 240, 0.22);
        }
        .mercatino-page .input-group-text {
            border: 2px solid var(--mercatino-bordo-trasparente) !important;
            background-color: rgba(13, 202, 240, 0.08);
            color: #087990;
        }
        .mercatino-page .input-group .form-control:not(.is-invalid) {
            border: 2px solid var(--mercatino-bordo-trasparente) !important;
        }
        .mercatino-page .input-group .form-control:focus:not(.is-invalid) {
            border-color: var(--mercatino-bordo) !important;
            box-shadow: 0 0 0 0.2rem rgba(13, 202, 240, 0.22);
        }
        .mercatino-page .form-check-input:not(.is-invalid) {
            border: 2px solid var(--mercatino-bordo-trasparente) !important;
        }
        .mercatino-page .form-check-input:focus {
            border-color: var(--mercatino-bordo) !important;
            box-shadow: 0 0 0 0.2rem rgba(13, 202, 240, 0.2);
        }
        .mercatino-page .mercatino-foto-slots > .col {
            min-width: 0;
        }
        .mercatino-page .mercatino-preview-frame {
            width: 100%;
            max-width: 100%;
            height: 190px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            border: 2px solid var(--mercatino-bordo) !important;
            overflow: hidden;
        }
        .mercatino-page .mercatino-preview-frame img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }
        .mercatino-page .alert {
            border-width: 2px !important;
            border-color: var(--mercatino-bordo) !important;
        }
        .mercatino-page .alert-success {
            border-color: var(--mercatino-bordo) !important;
        }
        .mercatino-page .card {
            border: 2px solid var(--mercatino-bordo) !important;
        }
        .mercatino-page .card-header.bg-dark {
            border-bottom: 2px solid var(--mercatino-bordo) !important;
        }
    </style>
    <div class="container mercatino-page">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <h1 class="mb-3 fs-2">
                    <i class="fas fa-store text-secondary me-2" aria-hidden="true"></i>Mercatino
                </h1>
                <p class="lead text-muted mb-4">
                    Scambi, attrezzatura e occasioni tra chi partecipa agli eventi Excursio: vendita, regalo o scambio in modo semplice e locale.
                </p>

                <div class="alert alert-light border mb-4 small" role="note">
                    <p class="mb-2">
                        <strong><i class="fas fa-lightbulb text-warning me-1"></i> Idea.</strong>
                        Il mercatino mette in contatto escursionisti che hanno oggetti in più (zaini, bastoni, libri di sentieri…)
                        con chi cerca qualcosa senza comprare sempre nuovo. È pensato per il <strong>ritiro di persona</strong> o
                        l’incontro in sede evento, quando possibile.
                    </p>
                    <p class="mb-0 text-muted">
                        <strong>Nota.</strong> Excursio non gestisce pagamenti né spedizioni: l’accordo è tra gli utenti.
                        Il modulo qui sotto serve a preparare gli annunci; la vetrina pubblica sarà attiva in un secondo momento.
                    </p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
                    </div>
                @endif

                @if(isset($bozze) && $bozze->isNotEmpty())
                    @php
                        $mercatinoLblCat = [
                            'attrezzatura' => 'Attrezzatura',
                            'abbigliamento' => 'Abbigliamento tecnico',
                            'libri_mappe' => 'Libri, mappe e guide',
                            'trasporti' => 'Trasporti / condivisione uscite',
                            'altro' => 'Altro',
                        ];
                        $mercatinoLblPrezzo = [
                            'fisso' => 'Prezzo fisso (€)',
                            'gratis' => 'Gratis / omaggio',
                            'trattabile' => 'Trattabile',
                            'scambio' => 'Solo scambio',
                        ];
                        $mercatinoLblCond = [
                            'nuovo' => 'Nuovo / mai usato',
                            'ottimo' => 'Ottime condizioni',
                            'buono' => 'Buone condizioni',
                            'discreto' => 'Usato, ma funzionale',
                        ];
                        $mercatinoLblCont = [
                            'excursio' => 'Messaggi tramite Excursio',
                            'email' => 'Email',
                            'telefono' => 'Telefono',
                        ];
                    @endphp
                    <div class="mb-4">
                        <h2 class="h5 mb-2"><i class="fas fa-folder-open text-secondary me-2"></i>Le tue bozze salvate</h2>
                        <p class="small text-muted mb-3">
                            Di seguito i dettagli degli annunci che hai già inviato come bozza (solo tu li vedi qui).
                            Gli invii fatti <strong>prima di oggi</strong> potevano salvare solo le foto: per quelli non c’è scheda testo.
                        </p>
                        @foreach($bozze as $bozza)
                            @php $d = $bozza['dati']; @endphp
                            <div class="card mb-3">
                                <div class="card-header py-2 small text-muted d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <span>
                                        @if(!empty($d['inviato_il']))
                                            Inviata il {{ \Carbon\Carbon::parse($d['inviato_il'])->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                        @else
                                            Bozza
                                        @endif
                                    </span>
                                    @if(!empty($d['foto_caricate']))
                                        <span class="badge bg-secondary">{{ $d['foto_caricate'] }} foto allegate</span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <h3 class="h6 mb-3">{{ $d['titolo'] ?? 'Senza titolo' }}</h3>
                                    <dl class="row small mb-0">
                                        <dt class="col-sm-4 col-md-3">Categoria</dt>
                                        <dd class="col-sm-8 col-md-9">{{ $mercatinoLblCat[$d['categoria'] ?? ''] ?? ($d['categoria'] ?? '—') }}</dd>
                                        <dt class="col-sm-4 col-md-3">Prezzo</dt>
                                        <dd class="col-sm-8 col-md-9">
                                            {{ $mercatinoLblPrezzo[$d['tipo_prezzo'] ?? ''] ?? ($d['tipo_prezzo'] ?? '—') }}
                                            @if(($d['tipo_prezzo'] ?? '') === 'fisso' && isset($d['prezzo']))
                                                — <strong>{{ number_format((float) $d['prezzo'], 2, ',', '.') }} €</strong>
                                            @endif
                                        </dd>
                                        <dt class="col-sm-4 col-md-3">Stato</dt>
                                        <dd class="col-sm-8 col-md-9">{{ $mercatinoLblCond[$d['condizione'] ?? ''] ?? ($d['condizione'] ?? '—') }}</dd>
                                        <dt class="col-sm-4 col-md-3">Zona ritiro</dt>
                                        <dd class="col-sm-8 col-md-9">{{ $d['zona_ritiro'] ?? '—' }}</dd>
                                        <dt class="col-sm-4 col-md-3">Contatto</dt>
                                        <dd class="col-sm-8 col-md-9">{{ $mercatinoLblCont[$d['contatto'] ?? ''] ?? ($d['contatto'] ?? '—') }}</dd>
                                    </dl>
                                    <p class="small fw-semibold mt-3 mb-1">Descrizione</p>
                                    <p class="small text-muted mb-0" style="white-space: pre-wrap;">{{ $d['descrizione'] ?? '' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white py-3">
                        <h2 class="h5 mb-0"><i class="fas fa-edit me-2"></i>Nuovo annuncio (bozza)</h2>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('mercatino.store') }}" enctype="multipart/form-data" novalidate>
                            @csrf

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="titolo" class="form-label">Titolo dell’annuncio <span class="text-danger">*</span></label>
                                    <input type="text" name="titolo" id="titolo"
                                           class="form-control @error('titolo') is-invalid @enderror"
                                           value="{{ old('titolo') }}"
                                           placeholder="Es. Zaino 40 L, bastoni telescopici, guida CAI…"
                                           maxlength="120" required>
                                    @error('titolo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="categoria" class="form-label">Categoria <span class="text-danger">*</span></label>
                                    <select name="categoria" id="categoria" class="form-select @error('categoria') is-invalid @enderror" required>
                                        <option value="">— Scegli —</option>
                                        <option value="attrezzatura" @selected(old('categoria') === 'attrezzatura')>Attrezzatura (zaini, tende, bastoni, borracce…)</option>
                                        <option value="abbigliamento" @selected(old('categoria') === 'abbigliamento')>Abbigliamento tecnico</option>
                                        <option value="libri_mappe" @selected(old('categoria') === 'libri_mappe')>Libri, mappe e guide</option>
                                        <option value="trasporti" @selected(old('categoria') === 'trasporti')>Trasporti / condivisione uscite</option>
                                        <option value="altro" @selected(old('categoria') === 'altro')>Altro</option>
                                    </select>
                                    @error('categoria')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="descrizione" class="form-label">Descrizione <span class="text-danger">*</span></label>
                                <textarea name="descrizione" id="descrizione" rows="5"
                                          class="form-control @error('descrizione') is-invalid @enderror"
                                          placeholder="Marca, modello, peso, taglia, difetti eventuali, cosa include…"
                                          required>{{ old('descrizione') }}</textarea>
                                @error('descrizione')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label d-block">Foto dell’oggetto <span class="text-muted fw-normal">(opzionale, max 3)</span></label>
                                <p class="small text-muted mb-2">JPG, PNG, WebP o GIF, fino a 4 MB ciascuna. L’anteprima compare subito dopo la scelta del file.</p>
                                <div class="row row-cols-1 row-cols-md-3 g-3 mercatino-foto-slots">
                                    <div class="col">
                                        <label for="foto_1" class="form-label small text-muted mb-1">Foto 1</label>
                                        <input type="file" name="foto_1" id="foto_1" accept="image/jpeg,image/png,image/webp,image/gif"
                                               class="form-control form-control-sm @error('foto_1') is-invalid @enderror">
                                        <div id="mercatino_preview_1" class="mercatino-foto-preview mt-2 d-none text-center">
                                            <div class="mercatino-preview-frame mx-auto">
                                                <img src="" alt="Anteprima foto 1">
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-link btn-sm text-danger px-0 mercatino-foto-clear" aria-label="Rimuovi foto 1">Rimuovi</button>
                                            </div>
                                        </div>
                                        @error('foto_1')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col">
                                        <label for="foto_2" class="form-label small text-muted mb-1">Foto 2</label>
                                        <input type="file" name="foto_2" id="foto_2" accept="image/jpeg,image/png,image/webp,image/gif"
                                               class="form-control form-control-sm @error('foto_2') is-invalid @enderror">
                                        <div id="mercatino_preview_2" class="mercatino-foto-preview mt-2 d-none text-center">
                                            <div class="mercatino-preview-frame mx-auto">
                                                <img src="" alt="Anteprima foto 2">
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-link btn-sm text-danger px-0 mercatino-foto-clear" aria-label="Rimuovi foto 2">Rimuovi</button>
                                            </div>
                                        </div>
                                        @error('foto_2')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col">
                                        <label for="foto_3" class="form-label small text-muted mb-1">Foto 3</label>
                                        <input type="file" name="foto_3" id="foto_3" accept="image/jpeg,image/png,image/webp,image/gif"
                                               class="form-control form-control-sm @error('foto_3') is-invalid @enderror">
                                        <div id="mercatino_preview_3" class="mercatino-foto-preview mt-2 d-none text-center">
                                            <div class="mercatino-preview-frame mx-auto">
                                                <img src="" alt="Anteprima foto 3">
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-link btn-sm text-danger px-0 mercatino-foto-clear" aria-label="Rimuovi foto 3">Rimuovi</button>
                                            </div>
                                        </div>
                                        @error('foto_3')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="row g-2">
                                        <div class="col-sm-6">
                                            <label for="tipo_prezzo" class="form-label">Prezzo <span class="text-danger">*</span></label>
                                            <select name="tipo_prezzo" id="tipo_prezzo" class="form-select @error('tipo_prezzo') is-invalid @enderror" required>
                                                <option value="">— Scegli —</option>
                                                <option value="fisso" @selected(old('tipo_prezzo') === 'fisso')>Prezzo fisso (€)</option>
                                                <option value="gratis" @selected(old('tipo_prezzo') === 'gratis')>Gratis / omaggio</option>
                                                <option value="trattabile" @selected(old('tipo_prezzo') === 'trattabile')>Trattabile</option>
                                                <option value="scambio" @selected(old('tipo_prezzo') === 'scambio')>Solo scambio</option>
                                            </select>
                                            @error('tipo_prezzo')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-sm-6" id="prezzo_row">
                                            <label for="prezzo" class="form-label">Importo (€)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">€</span>
                                                <input type="number" name="prezzo" id="prezzo" step="0.01" min="0"
                                                       class="form-control @error('prezzo') is-invalid @enderror"
                                                       value="{{ old('prezzo') }}"
                                                       placeholder="0,00">
                                            </div>
                                            @error('prezzo')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="condizione" class="form-label">Stato <span class="text-danger">*</span></label>
                                    <select name="condizione" id="condizione" class="form-select @error('condizione') is-invalid @enderror" required>
                                        <option value="">— Scegli —</option>
                                        <option value="nuovo" @selected(old('condizione') === 'nuovo')>Nuovo / mai usato</option>
                                        <option value="ottimo" @selected(old('condizione') === 'ottimo')>Ottime condizioni</option>
                                        <option value="buono" @selected(old('condizione') === 'buono')>Buone condizioni</option>
                                        <option value="discreto" @selected(old('condizione') === 'discreto')>Usato, ma funzionale</option>
                                    </select>
                                    @error('condizione')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="zona_ritiro" class="form-label">Zona di ritiro / incontro <span class="text-danger">*</span></label>
                                <input type="text" name="zona_ritiro" id="zona_ritiro"
                                       class="form-control @error('zona_ritiro') is-invalid @enderror"
                                       value="{{ old('zona_ritiro') }}"
                                       placeholder="Es. Milano nord, Sesto, stazione Centrale…"
                                       maxlength="120" required>
                                @error('zona_ritiro')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="contatto" class="form-label">Come preferisci essere contattato <span class="text-danger">*</span></label>
                                <select name="contatto" id="contatto" class="form-select @error('contatto') is-invalid @enderror" required>
                                    <option value="">— Scegli —</option>
                                    <option value="excursio" @selected(old('contatto') === 'excursio')>Messaggi tramite Excursio (consigliato)</option>
                                    <option value="email" @selected(old('contatto') === 'email')>Email (se visibile nel profilo)</option>
                                    <option value="telefono" @selected(old('contatto') === 'telefono')>Telefono (solo dopo accordo)</option>
                                </select>
                                @error('contatto')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input @error('accetto_regole') is-invalid @enderror"
                                       type="checkbox" name="accetto_regole" value="1" id="accetto_regole"
                                       {{ old('accetto_regole') ? 'checked' : '' }} required>
                                <label class="form-check-label small" for="accetto_regole">
                                    Dichiaro che l’annuncio è veritiero e che rispetto le regole della community Excursio.
                                </label>
                                @error('accetto_regole')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Invia bozza
                            </button>
                        </form>
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <div class="card h-100 border-0 bg-light">
                            <div class="card-body">
                                <h3 class="h6"><i class="fas fa-shield-alt text-primary me-1"></i> Sicurezza</h3>
                                <p class="small text-muted mb-0">Incontra in luogo pubblico per il ritiro; per oggetti di valore valuta sempre due passaggi.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 bg-light">
                            <div class="card-body">
                                <h3 class="h6"><i class="fas fa-recycle text-success me-1"></i> Sostenibilità</h3>
                                <p class="small text-muted mb-0">Dare una seconda vita a zaini e abbigliamento tecnico riduce sprechi e costi.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 bg-light">
                            <div class="card-body">
                                <h3 class="h6"><i class="fas fa-handshake text-secondary me-1"></i> Fair play</h3>
                                <p class="small text-muted mb-0">Prezzi onesti, descrizioni chiare: rispetto per chi legge e chi partecipa agli eventi.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tipo = document.getElementById('tipo_prezzo');
            var row = document.getElementById('prezzo_row');
            var input = document.getElementById('prezzo');

            function syncPrezzoField() {
                var show = tipo && tipo.value === 'fisso';
                if (row) {
                    row.classList.toggle('d-none', !show);
                }
                if (input) {
                    input.required = !!show;
                    if (!show) {
                        input.value = '';
                    }
                }
            }

            if (tipo) {
                tipo.addEventListener('change', syncPrezzoField);
            }
            syncPrezzoField();

            function bindMercatinoFotoPreview(inputId, previewId) {
                var input = document.getElementById(inputId);
                var wrap = document.getElementById(previewId);
                if (!input || !wrap) {
                    return;
                }
                var img = wrap.querySelector('img');
                var clearBtn = wrap.querySelector('.mercatino-foto-clear');

                function hidePreview() {
                    wrap.classList.add('d-none');
                    if (img) {
                        img.removeAttribute('src');
                    }
                }

                input.addEventListener('change', function () {
                    var file = input.files && input.files[0];
                    if (!file || !file.type || file.type.indexOf('image/') !== 0) {
                        hidePreview();
                        return;
                    }
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        if (img) {
                            img.src = e.target.result;
                        }
                        wrap.classList.remove('d-none');
                    };
                    reader.readAsDataURL(file);
                });

                if (clearBtn) {
                    clearBtn.addEventListener('click', function () {
                        input.value = '';
                        hidePreview();
                    });
                }
            }

            bindMercatinoFotoPreview('foto_1', 'mercatino_preview_1');
            bindMercatinoFotoPreview('foto_2', 'mercatino_preview_2');
            bindMercatinoFotoPreview('foto_3', 'mercatino_preview_3');
        });
    </script>
@endsection
