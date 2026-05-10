@extends('layouts.app')

@section('title', 'Mercatino - Excursio')

@section('content')
    <style>
        .mercatino-page {
            --mercatino-bordo: #0dcaf0;
            --mercatino-bordo-trasparente: rgba(13, 202, 240, 0.55);
        }
        .mercatino-page .mercatino-hero-title {
            font-size: clamp(2.2rem, 1.5rem + 1.9vw, 3.2rem);
            line-height: 1.12;
            letter-spacing: 0.2px;
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
        /* Mercatino: casella combinata prezzo con sfondo grigio */
        .mercatino-page #tipo_prezzo:not(.is-invalid) {
            background-color: #f2f4f6;
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
        .mercatino-page .mercatino-title-hero-img,
        .mercatino-page .mercatino-title-hero-preview {
            max-width: 200px;
            max-height: 7.5rem;
            width: auto;
            height: auto;
            object-fit: contain;
            vertical-align: middle;
        }
        .mercatino-page .mercatino-title-image-admin-form .input-group-text {
            background-color: rgba(13, 202, 240, 0.35) !important;
            border-color: rgba(13, 202, 240, 0.65) !important;
            color: #055160 !important;
            font-weight: 600;
        }
        .mercatino-page .mercatino-title-image-admin-form .input-group .form-control:not(.is-invalid) {
            border-color: #adb5bd !important;
        }
        .mercatino-page .mercatino-title-image-admin-form .input-group .btn {
            border-color: #adb5bd;
        }
        /* Stato, zona ritiro, contatto: controlli compatti, larghezza adeguata al contenuto */
        .mercatino-page .mercatino-field-compact-stato {
            max-width: 16rem;
        }
        .mercatino-page .mercatino-field-compact-zona {
            max-width: 22rem;
            min-width: min(100%, 14rem);
        }
        .mercatino-page .mercatino-field-compact-contatto {
            max-width: 28rem;
        }
        .mercatino-page .mercatino-field-compact-tipo-prezzo {
            max-width: 15rem;
        }
        .mercatino-page .mercatino-field-compact-importo {
            max-width: 20rem;
        }
        .mercatino-page .mercatino-field-compact-importo .mercatino-importo-euro {
            max-width: 9.25rem;
        }
        .mercatino-page .mercatino-contatto-accetto-row .mercatino-accetto-box .form-check {
            padding-top: 0.125rem;
        }
    </style>
    <div class="container mercatino-page">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                @php
                    $mercatinoHeroPath = $mercatinoTitleImage ?? null;
                    $mercatinoHeroDefault = 'upload_immagini/mercatino.jpg';
                    $mercatinoHeroRel = $mercatinoHeroPath ?: $mercatinoHeroDefault;
                    $mercatinoHeroFull = public_path($mercatinoHeroRel);
                    $mercatinoHeroV = time();
                    if (is_file($mercatinoHeroFull)) {
                        $tmp = filemtime($mercatinoHeroFull);
                        if (is_int($tmp) && $tmp > 0) {
                            $mercatinoHeroV = $tmp;
                        }
                    }
                @endphp
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
                    <h1 class="mb-0 mercatino-hero-title d-flex flex-wrap align-items-center gap-2">
                        <img src="{{ asset($mercatinoHeroRel) }}?v={{ $mercatinoHeroV }}"
                             alt=""
                             class="mercatino-title-hero-img flex-shrink-0"
                             loading="lazy"
                             decoding="async">
                        <span class="d-inline-flex align-items-center">
                            <i class="fas fa-store text-secondary me-2" aria-hidden="true"></i>Mercatino
                        </span>
                    </h1>
                </div>
                <p class="lead text-muted mb-4">
                    Scambi e occasioni di vendita o regalo di attrezzature, in modo semplice e locale.
                </p>

                <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
                    <button type="button"
                            class="btn btn-primary btn-sm"
                            data-bs-toggle="collapse"
                            data-bs-target="#mercatinoAnnuncioBox"
                            aria-expanded="false"
                            aria-controls="mercatinoAnnuncioBox"
                            id="mercatinoToggleAnnuncioBtn">
                        <i class="fas fa-edit"></i> Inserisci annuncio
                    </button>
                    <a href="{{ route('mercatino.vetrina') }}" class="btn btn-success btn-sm text-white">
                        <i class="fas fa-shop"></i> Vetrina mercatino
                    </a>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <form action="{{ route('mercatino.title-image') }}"
                                  method="POST"
                                  enctype="multipart/form-data"
                                  class="mercatino-title-image-admin-form d-flex flex-wrap align-items-center gap-2 ms-md-auto flex-grow-1 flex-md-grow-0"
                                  style="min-width: min(100%, 18rem);">
                                @csrf
                                <div class="flex-grow-1" style="min-width: 12rem;">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Solo amministratore</span>
                                        <input type="file"
                                               id="mercatinoTitleImageInput"
                                               name="title_image"
                                               class="form-control form-control-sm"
                                               accept="image/jpeg,image/png,image/webp,image/gif,.jpg,.jpeg,.png,.webp,.gif"
                                               required>
                                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-upload"></i> Cambia immagine titolo
                                        </button>
                                    </div>
                                </div>
                                <div id="mercatinoTitlePreviewWrap" class="d-none align-self-center">
                                    <img id="mercatinoTitleImagePreview" src="" alt="Anteprima immagine titolo" class="mercatino-title-hero-preview rounded border border-secondary" title="Anteprima">
                                </div>
                            </form>
                        @endif
                    @endauth
                </div>

                <div class="alert alert-info border mb-4 small" role="status">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fas fa-clock mt-1" aria-hidden="true"></i>
                        <div>
                            <strong class="d-block mb-1">Durata in vetrina (30 giorni)</strong>
                            <p class="mb-0">
                                L’annuncio resta visibile per <strong>30 giorni</strong> dalla data di pubblicazione
                                (o dall’<strong>ultimo rinnovo</strong>). Trascorso questo periodo viene <strong>rimosso automaticamente</strong> dalla vetrina.
                                Puoi pubblicare un nuovo annuncio oppure, finché è ancora attivo, tu (solo l’inserzionista) puoi usare il pulsante
                                <strong>Rinnova</strong> nella <a href="{{ route('mercatino.vetrina') }}">vetrina</a> o tra le bozze qui sotto: i 30 giorni ripartono dalla data del rinnovo.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="alert alert-light border mb-4 small" role="note">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fas fa-lightbulb text-warning" aria-hidden="true"></i>
                        <strong class="text-dark">La Nostra Vetrina dell'Usato</strong>
                    </div>

                    <p class="fw-semibold mb-1">Di cosa si tratta?</p>
                    <p class="mb-2">
                        Hai presente il fascino dei mercatini delle pulci, dove tra un oggetto e l'altro si trova sempre una piccola perla?
                        Abbiamo voluto ricreare quell'atmosfera qui sul nostro sito. Questo spazio è una bacheca virtuale dedicata a tutti noi,
                        nata per dare una seconda vita agli oggetti che non usiamo più ma che sono ancora pronti a raccontare una storia in una nuova casa.
                    </p>
                    <p class="mb-3">
                        Che si tratti di un libro letto, di un complemento d’arredo che non trova più posto o di quel regalo ricevuto e mai scartato,
                        qui puoi decidere di venderlo a piccolo prezzo, scambiarlo o regalarlo.
                    </p>

                    <p class="fw-semibold mb-1">Come funziona:</p>
                    <p class="mb-2">
                        Con <strong>Pubblica in vetrina</strong> l’annuncio va in
                        <a href="{{ route('mercatino.vetrina') }}">vetrina</a> e <strong>ogni bozza locale collegata viene eliminata automaticamente</strong>
                        (non serve cancellarla a mano). Se stai modificando una bozza, puoi anche <strong>Salva bozza</strong> senza pubblicare.
                        Dopo la pubblicazione, per titolo, descrizione, prezzo o foto usa <strong>Modifica</strong> sull’annuncio in vetrina.
                    </p>

                    <p class="mb-0 text-muted">
                        <strong>Nota.</strong> Excursio non gestisce pagamenti né spedizioni: l’accordo è tra gli utenti.
                    </p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <div class="fw-semibold mb-1">Controlla i campi evidenziati.</div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php
                    $isEditingDraft = isset($editDraft) && is_array($editDraft) && !empty($editFolder);
                    $mercatinoShowForm = $isEditingDraft || $errors->any();
                    $mercatinoCatLabels = [
                        'abbigliamento' => 'Abbigliamento',
                        'libri_riviste' => 'Libri e Riviste',
                        'giochi_videogiochi' => 'Giochi e Videogiochi',
                        'sport_hobby' => 'Sport e Hobby',
                        'tecnologia_elettronica' => 'Tecnologia ed Elettronica',
                        'audio_tv' => 'Audio e TV',
                        'idee_regalo' => 'Idee Regalo',
                        'in_omaggio' => 'In Omaggio',
                        'biciclette' => 'Biciclette',
                        'veicoli' => 'Veicoli',
                        'telefonia' => 'Telefonia',
                        'altro' => 'Altro',
                        'casa' => 'Articoli per la casa',
                        'sport' => 'Articoli sportivi',
                        'elettronica_videogiochi' => 'Elettronica e videogiochi',
                    ];
                    $mercatinoCatOrder = [
                        'abbigliamento',
                        'libri_riviste',
                        'giochi_videogiochi',
                        'sport_hobby',
                        'tecnologia_elettronica',
                        'audio_tv',
                        'idee_regalo',
                        'in_omaggio',
                        'biciclette',
                        'veicoli',
                        'telefonia',
                        'altro',
                    ];
                    $mercatinoCatCurrent = old('categoria', $isEditingDraft ? ($editDraft['categoria'] ?? '') : '');
                    $mercatinoCondLabels = [
                        'nuovo' => 'Nuovo / mai usato',
                        'buono' => 'Buone condizioni',
                        'discreto' => 'Usato, ma funzionale',
                        'altro' => 'Altro',
                        'ottimo' => 'Ottime condizioni',
                    ];
                    $mercatinoCondOrder = ['nuovo', 'buono', 'discreto', 'altro'];
                    $mercatinoCondCurrent = old('condizione', $isEditingDraft ? ($editDraft['condizione'] ?? '') : '');
                    $mercatinoTipoPrezzoSelect = old('tipo_prezzo', $isEditingDraft ? ($editDraft['tipo_prezzo'] ?? '') : '');
                    if ($mercatinoTipoPrezzoSelect === 'trattabile') {
                        $mercatinoTipoPrezzoSelect = 'fisso';
                    }
                    $mercatinoTrattabileChecked = (old('trattabile') === '1' || old('trattabile') === 1 || old('trattabile') === true)
                        || (
                            old('trattabile') === null && ! $errors->any()
                            && $isEditingDraft
                            && (
                                ! empty($editDraft['prezzo_trattabile'])
                                || (($editDraft['tipo_prezzo'] ?? '') === 'trattabile')
                            )
                        );
                @endphp
                <div class="collapse {{ $mercatinoShowForm ? 'show' : '' }}" id="mercatinoAnnuncioBox">
                    <div class="card shadow-sm border-0 mb-4" id="mercatino-form">
                        <div class="card-header bg-dark text-white py-3">
                            <h2 class="h5 mb-0">
                                <i class="fas fa-edit me-2"></i>{{ $isEditingDraft ? 'Modifica bozza locale' : 'Nuovo annuncio' }}
                            </h2>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST"
                                  action="{{ $isEditingDraft ? route('mercatino.bozza.update', ['folder' => $editFolder]) : route('mercatino.store') }}"
                                  enctype="multipart/form-data"
                                  autocomplete="off">
                                @csrf
                                @if($isEditingDraft)
                                    <input type="hidden" name="mercatino_bozza_origine" value="{{ $editFolder }}">
                                @endif

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="titolo" class="form-label">Titolo dell’annuncio <span class="text-danger">*</span></label>
                                        <input type="text" name="titolo" id="titolo"
                                               class="form-control @error('titolo') is-invalid @enderror"
                                               value="{{ old('titolo', $isEditingDraft ? ($editDraft['titolo'] ?? '') : '') }}"
                                               placeholder="Es. Zaino 40 L, bastoni telescopici, guida CAI…"
                                               maxlength="120" required>
                                        @error('titolo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="categoria" class="form-label">Categoria <span class="text-danger">*</span></label>
                                        <select name="categoria" id="categoria" class="form-select @error('categoria') is-invalid @enderror" required>
                                            <option value="">Inserisci</option>
                                            @if($mercatinoCatCurrent !== '' && ! in_array($mercatinoCatCurrent, $mercatinoCatOrder, true))
                                                <option value="{{ $mercatinoCatCurrent }}" selected>{{ $mercatinoCatLabels[$mercatinoCatCurrent] ?? $mercatinoCatCurrent }}</option>
                                            @endif
                                            @foreach($mercatinoCatOrder as $catSlug)
                                                <option value="{{ $catSlug }}" @selected($mercatinoCatCurrent === $catSlug)>{{ $mercatinoCatLabels[$catSlug] }}</option>
                                            @endforeach
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
                                          required>{{ old('descrizione', $isEditingDraft ? ($editDraft['descrizione'] ?? '') : '') }}</textarea>
                                @error('descrizione')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-3 mb-3 align-items-end flex-wrap">
                                <div class="col-12 col-sm-auto mercatino-field-compact-tipo-prezzo">
                                    <label for="tipo_prezzo" class="form-label">Prezzo <span class="text-danger">*</span></label>
                                    <select name="tipo_prezzo" id="tipo_prezzo" class="form-select form-select-sm @error('tipo_prezzo') is-invalid @enderror" required>
                                        <option value="">— Scegli —</option>
                                        <option value="fisso" @selected($mercatinoTipoPrezzoSelect === 'fisso')>pezzo</option>
                                        <option value="gratis" @selected($mercatinoTipoPrezzoSelect === 'gratis')>Gratis / omaggio</option>
                                        <option value="scambio" @selected($mercatinoTipoPrezzoSelect === 'scambio')>Solo scambio</option>
                                    </select>
                                    @error('tipo_prezzo')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-sm-auto mercatino-field-compact-importo" id="prezzo_row">
                                    <label for="prezzo" class="form-label">Importo (€)</label>
                                    <div class="d-flex flex-wrap align-items-end gap-2">
                                        <div class="input-group input-group-sm mercatino-importo-euro">
                                            <span class="input-group-text">€</span>
                                            <input type="number" name="prezzo" id="prezzo" step="0.01" min="0"
                                                   class="form-control @error('prezzo') is-invalid @enderror"
                                                   value="{{ old('prezzo', $isEditingDraft ? ($editDraft['prezzo'] ?? '') : '') }}"
                                                   placeholder="0,00">
                                        </div>
                                        <div class="form-check mb-1">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   name="trattabile"
                                                   value="1"
                                                   id="trattabile"
                                                   @checked($mercatinoTrattabileChecked)>
                                            <label class="form-check-label text-nowrap small" for="trattabile">Trattabile</label>
                                        </div>
                                    </div>
                                    @error('prezzo')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-sm-auto mercatino-field-compact-stato">
                                    <label for="condizione" class="form-label">Stato <span class="text-danger">*</span></label>
                                    <select name="condizione" id="condizione" class="form-select form-select-sm @error('condizione') is-invalid @enderror" required>
                                        <option value="">— Scegli —</option>
                                        @if($mercatinoCondCurrent !== '' && ! in_array($mercatinoCondCurrent, $mercatinoCondOrder, true))
                                            <option value="{{ $mercatinoCondCurrent }}" selected>{{ $mercatinoCondLabels[$mercatinoCondCurrent] ?? $mercatinoCondCurrent }}</option>
                                        @endif
                                        @foreach($mercatinoCondOrder as $condSlug)
                                            <option value="{{ $condSlug }}" @selected($mercatinoCondCurrent === $condSlug)>{{ $mercatinoCondLabels[$condSlug] }}</option>
                                        @endforeach
                                    </select>
                                    @error('condizione')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-sm-auto mercatino-field-compact-zona">
                                    <label for="zona_ritiro" class="form-label">Zona di ritiro / incontro <span class="text-danger">*</span></label>
                                    <input type="text" name="zona_ritiro" id="zona_ritiro"
                                           class="form-control form-control-sm @error('zona_ritiro') is-invalid @enderror"
                                           value="{{ old('zona_ritiro', $isEditingDraft ? ($editDraft['zona_ritiro'] ?? '') : '') }}"
                                           placeholder="Es. Milano nord, Sesto…"
                                           maxlength="120" required>
                                    @error('zona_ritiro')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-3 mb-4 align-items-start flex-wrap mercatino-contatto-accetto-row">
                                <div class="col-12 col-md-auto mercatino-field-compact-contatto">
                                    <label for="contatto" class="form-label">Come preferisci essere contattato <span class="text-danger">*</span></label>
                                    <select name="contatto" id="contatto" class="form-select form-select-sm @error('contatto') is-invalid @enderror" required>
                                        <option value="">— Scegli —</option>
                                        <option value="excursio" @selected(old('contatto', $isEditingDraft ? ($editDraft['contatto'] ?? '') : '') === 'excursio')>Messaggi tramite Excursio (consigliato)</option>
                                        <option value="email" @selected(old('contatto', $isEditingDraft ? ($editDraft['contatto'] ?? '') : '') === 'email')>Email (se visibile nel profilo)</option>
                                        <option value="telefono" @selected(old('contatto', $isEditingDraft ? ($editDraft['contatto'] ?? '') : '') === 'telefono')>Telefono (solo dopo accordo)</option>
                                    </select>
                                    @error('contatto')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-md mercatino-accetto-box">
                                    <div class="form-check">
                                        <input class="form-check-input @error('accetto_regole') is-invalid @enderror"
                                               type="checkbox" name="accetto_regole" value="1" id="accetto_regole"
                                               {{ old('accetto_regole') ? 'checked' : '' }}
                                               @unless($isEditingDraft) required @endunless>
                                        <label class="form-check-label small" for="accetto_regole">
                                            Dichiaro che l’annuncio è veritiero e che rispetto le regole della community Excursio.
                                        </label>
                                    </div>
                                    <div class="small text-muted mt-1 ms-1">
                                        Cliccando su <strong>Pubblica in vetrina</strong>, accetti le
                                        <a href="#"
                                           data-bs-toggle="modal"
                                           data-bs-target="#mercatinoRulesModal"
                                           class="text-decoration-none">nostre regole</a>.
                                        @if($isEditingDraft)
                                            <span class="d-block mt-1">Obbligatorio solo per la pubblicazione (non per «Salva bozza»).</span>
                                        @endif
                                    </div>
                                    @error('accetto_regole')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Foto in coda: con multipart, campi dopo file grandi possono non arrivare al server su alcune configurazioni PHP --}}
                            <div class="mb-4">
                                <p class="mb-2 small text-muted">
                                    <span class="fw-bold text-body">Foto dell’oggetto (opzionale, max 3)</span>
                                    <span class="ms-1">JPG, PNG, WebP o GIF, fino a 4 MB ciascuna. L’anteprima compare subito dopo la scelta del file.</span>
                                </p>
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

                            @if($isEditingDraft)
                                <button type="submit"
                                        formaction="{{ route('mercatino.bozza.update', ['folder' => $editFolder]) }}"
                                        formnovalidate
                                        class="btn btn-outline-secondary">
                                    <i class="fas fa-save me-1"></i> Salva bozza
                                </button>
                                <button type="submit"
                                        formaction="{{ route('mercatino.store') }}"
                                        class="btn btn-primary"
                                        id="mercatinoPubblicaDaBozzaBtn">
                                    <i class="fas fa-paper-plane me-1"></i> Pubblica in vetrina
                                </button>
                                <a href="{{ route('mercatino.index') }}" class="btn btn-outline-secondary ms-2">
                                    Annulla modifica
                                </a>
                            @else
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Pubblica in vetrina
                                </button>
                            @endif
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="mercatinoRulesModal" tabindex="-1" aria-labelledby="mercatinoRulesModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="mercatinoRulesModalLabel">Note Importanti per gli Utenti</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-3">
                                    Benvenuti nel nostro mercatino. Per garantire una convivenza serena e sicura, vi preghiamo di leggere quanto segue:
                                </p>

                                <p class="mb-3">
                                    <strong>Esclusione di Responsabilità:</strong>
                                    Il sito agisce esclusivamente come piattaforma di incontro. Non siamo parte delle transazioni, non verifichiamo la qualità degli oggetti e non ci assumiamo alcuna responsabilità per l'esito degli acquisti, eventuali vizi della merce o dispute tra gli utenti.
                                </p>

                                <p class="mb-3">
                                    <strong>Legalità e Pertinenza:</strong>
                                    È severamente vietata la pubblicazione di annunci riguardanti prodotti illegali, contraffatti o non pertinenti alle categorie del sito. Ogni utente è l'unico responsabile (civile e penale) di ciò che propone in vendita.
                                </p>

                                <p class="mb-3">
                                    <strong>Tutela delle Parti:</strong>
                                    Invitiamo i venditori alla massima trasparenza e gli acquirenti alla dovuta cautela. Gli scambi avvengono sotto la diretta ed esclusiva responsabilità degli interessati.
                                </p>

                                <p class="mb-0">
                                    <strong>Promemoria:</strong>
                                    Utilizzando questo servizio, dichiari di aver compreso e accettato che ogni rischio legato alla compravendita rimane a carico tuo e della tua controparte.
                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                            </div>
                        </div>
                    </div>
                </div>

                @if(isset($bozze) && $bozze->isNotEmpty())
                    @php
                        $mercatinoLblCat = [
                            'abbigliamento' => 'Abbigliamento',
                            'libri_riviste' => 'Libri e Riviste',
                            'giochi_videogiochi' => 'Giochi e Videogiochi',
                            'sport_hobby' => 'Sport e Hobby',
                            'tecnologia_elettronica' => 'Tecnologia ed Elettronica',
                            'audio_tv' => 'Audio e TV',
                            'idee_regalo' => 'Idee Regalo',
                            'in_omaggio' => 'In Omaggio',
                            'biciclette' => 'Biciclette',
                            'veicoli' => 'Veicoli',
                            'telefonia' => 'Telefonia',
                            'altro' => 'Altro',
                            'casa' => 'Articoli per la casa',
                            'sport' => 'Articoli sportivi',
                            'elettronica_videogiochi' => 'Elettronica e videogiochi',
                        ];
                        $mercatinoLblPrezzo = [
                            'fisso' => 'pezzo',
                            'gratis' => 'Gratis / omaggio',
                            'trattabile' => 'Trattabile',
                            'scambio' => 'Solo scambio',
                        ];
                        $mercatinoLblCond = [
                            'nuovo' => 'Nuovo / mai usato',
                            'buono' => 'Buone condizioni',
                            'discreto' => 'Usato, ma funzionale',
                            'altro' => 'Altro',
                            'ottimo' => 'Ottime condizioni',
                        ];
                        $mercatinoLblCont = [
                            'excursio' => 'Messaggi tramite Excursio',
                            'email' => 'Email',
                            'telefono' => 'Telefono',
                        ];
                    @endphp
                    <div class="mb-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                            <h2 class="h5 mb-0"><i class="fas fa-folder-open text-secondary me-2"></i>Bozze locali (solo se presenti)</h2>
                            <form method="POST" action="{{ route('mercatino.bozze.delete') }}"
                                  onsubmit="return confirm('Vuoi eliminare tutte le bozze salvate? Questa azione non si può annullare.');">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-trash me-1"></i> Elimina bozze
                                </button>
                            </form>
                        </div>
                        <p class="small text-muted mb-3">
                            Compaiono solo bozze non ancora pubblicate. Dopo «Pubblica in vetrina» la bozza viene rimossa da sola; se il nome cartella coincide già con un annuncio in vetrina, la copia locale viene eliminata al prossimo caricamento della pagina.
                        </p>
                        @foreach($bozze as $bozza)
                            @php
                                $d = $bozza['dati'];
                                $folder = $bozza['cartella'] ?? '';
                                $inVetrina = $folder !== '' && \App\Support\MercatinoAnnuncioStorage::annuncioExists($folder);
                                $mercatinoScadenzaBozza = $inVetrina ? \App\Support\MercatinoAnnuncioStorage::expiresAt($d) : null;
                            @endphp
                            <div class="card mb-3">
                                <div class="card-header py-2 small text-muted d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <span>
                                        @if(!empty($d['inviato_il']))
                                            Inviata il {{ \Carbon\Carbon::parse($d['inviato_il'])->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                        @else
                                            Bozza
                                        @endif
                                    </span>
                                    <div class="d-flex align-items-center flex-wrap gap-2">
                                        @if(!empty($d['foto_caricate']))
                                            <span class="badge bg-secondary">{{ $d['foto_caricate'] }} foto allegate</span>
                                        @endif
                                        @if($inVetrina)
                                            <span class="badge bg-success"><i class="fas fa-shop me-1"></i>In vetrina</span>
                                        @endif
                                        @if($mercatinoScadenzaBozza)
                                            <span class="badge bg-info text-dark">Scade il {{ $mercatinoScadenzaBozza->format('d/m/Y H:i') }}</span>
                                        @endif
                                        @if($folder !== '')
                                            <a class="btn btn-outline-secondary btn-sm"
                                               href="{{ route('mercatino.index', ['edit' => $folder]) }}#mercatino-form">
                                                <i class="fas fa-pen me-1"></i> Modifica
                                            </a>
                                            @if($inVetrina)
                                                <form method="POST"
                                                      action="{{ route('mercatino.annuncio.renew') }}"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Rinnovare questo annuncio? I 30 giorni di visibilità ripartono da oggi.');">
                                                    @csrf
                                                    <input type="hidden" name="folder" value="{{ $folder }}">
                                                    <button type="submit" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-redo-alt me-1"></i> Rinnova 30 giorni
                                                    </button>
                                                </form>
                                                <form method="POST"
                                                      action="{{ route('mercatino.annuncio.destroy') }}"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Rimuovere questo annuncio dalla vetrina pubblica? La bozza resterà salvata in questa pagina.');">
                                                    @csrf
                                                    <input type="hidden" name="folder" value="{{ $folder }}">
                                                    <button type="submit" class="btn btn-outline-warning btn-sm">
                                                        <i class="fas fa-eye-slash me-1"></i> Togli dalla vetrina
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST"
                                                  action="{{ route('mercatino.bozza.destroy', ['folder' => $folder]) }}"
                                                  onsubmit="return confirm('Vuoi eliminare questa bozza?{{ $inVetrina ? ' Verrà rimosso anche dalla vetrina.' : '' }}');"
                                                  class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="fas fa-trash me-1"></i> Cancella
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h3 class="h6 mb-3">{{ $d['titolo'] ?? 'Senza titolo' }}</h3>
                                    <dl class="row small mb-0">
                                        <dt class="col-sm-4 col-md-3">Categoria</dt>
                                        <dd class="col-sm-8 col-md-9">{{ $mercatinoLblCat[$d['categoria'] ?? ''] ?? ($d['categoria'] ?? '—') }}</dd>
                                        <dt class="col-sm-4 col-md-3">Prezzo</dt>
                                        <dd class="col-sm-8 col-md-9">
                                            @php
                                                $bozzaTipoPz = $d['tipo_prezzo'] ?? '';
                                                $bozzaTratt = ! empty($d['prezzo_trattabile']) || $bozzaTipoPz === 'trattabile';
                                                $bozzaHasPz = isset($d['prezzo']) && $d['prezzo'] !== '' && $d['prezzo'] !== null;
                                            @endphp
                                            @if($bozzaTipoPz === 'trattabile' && ! $bozzaHasPz)
                                                Trattabile
                                            @elseif($bozzaTipoPz === 'fisso' || ($bozzaTipoPz === 'trattabile' && $bozzaHasPz))
                                                {{ $mercatinoLblPrezzo['fisso'] ?? 'pezzo' }}
                                                @if($bozzaHasPz)
                                                    — <strong>{{ number_format((float) $d['prezzo'], 2, ',', '.') }} €</strong>
                                                @endif
                                                @if($bozzaTratt)
                                                    <span class="text-muted"> — trattabile</span>
                                                @endif
                                            @else
                                                {{ $mercatinoLblPrezzo[$bozzaTipoPz] ?? ($bozzaTipoPz ?: '—') }}
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
            (function setupMercatinoAnnuncioCollapse() {
                var btn = document.getElementById('mercatinoToggleAnnuncioBtn');
                var box = document.getElementById('mercatinoAnnuncioBox');
                var formAnchor = document.getElementById('mercatino-form');
                if (!btn || !box || typeof bootstrap === 'undefined') {
                    return;
                }

                function setBtnExpanded(isOpen) {
                    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    btn.innerHTML = isOpen
                        ? '<i class="fas fa-eye-slash"></i> Nascondi box annuncio'
                        : '<i class="fas fa-edit"></i> Inserisci annuncio';
                }

                // Stato iniziale
                setBtnExpanded(box.classList.contains('show'));

                // Quando si apre/chiude aggiorna testo pulsante
                box.addEventListener('shown.bs.collapse', function () {
                    setBtnExpanded(true);
                    if (formAnchor) {
                        formAnchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
                box.addEventListener('hidden.bs.collapse', function () {
                    setBtnExpanded(false);
                });

                // Se arrivo con hash sul form, apri automaticamente
                if (window.location && window.location.hash === '#mercatino-form') {
                    try {
                        bootstrap.Collapse.getOrCreateInstance(box, { toggle: false }).show();
                    } catch (e) {}
                }
            })();

            (function setupMercatinoPubblicaDaBozza() {
                var pub = document.getElementById('mercatinoPubblicaDaBozzaBtn');
                var chk = document.getElementById('accetto_regole');
                if (! pub || ! chk) {
                    return;
                }
                pub.addEventListener('click', function (e) {
                    if (! chk.checked) {
                        e.preventDefault();
                        alert('Per pubblicare in vetrina devi accettare le condizioni della community.');
                        chk.focus();
                    }
                });
            })();

            // Mercatino: titolo sempre in MAIUSCOLO (solo UI; lato server è comunque garantito)
            var titoloEl = document.getElementById('titolo');
            if (titoloEl) {
                titoloEl.addEventListener('input', function () {
                    var v = titoloEl.value || '';
                    var up = v.toLocaleUpperCase('it-IT');
                    if (v !== up) {
                        var start = titoloEl.selectionStart;
                        var end = titoloEl.selectionEnd;
                        titoloEl.value = up;
                        try {
                            titoloEl.setSelectionRange(start, end);
                        } catch (e) {}
                    }
                });
            }

            var tipo = document.getElementById('tipo_prezzo');
            var row = document.getElementById('prezzo_row');
            var input = document.getElementById('prezzo');
            var trattabileChk = document.getElementById('trattabile');

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
                if (trattabileChk && !show) {
                    trattabileChk.checked = false;
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

            (function setupMercatinoTitleImagePreview() {
                var input = document.getElementById('mercatinoTitleImageInput');
                var wrap = document.getElementById('mercatinoTitlePreviewWrap');
                var prev = document.getElementById('mercatinoTitleImagePreview');
                if (!input || !wrap || !prev) {
                    return;
                }
                input.addEventListener('change', function () {
                    var f = this.files && this.files[0];
                    if (!f || !/^image\//i.test(f.type)) {
                        wrap.classList.add('d-none');
                        prev.removeAttribute('src');
                        return;
                    }
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        prev.src = e.target.result;
                        wrap.classList.remove('d-none');
                    };
                    reader.readAsDataURL(f);
                });
            })();
        });
    </script>
@endsection
