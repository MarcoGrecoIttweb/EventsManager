<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stampa partecipanti — {{ $event->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            color: #212529;
        }

        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            padding: 1rem;
            margin: -0.5rem -0.5rem 1.5rem;
        }

        .print-doc h1 {
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }

        .print-meta {
            font-size: 0.9rem;
            color: #495057;
            margin-bottom: 1rem;
        }

        .print-table th {
            background: #e9ecef;
            font-weight: 600;
            white-space: nowrap;
        }

        .print-table td,
        .print-table th {
            vertical-align: middle;
            font-size: 0.875rem;
            white-space: nowrap;
        }

        .guest-row-print td {
            background-color: #f8f9fa !important;
            font-size: 0.8125rem;
        }

        .col-email,
        .col-phone,
        .col-registered {
            display: none;
        }

        .col-registered {
            white-space: nowrap;
        }

        body.show-email .col-email,
        body.show-phone .col-phone,
        body.show-registered .col-registered {
            display: table-cell;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .col-email,
            .col-phone,
            .col-registered {
                display: table-cell !important;
            }

            body:not(.print-with-email) .col-email,
            body:not(.print-with-phone) .col-phone,
            body:not(.print-with-registered) .col-registered {
                display: none !important;
            }

            body {
                padding: 0;
                margin: 0;
            }

            .print-doc {
                max-width: 100%;
            }

            .print-table {
                page-break-inside: auto;
            }

            .print-table tr {
                page-break-after: auto;
            }

            .print-table tr:not(.guest-row-print) {
                page-break-inside: avoid;
            }

            .guest-row-print {
                page-break-inside: avoid;
            }

            thead {
                display: table-header-group;
            }

            .print-description {
                page-break-before: auto;
            }

            .print-description__body {
                page-break-inside: auto;
            }

            .print-description__body,
            .print-description__body * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        .print-description h2 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .print-description__body {
            font-size: 0.9rem;
            line-height: 1.45;
            color: #212529;
        }

        .print-description__body p,
        .print-description__body div,
        .print-description__body ul,
        .print-description__body ol,
        .print-description__body li,
        .print-description__body h1,
        .print-description__body h2,
        .print-description__body h3,
        .print-description__body h4,
        .print-description__body h5,
        .print-description__body h6,
        .print-description__body blockquote {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }

        .print-description__body p:empty,
        .print-description__body div:empty {
            display: none;
        }

        .print-description__body img {
            display: none !important;
        }
    </style>
</head>
<body class="p-3">
    <div class="no-print print-toolbar shadow-sm rounded mb-3">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <strong class="me-2"><i class="fas fa-print"></i> Stampa</strong>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Apri finestra di stampa
            </button>
            <a href="{{ route('events.show', $event) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Torna all'evento
            </a>
        </div>
        <p class="small text-muted mb-2 mb-md-0">
            Puoi anche usare <kbd>Ctrl</kbd>+<kbd>P</kbd> (o <kbd>Cmd</kbd>+<kbd>P</kbd> su Mac).
            Nella finestra del browser trovi orientamento pagina, margini e stampante.
        </p>
        <div class="border-top pt-3 mt-2">
            <span class="small fw-semibold d-block mb-2">Colonne da includere in stampa:</span>
            <div class="d-flex flex-wrap gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="opt-email" data-col="email">
                    <label class="form-check-label" for="opt-email">Email</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="opt-phone" data-col="phone" checked>
                    <label class="form-check-label" for="opt-phone">Telefono</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="opt-registered" data-col="registered" checked>
                    <label class="form-check-label" for="opt-registered">Iscritto / data ospite</label>
                </div>
            </div>
        </div>
    </div>

    <div class="print-doc container-fluid" style="max-width: 1100px;">
        <header class="mb-3">
            <h1>{{ $event->title }}</h1>
            @php
                $postiPrenotati = $event->participants_count;
                $postiTotali = $event->max_participants ? (int) $event->max_participants : null;
                $postiLiberi = $postiTotali !== null ? max(0, $postiTotali - $postiPrenotati) : null;
            @endphp
            <div class="print-meta d-flex flex-wrap gap-3">
                @if($event->italian_event_date)
                    <div><strong>Data evento:</strong> {{ $event->italian_event_date }}</div>
                @endif
                @if($event->city || $event->address)
                    <div>
                        <strong>Luogo:</strong>
                        {{ trim(implode(' — ', array_filter([$event->address, $event->city]))) ?: '—' }}
                    </div>
                @endif
                @if($event->user)
                    <div><strong>Organizzatore:</strong> {{ $event->user->nickname }}</div>
                @endif
                <div>
                    <strong>Posti prenotati:</strong> {{ $postiPrenotati }}
                    <span class="text-muted">(comprende iscritti e ospiti)</span>
                </div>
            </div>
        </header>

        @if($participants->isEmpty())
            <p class="text-muted">Nessun partecipante iscritto.</p>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-sm print-table">
                    <thead>
                    <tr>
                        <th scope="col" style="width:1.8rem; text-align:center;">#</th>
                        <th scope="col" style="width:10rem;">Partecipante</th>
                        <th scope="col" class="col-phone" style="width:6.2rem;">Telefono</th>
                        <th scope="col" style="width:2.8rem; text-align:center;">Ospiti</th>
                        <th scope="col" class="col-email" style="width:10rem;">Email</th>
                        <th scope="col" class="col-registered" style="width:7rem;">Data iscrizione</th>
                        <th scope="col" style="width:14rem;">Note</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($participants as $i => $p)
                        @php
                            $full = trim(implode(' ', array_filter([$p->nome, $p->cognome])));
                            $guestsCount = (int) ($p->pivot->amici ?? 0);
                            $ospitiEntries = \App\Support\OspitiGuestStore::decode($p->pivot->ospiti_inseriti_il ?? null);
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                @if($full !== '')
                                    {{ $full }}
                                @endif
                                @if($p->nickname)
                                    @if($full !== '')
                                        <span class="text-muted">({{ $p->nickname }})</span>
                                    @else
                                        {{ $p->nickname }}
                                    @endif
                                @endif
                            </td>
                            <td class="col-phone">{{ $p->telefono ?: '—' }}</td>
                            <td class="text-center">{{ $guestsCount }}</td>
                            <td class="col-email">{{ $p->email ?: '—' }}</td>
                            <td class="col-registered">
                                @if(!empty($p->pivot->data_iscrizione))
                                    {{ \Illuminate\Support\Carbon::parse($p->pivot->data_iscrizione)->format('d/m/Y H:i') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td></td>
                        </tr>
                        @for($g = 1; $g <= $guestsCount; $g++)
                            @php
                                $gEnt = $ospitiEntries[$g - 1] ?? ['nome' => '', 'at' => ''];
                                $gNome = $gEnt['nome'] ?? '';
                                $gAt = $gEnt['at'] ?? '';
                            @endphp
                            <tr class="guest-row-print">
                                <td class="text-muted text-center">↳</td>
                                <td class="ps-4">
                                    @if($gNome !== '')
                                        <span class="fw-semibold">{{ $gNome }}</span>
                                        <span class="text-muted"> — amico di {{ $p->nickname ?: 'utente #' . $p->getKey() }}</span>
                                    @else
                                        <span class="text-muted">Ospite — amico di {{ $p->nickname ?: 'utente #' . $p->getKey() }}</span>
                                    @endif
                                </td>
                                <td class="col-phone text-muted">—</td>
                                <td class="text-center text-muted">—</td>
                                <td class="col-email text-muted">—</td>
                                <td class="col-registered text-muted">
                                    @if($gAt !== '')
                                        {{ \Illuminate\Support\Carbon::parse($gAt)->format('d/m/Y H:i') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td></td>
                            </tr>
                        @endfor
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if(!empty($printDescriptionHtml))
            <section class="print-description mt-4 pt-3 border-top">
                <h2 class="h5">Descrizione evento</h2>
                <div class="print-description__body">
                    {!! $printDescriptionHtml !!}
                </div>
            </section>
        @endif

        <p class="small text-muted mt-3 no-print">
            Generato il {{ now()->format('d/m/Y H:i') }} — Excursio
        </p>
    </div>

    <script>
        (function () {
            function syncPreview() {
                document.body.classList.toggle('show-email', document.getElementById('opt-email').checked);
                document.body.classList.toggle('show-phone', document.getElementById('opt-phone').checked);
                document.body.classList.toggle('show-registered', document.getElementById('opt-registered').checked);
            }

            function syncPrintClasses() {
                document.body.classList.toggle('print-with-email', document.getElementById('opt-email').checked);
                document.body.classList.toggle('print-with-phone', document.getElementById('opt-phone').checked);
                document.body.classList.toggle('print-with-registered', document.getElementById('opt-registered').checked);
            }

            ['opt-email', 'opt-phone', 'opt-registered'].forEach(function (id) {
                document.getElementById(id).addEventListener('change', syncPreview);
            });

            syncPreview();

            window.addEventListener('beforeprint', syncPrintClasses);
            window.addEventListener('afterprint', function () {
                document.body.classList.remove('print-with-email', 'print-with-phone', 'print-with-registered');
            });
        })();
    </script>
</body>
</html>
