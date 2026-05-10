@extends('layouts.app')

@section('title', 'Vetrina Mercatino - Excursio')

@section('content')
    <style>
        :root {
            --mercatino-blue: #0d6efd; /* Bootstrap primary */
        }
        .mercatino-vetrina-title {
            font-size: clamp(1.85rem, 1.2rem + 1.55vw, 2.85rem);
            letter-spacing: 0.2px;
            flex-wrap: wrap;
            row-gap: 0.5rem;
        }
        /* Immagine sotto il titolo (stesse impostazioni della chat) */
        .mercatino-vetrina-header-display,
        .mercatino-vetrina-header-image-preview {
            max-width: 140px;
            width: 100%;
            height: auto;
        }
        /* Form admin in toolbar: solo l’etichetta «Solo amministratore» in azzurro */
        .mercatino-vetrina-title-image-admin-form .input-group-text {
            background-color: rgba(13, 202, 240, 0.35);
            border-color: rgba(13, 202, 240, 0.65);
            color: #055160;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .mercatino-vetrina-title-image-admin-form .form-control,
        .mercatino-vetrina-title-image-admin-form .btn {
            border-color: #adb5bd;
        }
        .mercatino-vetrina-toolbar {
            row-gap: 0.5rem;
        }
        .mercatino-vetrina-title__text {
            /* Fallback (se il browser non supporta background-clip:text) */
            color: #0d6efd;
            background: linear-gradient(90deg, #ff3d00 0%, #ffcc00 30%, #00c853 55%, #2962ff 80%, #aa00ff 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent; /* usato quando background-clip funziona */
            text-shadow:
                0 1px 0 rgba(255,255,255,0.95),
                0 2px 12px rgba(0,0,0,0.25),
                0 0 48px rgba(25,135,84,1),
                0 0 18px rgba(25,135,84,1),
                0 0 1px rgba(0,0,0,0.55);
        }
        /* Bordi grigi per tutti i box della vetrina */
        .mercatino-vetrina-title,
        .mercatino-vetrina-title__text {
            /* no border, just keep title styles */
        }
        .mercatino-vetrina-card,
        .mercatino-vetrina-card .card-header,
        .mercatino-vetrina-card .card-body,
        .mercatino-vetrina-card .card-footer,
        .mercatino-vetrina-card .border,
        .mercatino-vetrina-card img.border,
        .mercatino-vetrina-card .alert.border {
            border-color: #adb5bd !important;
        }
        .mercatino-vetrina-card.card {
            border: 2px solid #adb5bd !important;
        }
        .mercatino-vetrina-card .card-header {
            border-bottom: 2px solid #adb5bd !important;
        }
        /* Foto annuncio: tutte stessa dimensione */
        .mercatino-vetrina-photos {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.5rem;
        }
        .mercatino-vetrina-photo {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 0.5rem;
            overflow: hidden;
            border: 2px solid #adb5bd;
            background: #f8f9fa;
        }
        .mercatino-vetrina-photo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }
        /* Lista compatta annunci */
        .mercatino-vetrina-list-card.card {
            border-width: 2px !important;
        }
        .mercatino-vetrina-list-row {
            display: grid;
            grid-template-columns: 88px 1fr auto auto;
            gap: 0.75rem;
            align-items: center;
        }
        @media (max-width: 576px) {
            .mercatino-vetrina-list-row {
                grid-template-columns: 76px 1fr;
                grid-auto-rows: auto;
            }
            .mercatino-vetrina-list-actions {
                grid-column: 1 / -1;
                display: flex;
                gap: 0.5rem;
                justify-content: flex-end;
            }
            .mercatino-vetrina-list-price {
                justify-self: start;
            }
        }
        .mercatino-vetrina-thumb {
            width: 88px;
            aspect-ratio: 1 / 1;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #adb5bd;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .mercatino-vetrina-thumb img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }
        .mercatino-vetrina-thumb i {
            font-size: 1.4rem;
            color: #6c757d;
        }
        .mercatino-vetrina-titleline {
            font-weight: 700;
            color: #212529;
            line-height: 1.15;
        }
        .mercatino-vetrina-subline {
            font-size: 0.82rem;
            color: #6c757d;
        }
        .mercatino-vetrina-seller {
            color: #0d6efd;
            font-weight: 700;
        }
        .mercatino-vetrina-list-price {
            white-space: nowrap;
        }
        .mercatino-vetrina-price-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 28px;
            min-width: 130px;
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
            background: #e9ecef;
            color: #212529;
            border: 1px solid #adb5bd;
            font-weight: 700;
            font-size: 0.85rem;
            line-height: 1;
        }
        .mercatino-vetrina-details-btn {
            min-height: 28px;
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
            line-height: 1;
        }
        .mercatino-vetrina-details-box {
            border: 2px solid #adb5bd !important;
            border-color: #adb5bd !important;
            background: transparent;
            border-radius: 12px;
            padding: 12px 12px;
        }
        /* Dentro i dettagli: nessun bordo blu */
        .mercatino-vetrina-details-box .border,
        .mercatino-vetrina-details-box .alert.border,
        .mercatino-vetrina-details-box img.border {
            border-color: #adb5bd !important;
        }
        /* Descrizione: riquadro grigio + testo nero più grande */
        .mercatino-vetrina-desc {
            border: 1px solid #adb5bd;
            border-radius: 10px;
            background: #f8f9fa;
            padding: 10px 12px;
            color: #212529;
            font-size: 0.98rem;
            line-height: 1.35;
            white-space: pre-wrap;
        }
        /* Modal modifica annuncio: bordi box grigi */
        .mercatino-edit-modal .border,
        .mercatino-edit-modal .alert.border,
        .mercatino-edit-modal .mercatino-vetrina-photo {
            border-color: #adb5bd !important;
        }
        .mercatino-edit-modal .mercatino-vetrina-photo {
            border-width: 1px !important;
        }
    </style>
    <div class="container py-4" style="max-width: 68rem;">
        <div class="mb-3">
            <h1 class="mb-0 d-flex align-items-center mercatino-vetrina-title gap-3">
                <span class="d-inline-flex align-items-center">
                    <i class="fas fa-store text-secondary me-2"></i>
                    <span class="mercatino-vetrina-title__text">Vetrina Mercatino</span>
                </span>
            </h1>
        </div>

        <div class="mb-4 text-center">
            @php
                $vetrinaBannerRel = ! empty($vetrinaHeaderImage) ? $vetrinaHeaderImage : null;
                if (! $vetrinaBannerRel && is_file(public_path('upload_immagini/mercatino2.jpg'))) {
                    $vetrinaBannerRel = 'upload_immagini/mercatino2.jpg';
                }
            @endphp
            @if(! empty($vetrinaBannerRel))
                @php
                    $vetrinaHeaderCacheBuster = time();
                    $vetrinaHeaderFull = public_path($vetrinaBannerRel);
                    if (file_exists($vetrinaHeaderFull)) {
                        $tmp = filemtime($vetrinaHeaderFull);
                        if (is_int($tmp) && $tmp > 0) {
                            $vetrinaHeaderCacheBuster = $tmp;
                        }
                    }
                @endphp
                <img src="{{ asset($vetrinaBannerRel) }}?v={{ $vetrinaHeaderCacheBuster }}"
                     alt="Vetrina Mercatino"
                     class="mercatino-vetrina-header-display">
            @endif
        </div>

        <p class="text-muted mb-3">
            Qui trovi gli annunci pubblicati dagli utenti della community.
        </p>

        <div class="d-flex flex-wrap gap-2 align-items-center mb-4 mercatino-vetrina-toolbar">
            <a href="{{ route('mercatino.index') }}" class="btn btn-primary btn-sm text-white">
                <i class="fas fa-edit"></i> Inserisci annuncio
            </a>
            @if(auth()->check() && auth()->user()->isAdmin() && isset($annunci) && $annunci->isNotEmpty())
                <form method="POST"
                      action="{{ route('mercatino.annunci.destroyAll') }}"
                      class="d-inline"
                      onsubmit="return confirm('Eliminare TUTTI gli annunci dalla vetrina? L’operazione non si può annullare.') && confirm('Seconda conferma: procedere comunque?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-trash-alt me-1"></i> Elimina tutti gli annunci
                    </button>
                </form>
            @endif
            <a href="{{ route('home') }}" class="btn btn-success btn-sm text-white">
                <i class="fas fa-home"></i> Home
            </a>
            @auth
                @if(auth()->user()->isAdmin())
                    <form action="{{ route('mercatino.vetrina.header-image') }}"
                          method="POST"
                          enctype="multipart/form-data"
                          class="mercatino-vetrina-title-image-admin-form d-flex flex-wrap align-items-center gap-2 text-start flex-grow-1 flex-md-grow-0"
                          style="min-width: min(100%, 18rem);">
                        @csrf
                        <div class="flex-grow-1" style="min-width: 12rem;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Solo amministratore</span>
                                <input type="file"
                                       id="mercatinoVetrinaHeaderImageInput"
                                       name="header_image"
                                       class="form-control form-control-sm"
                                       accept="image/jpeg,image/png,image/webp,image/gif,.jpg,.jpeg,.png,.webp,.gif"
                                       required>
                                <button type="submit" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-upload"></i> Cambia immagine titolo
                                </button>
                            </div>
                        </div>
                        <div id="mercatinoVetrinaHeaderPreviewWrap" class="d-none align-self-center">
                            <img id="mercatinoVetrinaHeaderImagePreview"
                                 src=""
                                 alt="Anteprima immagine titolo vetrina"
                                 class="mercatino-vetrina-header-image-preview rounded border border-secondary"
                                 title="Anteprima">
                        </div>
                    </form>
                @endif
            @endauth
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
                <div class="fw-semibold mb-1">Si è verificato un errore.</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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
            $mercatinoLblPrezzo = [
                'fisso' => '€',
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
            $mercatinoCondOrder = ['nuovo', 'buono', 'discreto', 'altro'];
        @endphp

        @if(!isset($annunci) || $annunci->isEmpty())
            <div class="alert alert-light border">
                Nessun annuncio pubblicato al momento.
            </div>
        @else
            <div class="row g-3">
                @foreach($annunci as $annuncio)
                    @php
                        $folder = $annuncio['cartella'] ?? '';
                        $d = $annuncio['dati'] ?? [];
                        $fotoCount = (int) ($d['foto_caricate'] ?? 0);
                        $autore = trim((string) ($d['autore_username'] ?? ''));
                        if ($autore === '') {
                            $autore = 'Utente';
                        }
                        $isAdminViewer = auth()->check() && auth()->user()->isAdmin();
                        $isOwnerViewer = auth()->check()
                            && $autore !== ''
                            && mb_strtolower((string) auth()->user()->username, 'UTF-8') === mb_strtolower($autore, 'UTF-8');
                        $mercatinoScadenzaAnnuncio = \App\Support\MercatinoAnnuncioStorage::expiresAt($d);

                        $fotoUrls = [];
                        $fotoSlots = [1 => null, 2 => null, 3 => null];
                        for ($i = 1; $i <= 3; $i++) {
                            $url = \App\Support\MercatinoAnnuncioStorage::photoPublicUrl($folder, $i);
                            if ($url !== null) {
                                $fotoUrls[] = $url;
                                $fotoSlots[$i] = $url;
                            }
                        }
                        $thumbUrl = $fotoSlots[1] ?? ($fotoUrls[0] ?? null);
                        $tipoPrezzo = (string) ($d['tipo_prezzo'] ?? '');
                        $prezzoLabel = $mercatinoLblPrezzo[$tipoPrezzo] ?? ($tipoPrezzo !== '' ? $tipoPrezzo : '—');
                        $hasPrezzoVal = isset($d['prezzo']) && $d['prezzo'] !== '' && $d['prezzo'] !== null;
                        $trattabileAnnuncio = ! empty($d['prezzo_trattabile']) || $tipoPrezzo === 'trattabile';
                        if ($tipoPrezzo === 'trattabile' && ! $hasPrezzoVal) {
                            $prezzoText = 'Trattabile';
                        } elseif (($tipoPrezzo === 'fisso' || ($tipoPrezzo === 'trattabile' && $hasPrezzoVal)) && $hasPrezzoVal) {
                            $prezzoText = '€ ' . number_format((float) $d['prezzo'], 2, ',', '.');
                            if ($trattabileAnnuncio) {
                                $prezzoText .= ' — Trattabile';
                            }
                        } elseif ($tipoPrezzo === 'fisso') {
                            $prezzoText = '€';
                            if ($trattabileAnnuncio) {
                                $prezzoText .= ' — Trattabile';
                            }
                        } else {
                            $prezzoText = $prezzoLabel;
                        }
                        $mercatinoEditCategoria = old('categoria', $d['categoria'] ?? '');
                        $mercatinoEditCondizione = old('condizione', $d['condizione'] ?? '');
                        $mercatinoEditTipoPrezzo = old('tipo_prezzo', $d['tipo_prezzo'] ?? '');
                        if ($mercatinoEditTipoPrezzo === 'trattabile') {
                            $mercatinoEditTipoPrezzo = 'fisso';
                        }
                        $mercatinoEditTrattabile = (old('trattabile') === '1' || old('trattabile') === 1 || old('trattabile') === true)
                            || (
                                old('trattabile') === null && ! $errors->any()
                                && (
                                    ! empty($d['prezzo_trattabile'])
                                    || (($d['tipo_prezzo'] ?? '') === 'trattabile')
                                )
                            );
                    @endphp

                    <div class="col-12">
                        <div class="card shadow-sm mercatino-vetrina-card mercatino-vetrina-list-card" data-mercatino-card="{{ $folder }}">
                            <div class="card-body py-3">
                                <div class="mercatino-vetrina-list-row">
                                    <div class="mercatino-vetrina-thumb">
                                        @if(!empty($thumbUrl))
                                            <img src="{{ $thumbUrl }}" alt="Foto annuncio" loading="lazy" decoding="async">
                                        @else
                                            <i class="fas fa-image" aria-hidden="true"></i>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="mercatino-vetrina-titleline">
                                            {{ $d['titolo'] ?? 'Senza titolo' }}
                                        </div>
                                        <div class="mercatino-vetrina-subline">
                                            {{ $mercatinoLblCat[$d['categoria'] ?? ''] ?? ($d['categoria'] ?? '—') }}
                                            @if(!empty($d['inviato_il']))
                                                — Pubblicato {{ \Carbon\Carbon::parse($d['inviato_il'])->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                            @endif
                                            @if($mercatinoScadenzaAnnuncio)
                                                — Scade il {{ $mercatinoScadenzaAnnuncio->format('d/m/Y H:i') }}
                                            @endif
                                            — Inserzionista: <span class="mercatino-vetrina-seller">{{ $autore }}</span>
                                        </div>
                                    </div>

                                    <div class="mercatino-vetrina-list-price">
                                        <span class="mercatino-vetrina-price-badge">{{ $prezzoText }}</span>
                                    </div>

                                    <div class="mercatino-vetrina-list-actions">
                                        <button id="mercatino-details-open-{{ $folder }}"
                                                class="btn btn-success btn-sm text-white mercatino-vetrina-details-btn"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#mercatinoDetails-{{ $folder }}"
                                                aria-expanded="false"
                                                aria-controls="mercatinoDetails-{{ $folder }}">
                                            <i class="fas fa-chevron-down me-1"></i> Dettagli
                                        </button>
                                        <button id="mercatino-details-close-{{ $folder }}"
                                                class="btn btn-success btn-sm text-white mercatino-vetrina-details-btn d-none"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#mercatinoDetails-{{ $folder }}"
                                                aria-expanded="true"
                                                aria-controls="mercatinoDetails-{{ $folder }}">
                                            <i class="fas fa-chevron-up me-1"></i> Chiudi
                                        </button>
                                    </div>
                                </div>

                                <div class="collapse mt-3" id="mercatinoDetails-{{ $folder }}" data-mercatino-details="{{ $folder }}">
                                    <div class="mercatino-vetrina-details-box">
                                    <dl class="row small mb-3">
                                        <dt class="col-sm-4">Categoria</dt>
                                        <dd class="col-sm-8">{{ $mercatinoLblCat[$d['categoria'] ?? ''] ?? ($d['categoria'] ?? '—') }}</dd>

                                        <dt class="col-sm-4">Prezzo €.</dt>
                                        <dd class="col-sm-8">
                                            @php
                                                $detTipo = (string) ($d['tipo_prezzo'] ?? '');
                                                $detHas = isset($d['prezzo']) && $d['prezzo'] !== '' && $d['prezzo'] !== null;
                                                $detTratt = ! empty($d['prezzo_trattabile']) || $detTipo === 'trattabile';
                                            @endphp
                                            @if($detTipo === 'trattabile' && ! $detHas)
                                                Trattabile
                                            @elseif($detTipo === 'fisso' || ($detTipo === 'trattabile' && $detHas))
                                                @if($detHas)
                                                    <strong>€ {{ number_format((float) $d['prezzo'], 2, ',', '.') }}</strong>
                                                @else
                                                    —
                                                @endif
                                                @if($detTratt)
                                                    <span class="text-muted"> — Trattabile</span>
                                                @endif
                                            @else
                                                {{ $mercatinoLblPrezzo[$detTipo] ?? ($detTipo !== '' ? $detTipo : '—') }}
                                            @endif
                                        </dd>

                                        <dt class="col-sm-4">Condizione</dt>
                                        <dd class="col-sm-8">{{ $mercatinoLblCond[$d['condizione'] ?? ''] ?? ($d['condizione'] ?? '—') }}</dd>

                                        <dt class="col-sm-4">Zona ritiro</dt>
                                        <dd class="col-sm-8">{{ $d['zona_ritiro'] ?? '—' }}</dd>
                                    </dl>

                                    <div class="small fw-semibold mb-1">Descrizione</div>
                                    <div class="mercatino-vetrina-desc">{{ $d['descrizione'] ?? '' }}</div>

                                    <div class="mt-3">
                                        @if(count($fotoUrls) > 0)
                                            <div class="mercatino-vetrina-photos">
                                                @foreach($fotoUrls as $url)
                                                    <a href="{{ $url }}" target="_blank" rel="noopener" class="mercatino-vetrina-photo">
                                                        <img src="{{ $url }}"
                                                             alt="Foto annuncio"
                                                             loading="lazy"
                                                             decoding="async">
                                                    </a>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="alert alert-light border small mb-0">
                                                Nessuna foto allegata.
                                            </div>
                                        @endif
                                        @if($fotoCount > 0 && count($fotoUrls) === 0)
                                            <div class="small text-muted mt-2">
                                                Nota: risultano {{ $fotoCount }} foto caricate, ma non sono state trovate in vetrina.
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mt-3 d-flex flex-wrap align-items-center gap-2">
                                        <button type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#mercatinoContactModal-{{ $folder }}">
                                            <i class="fas fa-envelope me-1"></i> Contatta inserzionista
                                        </button>
                                        @if($isOwnerViewer)
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
                                        @endif
                                        @if($isAdminViewer || $isOwnerViewer)
                                            <button type="button"
                                                    class="btn btn-outline-secondary btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#mercatinoEditModal-{{ $folder }}">
                                                <i class="fas fa-pen me-1"></i> Modifica
                                            </button>
                                            <form method="POST"
                                                  action="{{ route('mercatino.annuncio.destroy') }}"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Eliminare questo annuncio dalla vetrina? L’operazione non si può annullare.');">
                                                @csrf
                                                <input type="hidden" name="folder" value="{{ $folder }}">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="fas fa-trash-alt me-1"></i> Elimina annuncio
                                                </button>
                                            </form>
                                        @endif
                                        <span class="small text-muted">
                                            Il messaggio verrà inviato via email all’inserzionista (senza mostrare pubblicamente l’indirizzo).
                                        </span>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="mercatinoContactModal-{{ $folder }}" tabindex="-1"
                         aria-labelledby="mercatinoContactModalLabel-{{ $folder }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="mercatinoContactModalLabel-{{ $folder }}">
                                        Contatta {{ $autore }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="small text-muted mb-2">
                                        Annuncio: <strong>{{ $d['titolo'] ?? 'Senza titolo' }}</strong>
                                    </p>

                                    @guest
                                        <p class="small mb-3">
                                            Per inviare un messaggio all’inserzionista devi avere un account e aver effettuato l’accesso.
                                        </p>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Accedi</a>
                                            <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-sm">Registrati</a>
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Chiudi</button>
                                        </div>
                                    @else
                                    <form method="POST" action="{{ route('mercatino.contact') }}">
                                        @csrf
                                        <input type="hidden" name="folder" value="{{ $folder }}">

                                        <div class="mb-3">
                                            <label for="mercatino_msg_{{ $folder }}" class="form-label">Messaggio</label>
                                            <textarea id="mercatino_msg_{{ $folder }}" name="messaggio" rows="5"
                                                      class="form-control @error('messaggio') is-invalid @enderror"
                                                      maxlength="2000"
                                                      placeholder="Scrivi qui il tuo messaggio...">{{ old('messaggio') }}</textarea>
                                            @error('messaggio')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="alert alert-light border small mb-0">
                                            La risposta avverrà via email. Se non vuoi condividere la tua email, non inviare il messaggio.
                                        </div>

                                        <div class="mt-3 d-flex gap-2">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-paper-plane me-1"></i> Invia messaggio
                                            </button>
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                                                Annulla
                                            </button>
                                        </div>
                                    </form>
                                    @endguest
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($isAdminViewer || $isOwnerViewer)
                        <div class="modal fade mercatino-edit-modal" id="mercatinoEditModal-{{ $folder }}" tabindex="-1"
                             aria-labelledby="mercatinoEditModalLabel-{{ $folder }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="mercatinoEditModalLabel-{{ $folder }}">
                                            Modifica annuncio
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-light border small">
                                            Nota: da qui puoi modificare i dati dell’annuncio. Le foto restano invariate.
                                        </div>

                                        <form method="POST" action="{{ route('mercatino.update') }}" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="folder" value="{{ $folder }}">

                                            <div class="row g-3">
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label" for="edit_titolo_{{ $folder }}">Titolo</label>
                                                    <input type="text"
                                                           id="edit_titolo_{{ $folder }}"
                                                           name="titolo"
                                                           class="form-control"
                                                           maxlength="120"
                                                           value="{{ old('titolo', $d['titolo'] ?? '') }}"
                                                           required>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label" for="edit_categoria_{{ $folder }}">Categoria</label>
                                                    <select id="edit_categoria_{{ $folder }}" name="categoria" class="form-select" required>
                                                        @if($mercatinoEditCategoria !== '' && ! in_array($mercatinoEditCategoria, $mercatinoCatOrder, true))
                                                            <option value="{{ $mercatinoEditCategoria }}" selected>{{ $mercatinoLblCat[$mercatinoEditCategoria] ?? $mercatinoEditCategoria }}</option>
                                                        @endif
                                                        @foreach($mercatinoCatOrder as $catSlug)
                                                            <option value="{{ $catSlug }}" @selected($mercatinoEditCategoria === $catSlug)>{{ $mercatinoLblCat[$catSlug] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label" for="edit_descrizione_{{ $folder }}">Descrizione</label>
                                                    <textarea id="edit_descrizione_{{ $folder }}"
                                                              name="descrizione"
                                                              class="form-control"
                                                              rows="5"
                                                              maxlength="2000"
                                                              required>{{ old('descrizione', $d['descrizione'] ?? '') }}</textarea>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label" for="edit_tipo_prezzo_{{ $folder }}">Prezzo</label>
                                                    <select id="edit_tipo_prezzo_{{ $folder }}" name="tipo_prezzo" class="form-select" required>
                                                        <option value="fisso" @selected($mercatinoEditTipoPrezzo === 'fisso')>Prezzo fisso</option>
                                                        <option value="gratis" @selected($mercatinoEditTipoPrezzo === 'gratis')>Gratis / omaggio</option>
                                                        <option value="scambio" @selected($mercatinoEditTipoPrezzo === 'scambio')>Solo scambio</option>
                                                    </select>
                                                </div>
                                                <div class="col-12 col-md-6" id="mercatino-edit-prezzo-wrap-{{ $folder }}">
                                                    <label class="form-label" for="edit_prezzo_{{ $folder }}">Importo (€)</label>
                                                    <div class="d-flex flex-wrap align-items-end gap-3">
                                                        <input type="number"
                                                               id="edit_prezzo_{{ $folder }}"
                                                               name="prezzo"
                                                               class="form-control flex-grow-1"
                                                               style="min-width: 7rem;"
                                                               step="0.01"
                                                               min="0"
                                                               value="{{ old('prezzo', $d['prezzo'] ?? '') }}">
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input"
                                                                   type="checkbox"
                                                                   name="trattabile"
                                                                   value="1"
                                                                   id="edit_trattabile_{{ $folder }}"
                                                                   @checked($mercatinoEditTrattabile)>
                                                            <label class="form-check-label text-nowrap" for="edit_trattabile_{{ $folder }}">Trattabile</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label" for="edit_condizione_{{ $folder }}">Condizione</label>
                                                    <select id="edit_condizione_{{ $folder }}" name="condizione" class="form-select" required>
                                                        @if($mercatinoEditCondizione !== '' && ! in_array($mercatinoEditCondizione, $mercatinoCondOrder, true))
                                                            <option value="{{ $mercatinoEditCondizione }}" selected>{{ $mercatinoLblCond[$mercatinoEditCondizione] ?? $mercatinoEditCondizione }}</option>
                                                        @endif
                                                        @foreach($mercatinoCondOrder as $condSlug)
                                                            <option value="{{ $condSlug }}" @selected($mercatinoEditCondizione === $condSlug)>{{ $mercatinoLblCond[$condSlug] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label" for="edit_contatto_{{ $folder }}">Contatto</label>
                                                    <select id="edit_contatto_{{ $folder }}" name="contatto" class="form-select" required>
                                                        <option value="excursio" @selected(old('contatto', $d['contatto'] ?? '') === 'excursio')>Messaggi tramite Excursio</option>
                                                        <option value="email" @selected(old('contatto', $d['contatto'] ?? '') === 'email')>Email</option>
                                                        <option value="telefono" @selected(old('contatto', $d['contatto'] ?? '') === 'telefono')>Telefono</option>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label" for="edit_zona_{{ $folder }}">Zona ritiro</label>
                                                    <input type="text"
                                                           id="edit_zona_{{ $folder }}"
                                                           name="zona_ritiro"
                                                           class="form-control"
                                                           maxlength="120"
                                                           value="{{ old('zona_ritiro', $d['zona_ritiro'] ?? '') }}"
                                                           required>
                                                </div>

                                                <div class="col-12">
                                                    <div class="mt-2 fw-semibold">Foto</div>
                                                    <div class="small text-muted mb-2">Puoi sostituire o rimuovere fino a 3 foto (max 4MB ciascuna).</div>
                                                    <div class="row g-3">
                                                        @for($i = 1; $i <= 3; $i++)
                                                            <div class="col-12 col-md-4">
                                                                <div class="border rounded p-2 h-100">
                                                                    <div class="small fw-semibold mb-2">Foto {{ $i }}</div>
                                                                    @if(!empty($fotoSlots[$i]))
                                                                        <a href="{{ $fotoSlots[$i] }}" target="_blank" rel="noopener" class="d-block mb-2">
                                                                            <div class="mercatino-vetrina-photo" style="max-width: 220px;">
                                                                                <img src="{{ $fotoSlots[$i] }}" alt="Foto {{ $i }}">
                                                                            </div>
                                                                        </a>
                                                                        <div class="form-check mb-2">
                                                                            <input class="form-check-input" type="checkbox" value="1" id="remove_foto_{{ $folder }}_{{ $i }}" name="remove_foto_{{ $i }}">
                                                                            <label class="form-check-label small" for="remove_foto_{{ $folder }}_{{ $i }}">
                                                                                Rimuovi foto {{ $i }}
                                                                            </label>
                                                                        </div>
                                                                    @else
                                                                        <div class="alert alert-light border small mb-2">Nessuna foto</div>
                                                                    @endif

                                                                    <label for="edit_foto_{{ $folder }}_{{ $i }}" class="form-label small mb-1">Sostituisci / aggiungi</label>
                                                                    <input type="file"
                                                                           class="form-control form-control-sm"
                                                                           id="edit_foto_{{ $folder }}_{{ $i }}"
                                                                           name="foto_{{ $i }}"
                                                                           accept="image/jpeg,image/png,image/webp,image/gif">
                                                                </div>
                                                            </div>
                                                        @endfor
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3 d-flex gap-2">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-save me-1"></i> Salva modifiche
                                                </button>
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                                                    Chiudi
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                function syncMercatinoEditPrezzoRow(folder) {
                    var tipo = document.getElementById('edit_tipo_prezzo_' + folder);
                    var wrap = document.getElementById('mercatino-edit-prezzo-wrap-' + folder);
                    var input = document.getElementById('edit_prezzo_' + folder);
                    var tratt = document.getElementById('edit_trattabile_' + folder);
                    if (!tipo || !wrap || !input) {
                        return;
                    }
                    var show = tipo.value === 'fisso';
                    wrap.classList.toggle('d-none', !show);
                    input.required = show;
                    if (!show) {
                        input.value = '';
                        if (tratt) {
                            tratt.checked = false;
                        }
                    }
                }

                document.querySelectorAll('[id^="edit_tipo_prezzo_"]').forEach(function (sel) {
                    var folder = sel.id.replace('edit_tipo_prezzo_', '');
                    syncMercatinoEditPrezzoRow(folder);
                });

                document.addEventListener('change', function (e) {
                    var t = e.target;
                    if (!t || t.tagName !== 'SELECT' || !t.id || t.id.indexOf('edit_tipo_prezzo_') !== 0) {
                        return;
                    }
                    syncMercatinoEditPrezzoRow(t.id.replace('edit_tipo_prezzo_', ''));
                });

                (function setupMercatinoVetrinaHeaderImagePreview() {
                    var input = document.getElementById('mercatinoVetrinaHeaderImageInput');
                    var wrap = document.getElementById('mercatinoVetrinaHeaderPreviewWrap');
                    var prev = document.getElementById('mercatinoVetrinaHeaderImagePreview');
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

                document.querySelectorAll('.collapse[id^="mercatinoDetails-"]').forEach(function (coll) {
                    var folder = coll.id.replace('mercatinoDetails-', '');
                    var openBtn = document.getElementById('mercatino-details-open-' + folder);
                    var closeBtn = document.getElementById('mercatino-details-close-' + folder);
                    if (!openBtn || !closeBtn) {
                        return;
                    }
                    coll.addEventListener('shown.bs.collapse', function () {
                        openBtn.classList.add('d-none');
                        closeBtn.classList.remove('d-none');
                        openBtn.setAttribute('aria-expanded', 'true');
                        closeBtn.setAttribute('aria-expanded', 'true');
                    });
                    coll.addEventListener('hidden.bs.collapse', function () {
                        openBtn.classList.remove('d-none');
                        closeBtn.classList.add('d-none');
                        openBtn.setAttribute('aria-expanded', 'false');
                        closeBtn.setAttribute('aria-expanded', 'false');
                    });
                });
            });
        </script>
    @endpush

@endsection

