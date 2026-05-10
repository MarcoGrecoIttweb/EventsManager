@extends('layouts.app')

@section('title', 'Newsletter - Admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card newsletter-send-card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-envelope"></i> Invia Newsletter
                        </h4>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                                @if(session('failed_emails'))
                                    <hr>
                                    <small>
                                        <strong>Invio fallito per:</strong><br>
                                        {{ implode(', ', session('failed_emails')) }}
                                    </small>
                                @endif
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.newsletter.send') }}" method="POST" id="newsletterForm">
                            @csrf
                            <input type="hidden" name="newsletter_test_send_to_receipt_admin" id="newsletter_test_send_to_receipt_admin" value="0">
                            <div id="newsletterExcludeHiddenInputs"></div>

                            <div class="mb-3">
                                <label for="target" class="form-label newsletter-target-label">
                                    Destinatari
                                    <span class="text-danger fw-bold ms-2">Seleziona qui sotto il gruppo a cui inviare email</span>
                                </label>
                                <select class="form-select" id="target" name="target" required onchange="toggleUserSelection()">
                                    <option value="all" style="color:#6c757d; font-weight:800;">Tutti gli utenti</option>
                                    <option value="approved" style="color:#198754; font-weight:800;">Solo utenti attivati</option>
                                    <option value="approved_newsletter_off" style="color:#6f42c1; font-weight:800;">Solo attivati con newsletter disattivata</option>
                                    <option value="news" selected style="color:#0aa2c0; font-weight:800;">Solo utenti con News attiva (newsletter)</option>
                                    <option value="participants" style="color:#b88400; font-weight:800;">Solo utenti che partecipano ad eventi</option>
                                    <option value="never_participated" style="color:#6f42c1; font-weight:800;">Solo utenti che non hanno mai partecipato ad eventi</option>
                                    <option value="pending" style="color:#fd7e14; font-weight:800;">Solo utenti in attesa di approvazione</option>
                                    <option value="low_participation" style="color:#20c997; font-weight:800;">Attivati con meno di 2 eventi</option>
                                </select>
                            </div>

                            <div class="mb-3 border rounded p-3 bg-light" id="newsGroupPanel">
                                <h6 class="mb-2"><i class="fas fa-layer-group me-1"></i> Invio a gruppi (solo con destinatari «<span class="text-success fw-bold">News attiva</span>»</h6>
                                <div id="newsGroupPanelHint" class="alert alert-info py-2 px-3 small mb-3 d-none" role="status">
                                    Seleziona <strong>“Solo utenti con News attiva”</strong> in <strong>Destinatari</strong> per attivare l’invio a gruppi.
                                </div>
                                <p class="small mb-3 newsletter-groups-desc">
                                    Gli iscritti sono ordinati in modo fisso (per ID utente) e divisi in blocchi della dimensione che scegli (es. 80).
                                    Puoi inviare <strong>2 o 3 gruppi per volta</strong> (fino a 5) per non sovraccaricare il server.
                                    Ripeti l’invio con altri gruppi fino a coprire tutti.
                                </p>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="news_send" id="news_send_all" value="all" checked>
                                    <label class="form-check-label" for="news_send_all">
                                        <strong>Tutti</strong> gli iscritti alla newsletter in questo invio
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="news_send" id="news_send_groups" value="groups">
                                    <label class="form-check-label" for="news_send_groups">
                                        <strong>Solo gruppi selezionati</strong> (blocchi da N persone)
                                    </label>
                                </div>

                                @php
                                    if (old('news_receipt_admin_id') !== null) {
                                        $newsReceiptSelectedId = (string) old('news_receipt_admin_id');
                                    } elseif (auth()->check() && auth()->user()->isAdmin()) {
                                        $newsReceiptSelectedId = (string) auth()->user()->userID;
                                    } else {
                                        $newsReceiptSelectedId = '';
                                    }
                                @endphp
                                <div class="mt-3 pt-2 border-top">
                                    <div class="d-flex flex-wrap align-items-end gap-2">
                                        <div class="flex-grow-1" style="min-width: 220px;">
                                            <label for="news_receipt_admin_id" class="form-label small mb-1">
                                                Responsabile riscontro invio (amministratore)
                                            </label>
                                            <select class="form-select form-select-sm" name="news_receipt_admin_id" id="news_receipt_admin_id">
                                                <option value="">Scegli un amministratore...</option>
                                                @foreach($newsletterReceiptAdmins ?? [] as $adm)
                                                    @php
                                                        $__lbl = trim((string) $adm->nome) !== '' ? trim($adm->nome) : 'Senza nome';
                                                        if (!empty($adm->username)) {
                                                            $__lbl .= ' ('.$adm->username.')';
                                                        }
                                                        if (!empty($adm->email)) {
                                                            $__lbl .= ' - '.$adm->email;
                                                        }
                                                    @endphp
                                                    <option value="{{ $adm->userID }}"{{ $newsReceiptSelectedId === (string) $adm->userID ? ' selected' : '' }}>{{ $__lbl }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="pb-1">
                                            <button type="button" class="btn btn-success btn-sm" id="newsletterTestSendToReceiptBtn"
                                                    title="Invia una sola email di prova al responsabile scelto, per controllare oggetto, formattazione e consegna">
                                                <i class="fas fa-envelope me-1"></i>Prova invio al responsabile
                                            </button>
                                            <button type="button" class="btn btn-secondary btn-sm ms-1" id="newsletterReceiptHelpBtn"
                                                    title="Mostra/Nascondi spiegazione">
                                                Leggimi
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-1 d-none" id="newsletterReceiptHelpText">
                                        Scegli chi riceve la mail di prova e, se invii a gruppi, chi viene indicato come referente interno (compare nel messaggio di conferma e nel registro attività).
                                        Il pulsante «Prova invio» manda <strong>solo a quel destinatario</strong> una copia (oggetto con prefisso [PROVA]), senza inviare ai gruppi.
                                    </small>
                                    @php
                                        $__nrAdm = $newsletterReceiptAdmins ?? collect();
                                    @endphp
                                    @if($__nrAdm->isEmpty())
                                        <p class="small text-danger mb-0 mt-1">Nessun amministratore trovato: serve almeno un utente con ruolo Admin.</p>
                                    @endif
                                </div>

                                <div id="newsGroupsFields" class="ps-2 border-start border-2 border-info" style="display: none;">
                                    <div class="mb-2">
                                        <label for="news_group_size" class="form-label small mb-0">Persone per gruppo</label>
                                        <input type="number" class="form-control form-control-sm w-auto" name="news_group_size"
                                               id="news_group_size" value="{{ $newsGroupSizePreview ?? 80 }}" min="20" max="300" step="1">
                                        <small class="text-muted d-block mt-1">Il riquadro verde sotto si aggiorna mentre modifichi il numero: così vedi subito quanti gruppi ci sono e quante persone per gruppo verranno usate in invio.</small>
                                    </div>
                                    <div id="newsGroupSummary" class="alert alert-success py-2 px-3 small mb-2 d-none" role="status">
                                        <strong>Riepilogo invio a gruppi:</strong>
                                        <span id="newsGroupSummaryText"></span>
                                    </div>
                                    <p class="small fw-semibold mb-1 news-groups-prompt">Seleziona i gruppi da includere in questo invio:</p>
                                    <div id="newsGroupsCheckboxArea" class="d-flex flex-column gap-1 mb-2"></div>
                                    <p id="newsGroupsEmptyHint" class="small text-warning mb-0 d-none">Nessun iscritto con email: non ci sono gruppi.</p>

                                    <div class="mt-3 pt-2 border-top">
                                        <button type="button" class="btn btn-secondary btn-sm mb-2" id="previewRecipientsBtn">
                                            <i class="fas fa-list"></i> Mostra elenco destinatari
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Selezione utenti specifici -->
                            <div class="mb-3" id="userSelection" style="display: none;">
                                <label class="form-label">Seleziona Utenti</label>

                                <div class="mb-2">
                                    <input type="text" id="userSearch" class="form-control"
                                           placeholder="Cerca utenti per nome, email o nickname...">
                                </div>

                                <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                    <div class="form-check mb-2">
                                        <input type="checkbox" id="selectAllUsers" class="form-check-input">
                                        <label class="form-check-label fw-bold" for="selectAllUsers">
                                            Seleziona tutti
                                        </label>
                                    </div>
                                    <hr>

                                    <div id="usersList">
                                        @foreach($users as $user)
                                            <div class="form-check mb-2 user-item">
                                                <input type="checkbox" name="selected_users[]"
                                                       value="{{ $user->id }}"
                                                       class="form-check-input user-checkbox"
                                                       id="user_{{ $user->id }}">
                                                <label class="form-check-label" for="user_{{ $user->id }}">
                                                    {{ $user->name }} ({{ $user->nickname }}) - {{ $user->email }}
                                                    <span class="badge bg-{{ $user->status === 'approved' ? 'success' : ($user->status === 'awaiting' ? 'warning' : ($user->status === 'suspended' ? 'secondary' : 'danger')) }} ms-2">
                                                    {{ $user->status }}
                                                </span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div id="usersListNews" style="display:none;">
                                        @foreach(($newsletterActiveUsers ?? collect()) as $user)
                                            <div class="form-check mb-2 user-item">
                                                <input type="checkbox" name="selected_users[]"
                                                       value="{{ $user->id }}"
                                                       class="form-check-input user-checkbox"
                                                       id="user_news_{{ $user->id }}">
                                                <label class="form-check-label" for="user_news_{{ $user->id }}">
                                                    {{ $user->name }} ({{ $user->nickname }}) - {{ $user->email }}
                                                    <span class="badge bg-success ms-2">news</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    Seleziona gli utenti a cui vuoi inviare la newsletter.
                                </small>
                            </div>

                            <div class="mb-3">
                                <input type="text" class="form-control" id="subject" name="subject"
                                       placeholder="Oggetto della newsletter" required>
                            </div>

                            <div class="mb-3">
                                <textarea class="form-control" id="message" name="message"
                                          rows="10" placeholder="Scrivi il contenuto della newsletter..."
                                          required></textarea>
                                <div class="d-flex justify-content-between align-items-end gap-2 mt-2 flex-wrap">
                                    <small class="form-text text-muted mb-0">
                                        Puoi usare HTML base per formattare il testo.
                                    </small>
                                    <button type="submit" class="btn btn-primary btn-sm"
                                            onclick="return confirm('Sei sicuro di voler inviare la newsletter?')">
                                        <i class="fas fa-paper-plane"></i> Invia Newsletter
                                    </button>
                                </div>
                            </div>

                            <div class="alert py-1 px-2 mt-2 mb-3 small" id="newsletterWarningBox" role="status">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Attenzione:</strong> Assicurati del contenuto prima di inviare.
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Anteprima selezione -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Anteprima Selezione</h5>
                    </div>
                    <div class="card-body bg-light">
                        <div id="selectionPreview">
                            <p class="text-muted">Seleziona un'opzione per vedere l'anteprima</p>
                        </div>
                    </div>
                </div>

                <!-- Statistiche -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Statistiche Destinatari</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Tutti gli utenti (non admin):</strong>
                            <span class="badge bg-primary float-end newsletter-stat-open cursor-pointer" role="button" tabindex="0" data-stat-list="all_non_admin" title="Apre l’elenco di tutti gli utenti non amministratori">{{ number_format($totalUsersCount ?? $usersCount) }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Utenti approvati:</strong>
                            <span class="badge bg-success float-end newsletter-stat-open cursor-pointer" role="button" tabindex="0" data-stat-list="approved" title="Apre l’elenco degli utenti approvati / attivati (con o senza News)">{{ number_format($usersCount) }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Con News attiva (newsletter):</strong>
                            <span class="badge bg-info float-end newsletter-stat-open cursor-pointer" role="button" tabindex="0" data-stat-list="news_active" title="Apre l’elenco degli utenti con News attiva e indirizzo email valido">{{ number_format($newsSubscribersCount) }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Attivati con newsletter disattivata:</strong>
                            <span class="badge bg-secondary float-end newsletter-stat-open cursor-pointer" role="button" tabindex="0" data-stat-list="approved_newsletter_off" title="Apre l’elenco degli utenti attivati con newsletter disattivata e indirizzo email valido">{{ number_format($approvedNewsletterOffCount ?? 0) }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Partecipanti ad eventi:</strong>
                            <span class="badge bg-warning text-dark float-end newsletter-stat-open cursor-pointer" role="button" tabindex="0" data-stat-list="participants" title="Apre l’elenco degli utenti attivati iscritti ad almeno un evento">{{ number_format($participantsCount) }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Attivati con meno di 2 eventi:</strong>
                            <span class="float-end d-inline-flex align-items-center flex-wrap justify-content-end gap-1">
                                <span class="badge bg-primary newsletter-stat-open cursor-pointer" role="button" tabindex="0" data-stat-list="low_participation" title="Apre l’elenco degli utenti attivati iscritti a 0 o 1 evento">{{ number_format($lowEventParticipationUsersCount ?? 0) }}</span>
                                <span class="badge bg-light text-dark border" title="Somma delle partecipazioni a eventi di questi utenti (ciascuno ha al massimo 1 evento in questo elenco)">{{ number_format($lowEventParticipationTotalEvents ?? 0) }}</span>
                            </span>
                        </div>
                        <div class="mb-3">
                            <strong>In attesa di approvazione:</strong>
                            <span class="badge bg-secondary float-end newsletter-stat-open cursor-pointer" role="button" tabindex="0" data-stat-list="pending" title="Apre l’elenco degli utenti in attesa di approvazione">{{ number_format($users->where('status', 'awaiting')->count()) }}</span>
                        </div>
                        @if(($newsBatchCount ?? 0) > 0)
                            <div class="mb-3">
                                <strong>Gruppi da ~{{ $newsGroupSizePreview ?? 80 }} iscritti:</strong>
                                <span class="badge bg-dark float-end" title="Numero di blocchi per invio a gruppi (non è un elenco utenti)">{{ $newsBatchCount }}</span>
                            </div>
                        @endif
                        <hr>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> I conteggi con email si riferiscono a utenti non admin con indirizzo valorizzato. <strong>Clicca sul numero colorato</strong> per aprire l’elenco (max 2000 righe).
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="newsGroupRecipientsModal" tabindex="-1" aria-labelledby="newsGroupRecipientsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newsGroupRecipientsModalLabel">Destinatari del gruppo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2" id="newsGroupRecipientsModalMeta"></p>
                    <div id="newsGroupRecipientsModalBody"></div>
                </div>
                <div class="modal-footer flex-column align-items-stretch">
                    <p class="small text-muted mb-0">
                        La spunta indica che il destinatario sarà incluso nell’invio. Toglila per escluderlo manualmente
                        (resta escluso per tutti i gruppi selezionati in questo invio).
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="newsletterPreviewRecipientsModal" tabindex="-1" aria-labelledby="newsletterPreviewRecipientsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newsletterPreviewRecipientsModalLabel">Elenco destinatari</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2" id="newsletterPreviewRecipientsMeta"></p>
                    <div id="newsletterPreviewRecipientsBody"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="newsletterStatListModal" tabindex="-1" aria-labelledby="newsletterStatListModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newsletterStatListModalLabel">Elenco utenti</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2" id="newsletterStatListModalMeta"></p>
                    <div id="newsletterStatListModalBody"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.NEWS_SUBSCRIBERS_TOTAL = {{ (int) ($newsSubscribersCount ?? 0) }};
        window.TARGET_EMAIL_TOTALS = @json($targetEmailTotals ?? []);
        window.NEWS_GROUP_RECIPIENTS_URL = @json(route('admin.newsletter.group-recipients'));
        window.NEWSLETTER_PREVIEW_RECIPIENTS_URL = @json(route('admin.newsletter.preview-recipients'));
        window.NEWSLETTER_STAT_RECIPIENTS_URL = @json(route('admin.newsletter.stat-recipients'));
        window.NEWSLETTER_EXCLUDED_IDS = Object.create(null);

        function newsletterStatStatusLabel(st) {
            switch (st) {
                case 'approved': return 'Attivo';
                case 'awaiting': return 'In attesa';
                case 'suspended': return 'Sospeso';
                case 'banned': return 'Bannato';
                case 'admin': return 'Admin';
                default: return st || '—';
            }
        }

        function openNewsletterStatListModal(listKey) {
            var modalEl = document.getElementById('newsletterStatListModal');
            var titleEl = document.getElementById('newsletterStatListModalLabel');
            var metaEl = document.getElementById('newsletterStatListModalMeta');
            var bodyEl = document.getElementById('newsletterStatListModalBody');
            if (!modalEl || !titleEl || !metaEl || !bodyEl) return;

            titleEl.textContent = 'Caricamento…';
            metaEl.textContent = '';
            bodyEl.innerHTML = '<p class="text-muted mb-0"><i class="fas fa-spinner fa-spin me-1"></i>Caricamento elenco…</p>';

            var url = new URL(window.NEWSLETTER_STAT_RECIPIENTS_URL, window.location.origin);
            url.searchParams.set('list', listKey);

            fetch(url.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (r) {
                    return r.json().then(function (data) {
                        return { ok: r.ok, data: data };
                    });
                })
                .then(function (res) {
                    if (!res.ok || !res.data) {
                        var err = (res.data && res.data.message) ? res.data.message : 'Impossibile caricare l’elenco.';
                        titleEl.textContent = 'Errore';
                        bodyEl.innerHTML = '<p class="text-danger mb-0">' + escapeHtmlSimple(err) + '</p>';
                        return;
                    }
                    var d = res.data;
                    titleEl.textContent = d.title || 'Elenco utenti';
                    var meta = 'Totale in anagrafica: <strong>' + (d.total != null ? d.total : 0) + '</strong>';
                    if (d.truncated) {
                        meta += ' · mostrati i primi <strong>' + (d.shown != null ? d.shown : 0) + '</strong> (limite ' + (d.max_rows || 2000) + ')';
                    } else {
                        meta += ' · righe mostrate: <strong>' + (d.shown != null ? d.shown : 0) + '</strong>';
                    }
                    metaEl.innerHTML = meta;

                    var showEventsCol = d.list === 'low_participation';
                    var thEvents = showEventsCol ? '<th class="text-center">Eventi</th>' : '';
                    var colCount = showEventsCol ? 5 : 4;

                    var rows = (d.users || []).map(function (u) {
                        var em = u.email || '';
                        var nick = u.nickname ? ' <span class="text-muted">(' + escapeHtmlSimple(u.nickname) + ')</span>' : '';
                        var newsOn = u.newsletter ? '<span class="badge bg-info">News on</span>' : '<span class="badge bg-secondary">News off</span>';
                        var st = newsletterStatStatusLabel(u.status);
                        var evCell = showEventsCol
                            ? '<td class="text-center">' + (u.events_count != null ? String(u.events_count) : '0') + '</td>'
                            : '';
                        return '<tr><td>' + escapeHtmlSimple(u.name || '') + nick + '</td><td><a href="mailto:' + encodeURIComponent(em) + '">' + escapeHtmlSimple(em) + '</a></td><td class="text-center">' + newsOn + '</td>' + evCell + '<td class="small">' + escapeHtmlSimple(st) + '</td></tr>';
                    }).join('');

                    bodyEl.innerHTML =
                        '<div class="table-responsive"><table class="table table-sm table-striped table-hover mb-0">' +
                        '<thead><tr><th>Nome</th><th>Email</th><th class="text-center">Newsletter</th>' + thEvents + '<th>Stato</th></tr></thead><tbody>' +
                        (rows || '<tr><td colspan="' + colCount + '" class="text-muted">Nessun utente</td></tr>') +
                        '</tbody></table></div>';

                    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                })
                .catch(function () {
                    titleEl.textContent = 'Errore';
                    bodyEl.innerHTML = '<p class="text-danger mb-0">Errore di rete.</p>';
                    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                });
        }

        document.querySelectorAll('.newsletter-stat-open[data-stat-list]').forEach(function (el) {
            el.addEventListener('click', function () {
                var k = el.getAttribute('data-stat-list');
                if (k) openNewsletterStatListModal(k);
            });
            el.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    var k = el.getAttribute('data-stat-list');
                    if (k) openNewsletterStatListModal(k);
                }
            });
        });

        function clearNewsletterManualExclusions() {
            window.NEWSLETTER_EXCLUDED_IDS = Object.create(null);
            syncNewsletterExcludeHiddenInputs();
        }

        function syncNewsletterExcludeHiddenInputs() {
            var box = document.getElementById('newsletterExcludeHiddenInputs');
            if (!box) {
                return;
            }
            box.innerHTML = '';
            Object.keys(window.NEWSLETTER_EXCLUDED_IDS).forEach(function (id) {
                if (!window.NEWSLETTER_EXCLUDED_IDS[id]) {
                    return;
                }
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'exclude_newsletter_users[]';
                inp.value = id;
                box.appendChild(inp);
            });
        }

        function countNewsletterManualExclusions() {
            return Object.keys(window.NEWSLETTER_EXCLUDED_IDS).filter(function (k) {
                return window.NEWSLETTER_EXCLUDED_IDS[k];
            }).length;
        }

        function clampNewsGroupSize(raw) {
            var n = parseInt(raw, 10);
            if (isNaN(n)) {
                n = 80;
            }
            return Math.min(300, Math.max(20, n));
        }

        function refreshNewsGroupsCheckboxes() {
            var selTargetEl = document.getElementById('target');
            var v = selTargetEl ? (selTargetEl.value || 'news') : 'news';
            var totals = window.TARGET_EMAIL_TOTALS || {};
            var total = parseInt(totals[v] || 0, 10) || 0;
            var sizeInput = document.getElementById('news_group_size');
            var area = document.getElementById('newsGroupsCheckboxArea');
            var summary = document.getElementById('newsGroupSummary');
            var summaryText = document.getElementById('newsGroupSummaryText');
            var emptyHint = document.getElementById('newsGroupsEmptyHint');
            if (!area || !summary || !summaryText || !emptyHint) {
                return;
            }

            var size = clampNewsGroupSize(sizeInput ? sizeInput.value : 80);
            if (sizeInput) {
                sizeInput.value = size;
            }

            if (total === 0) {
                area.innerHTML = '';
                summary.classList.add('d-none');
                emptyHint.classList.remove('d-none');
                return;
            }
            emptyHint.classList.add('d-none');

            var numGroups = Math.ceil(total / size);
            summaryText.innerHTML =
                'persone per gruppo: <strong>' + size + '</strong> · destinatari: <strong>' + total + '</strong> · ' +
                'gruppi totali: <strong>' + numGroups + '</strong> (posizioni 1–' + total + ' nell’ordine fisso per ID utente).';
            summary.classList.remove('d-none');

            var prev = {};
            document.querySelectorAll('.news-group-cb').forEach(function (cb) {
                prev[String(cb.value)] = cb.checked;
            });

            area.innerHTML = '';
            for (var i = 1; i <= numGroups; i++) {
                var from = (i - 1) * size + 1;
                var to = Math.min(i * size, total);
                var cnt = to - from + 1;
                var wrap = document.createElement('div');
                wrap.className = 'd-flex flex-wrap align-items-center gap-2 mb-1';
                var id = 'news_g_dyn_' + i;
                wrap.innerHTML =
                    '<div class="form-check m-0">' +
                    '<input class="form-check-input news-group-cb" type="checkbox" name="news_groups[]" value="' + i + '" id="' + id + '">' +
                    '<label class="form-check-label small mb-0" for="' + id + '">Gruppo <strong>' + i + '</strong> ' +
                    '<span class="text-muted">(pos. ' + from + '–' + to + ', ' + cnt + ' ut.)</span></label>' +
                    '</div>' +
                    '<button type="button" class="btn btn-outline-secondary btn-sm news-group-preview-btn" data-group="' + i + '" ' +
                    'title="Elenco nomi ed email che riceveranno il messaggio in questo gruppo">' +
                    '<i class="fas fa-users me-1"></i>Destinatari</button>';
                area.appendChild(wrap);
                var cb = wrap.querySelector('input');
                if (prev[String(i)]) {
                    cb.checked = true;
                }
                cb.addEventListener('change', updateSelectionPreview);
            }
        }

        function escapeHtmlNews(str) {
            if (str == null || str === undefined) {
                return '';
            }
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function openNewsGroupRecipientsModal(groupIndex) {
            var sizeInput = document.getElementById('news_group_size');
            var size = clampNewsGroupSize(sizeInput ? sizeInput.value : 80);
            if (sizeInput) {
                sizeInput.value = size;
            }
            var modalEl = document.getElementById('newsGroupRecipientsModal');
            var bodyEl = document.getElementById('newsGroupRecipientsModalBody');
            var metaEl = document.getElementById('newsGroupRecipientsModalMeta');
            if (!modalEl || !bodyEl || !metaEl) {
                return;
            }
            metaEl.textContent = 'Gruppo ' + groupIndex + ' · persone per gruppo: ' + size + ' (come in invio)';
            bodyEl.innerHTML = '<p class="text-muted mb-0"><i class="fas fa-spinner fa-spin me-1"></i>Caricamento elenco…</p>';
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();

            var url = new URL(window.NEWS_GROUP_RECIPIENTS_URL, window.location.origin);
            var targetSel = document.getElementById('target');
            var t = targetSel ? (targetSel.value || 'news') : 'news';
            url.searchParams.set('target', t);
            url.searchParams.set('group', String(groupIndex));
            url.searchParams.set('news_group_size', String(size));

            fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(function (r) {
                    return r.json().then(function (data) {
                        return { ok: r.ok, data: data };
                    });
                })
                .then(function (res) {
                    if (!res.ok) {
                        var err = 'Impossibile caricare l’elenco.';
                        if (res.data) {
                            if (res.data.error) {
                                err = res.data.error;
                            } else if (res.data.message) {
                                err = res.data.message;
                            }
                        }
                        bodyEl.innerHTML = '<p class="text-danger mb-0">' + escapeHtmlNews(err) + '</p>';
                        return;
                    }
                    var d = res.data;
                    metaEl.textContent =
                        'Gruppo ' + d.group + ' di ' + d.total_groups + ' · ' + d.count + ' destinatari · ' +
                        d.news_group_size + ' persone/gruppo (ordine per ID utente, come in invio)';
                    var rows = (d.recipients || []).map(function (u) {
                        var uid = String(u.id);
                        var included = !window.NEWSLETTER_EXCLUDED_IDS[uid];
                        var nick = u.nickname ? ' <span class="text-muted">(' + escapeHtmlNews(u.nickname) + ')</span>' : '';
                        var em = u.email || '';
                        var cbId = 'news_rec_inc_' + uid;
                        return '<tr><td class="align-middle text-center" style="width:2.5rem">' +
                            '<input type="checkbox" class="form-check-input news-recipient-include" id="' + cbId + '" ' +
                            'data-user-id="' + uid + '" ' + (included ? 'checked' : '') + ' title="Includi nell’invio">' +
                            '</td><td><label class="mb-0 small" for="' + cbId + '">' + escapeHtmlNews(u.name) + nick + '</label></td>' +
                            '<td><a href="mailto:' + encodeURIComponent(em) + '">' + escapeHtmlNews(em) + '</a></td></tr>';
                    }).join('');
                    bodyEl.innerHTML =
                        '<div class="table-responsive"><table class="table table-sm table-striped mb-0">' +
                        '<thead><tr><th class="text-center small">Includi</th><th>Nome</th><th>Email</th></tr></thead><tbody>' +
                        rows + '</tbody></table></div>';
                })
                .catch(function () {
                    bodyEl.innerHTML = '<p class="text-danger mb-0">Errore di rete. Riprova.</p>';
                });
        }

        function escapeHtmlSimple(str) {
            if (str == null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function gatherNewsletterPreviewPayload() {
            var targetEl = document.getElementById('target');
            var target = targetEl ? targetEl.value : 'news';
            var payload = {
                target: target
            };

            // manual excludes (sempre, per qualunque target)
            var exAll = [];
            Object.keys(window.NEWSLETTER_EXCLUDED_IDS || {}).forEach(function (k) {
                if (window.NEWSLETTER_EXCLUDED_IDS[k]) exAll.push(parseInt(k, 10));
            });
            payload.exclude_newsletter_users = exAll.filter(function (n) { return !isNaN(n); });

            // invio a gruppi (per QUALSIASI target)
            var groupsRadio = document.getElementById('news_send_groups');
            var sendMode = (groupsRadio && groupsRadio.checked) ? 'groups' : 'all';
            payload.news_send = sendMode;
            if (sendMode === 'groups') {
                var sizeEl = document.getElementById('news_group_size');
                var sz = sizeEl ? parseInt(sizeEl.value, 10) : 80;
                if (!isNaN(sz)) payload.news_group_size = sz;
                var gr = [];
                document.querySelectorAll('.news-group-cb:checked').forEach(function (cb) {
                    var v = parseInt(cb.value, 10);
                    if (!isNaN(v)) gr.push(v);
                });
                payload.news_groups = gr;
            }

            return payload;
        }

        function openNewsletterPreviewRecipientsModal() {
            var modalEl = document.getElementById('newsletterPreviewRecipientsModal');
            var bodyEl = document.getElementById('newsletterPreviewRecipientsBody');
            var metaEl = document.getElementById('newsletterPreviewRecipientsMeta');
            if (!modalEl || !bodyEl || !metaEl) return;

            var payload = gatherNewsletterPreviewPayload();
            var titleEl = document.getElementById('newsletterPreviewRecipientsModalLabel');
            if (titleEl) {
                titleEl.textContent = 'Elenco destinatari — ' + (function () {
                    switch (payload.target) {
                        case 'all': return 'Tutti gli utenti';
                        case 'approved': return 'Solo utenti attivati';
                        case 'approved_newsletter_off': return 'Solo attivati con newsletter disattivata';
                        case 'participants': return 'Partecipanti ad eventi';
                        case 'never_participated': return 'Mai partecipato ad eventi';
                        case 'pending': return 'In attesa di approvazione';
                        case 'low_participation': return 'Attivati con meno di 2 eventi';
                        default: return 'Newsletter attiva (News)';
                    }
                })();
            }
            updateTargetSelectTheme();
            metaEl.textContent = 'Calcolo elenco destinatari…';
            bodyEl.innerHTML = '<p class="text-muted mb-0"><i class="fas fa-spinner fa-spin me-1"></i>Caricamento…</p>';
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();

            fetch(window.NEWSLETTER_PREVIEW_RECIPIENTS_URL, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : ''
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            })
                .then(function (r) {
                    return r.json().then(function (data) {
                        return { ok: r.ok, data: data };
                    });
                })
                .then(function (res) {
                    if (!res.ok) {
                        var msg = (res.data && (res.data.message || res.data.error)) ? (res.data.message || res.data.error) : 'Impossibile calcolare i destinatari.';
                        metaEl.textContent = '';
                        bodyEl.innerHTML = '<p class="text-danger mb-0">' + escapeHtmlSimple(msg) + '</p>';
                        return;
                    }
                    var d = res.data || {};
                    var total = d.total || 0;
                    var shown = d.shown || [];
                    var maxShow = d.max_show || shown.length;
                    var excl = countNewsletterManualExclusions();
                    metaEl.textContent =
                        (d.target_label ? (d.target_label + ' · ') : '') +
                        'Totale destinatari: ' + total +
                        (excl > 0 ? ' · Esclusi manualmente: ' + excl : '') +
                        (total > maxShow ? ' · Mostrati: ' + shown.length + ' (limite ' + maxShow + ')' : '');
                    var rows = shown.map(function (u) {
                        var uid = String(u.id);
                        var included = !window.NEWSLETTER_EXCLUDED_IDS[uid];
                        var name = escapeHtmlSimple(u.name || '');
                        var nick = u.nickname ? ' <span class="text-muted">(' + escapeHtmlSimple(u.nickname) + ')</span>' : '';
                        var em = escapeHtmlSimple(u.email || '');
                        var cbId = 'nl_prev_inc_' + uid;
                        return '<tr>' +
                            '<td class="align-middle text-center" style="width:2.5rem">' +
                            '<input type="checkbox" class="form-check-input nl-preview-include" id="' + cbId + '" ' +
                            'data-user-id="' + uid + '" ' + (included ? 'checked' : '') + ' title="Includi nell’invio">' +
                            '</td>' +
                            '<td><label class="mb-0 small" for="' + cbId + '">' + name + nick + '</label></td>' +
                            '<td><a href="mailto:' + encodeURIComponent(u.email || '') + '">' + em + '</a></td>' +
                            '</tr>';
                    }).join('');
                    bodyEl.innerHTML =
                        '<div class="table-responsive"><table class="table table-sm table-striped mb-0">' +
                        '<thead><tr><th class="text-center small">Includi</th><th>Nome</th><th>Email</th></tr></thead><tbody>' +
                        rows + '</tbody></table></div>';
                })
                .catch(function () {
                    metaEl.textContent = '';
                    bodyEl.innerHTML = '<p class="text-danger mb-0">Errore di rete. Riprova.</p>';
                });
        }

        function toggleUserSelection() {
            const target = document.getElementById('target').value;
            const userSelection = document.getElementById('userSelection');
            const newsPanel = document.getElementById('newsGroupPanel');
            if (userSelection) {
                userSelection.style.display = 'none';
            }
            var listAll = document.getElementById('usersList');
            var listNews = document.getElementById('usersListNews');
            if (listAll && listNews) {
                listAll.style.display = 'block';
                listNews.style.display = 'none';
            }
            if (newsPanel) {
                // box sempre disponibile: i gruppi sono calcolati in base al target selezionato
                newsPanel.querySelectorAll('input, select, button, textarea').forEach(function (el) {
                    el.disabled = false;
                });
                var hint = document.getElementById('newsGroupPanelHint');
                if (hint) hint.classList.add('d-none');
            }
            refreshNewsGroupsCheckboxes();
            syncNewsReceiptAdminRequired();
            updateSelectionPreview();
            updateTargetSelectTheme();
        }

        function updateTargetSelectTheme() {
            var sel = document.getElementById('target');
            if (!sel) return;
            var v = sel.value || 'news';
            var classes = [
                'target-theme-all',
                'target-theme-approved',
                'target-theme-approved-news-off',
                'target-theme-news',
                'target-theme-participants',
                'target-theme-never',
                'target-theme-pending',
                'target-theme-low-participation'
            ];
            classes.forEach(function (c) { sel.classList.remove(c); });

            var previewBox = document.getElementById('selectionPreview');
            if (previewBox) {
                classes.forEach(function (c) { previewBox.classList.remove(c); });
            }

            var modalTitle = document.getElementById('newsletterPreviewRecipientsModalLabel');
            if (modalTitle) {
                classes.forEach(function (c) { modalTitle.classList.remove(c); });
            }

            switch (v) {
                case 'all':
                    sel.classList.add('target-theme-all');
                    if (previewBox) previewBox.classList.add('target-theme-all');
                    if (modalTitle) modalTitle.classList.add('target-theme-all');
                    break;
                case 'approved':
                    sel.classList.add('target-theme-approved');
                    if (previewBox) previewBox.classList.add('target-theme-approved');
                    if (modalTitle) modalTitle.classList.add('target-theme-approved');
                    break;
                case 'approved_newsletter_off':
                    sel.classList.add('target-theme-approved-news-off');
                    if (previewBox) previewBox.classList.add('target-theme-approved-news-off');
                    if (modalTitle) modalTitle.classList.add('target-theme-approved-news-off');
                    break;
                case 'participants':
                    sel.classList.add('target-theme-participants');
                    if (previewBox) previewBox.classList.add('target-theme-participants');
                    if (modalTitle) modalTitle.classList.add('target-theme-participants');
                    break;
                case 'never_participated':
                    sel.classList.add('target-theme-never');
                    if (previewBox) previewBox.classList.add('target-theme-never');
                    if (modalTitle) modalTitle.classList.add('target-theme-never');
                    break;
                case 'pending':
                    sel.classList.add('target-theme-pending');
                    if (previewBox) previewBox.classList.add('target-theme-pending');
                    if (modalTitle) modalTitle.classList.add('target-theme-pending');
                    break;
                case 'low_participation':
                    sel.classList.add('target-theme-low-participation');
                    if (previewBox) previewBox.classList.add('target-theme-low-participation');
                    if (modalTitle) modalTitle.classList.add('target-theme-low-participation');
                    break;
                default:
                    sel.classList.add('target-theme-news');
                    if (previewBox) previewBox.classList.add('target-theme-news');
                    if (modalTitle) modalTitle.classList.add('target-theme-news');
                    break; // news
            }

            var infoBox = document.getElementById('targetInfoBox');
            if (infoBox) {
                switch (v) {
                    case 'approved':
                        infoBox.innerHTML = 'Invia email a <strong>tutti gli utenti attivati</strong> con email valida (con o senza newsletter)';
                        break;
                    case 'approved_newsletter_off':
                        infoBox.innerHTML = 'Invia email solo agli <strong>attivati</strong> che hanno <strong>disattivato</strong> la newsletter (flag News off)';
                        break;
                    case 'all':
                        infoBox.innerHTML = 'Invia email a tutti gli utenti';
                        break;
                    case 'pending':
                        infoBox.innerHTML = 'Invia email a gruppi (solo con destinatari <strong>«Sospesi»</strong>)';
                        break;
                    case 'participants':
                        infoBox.innerHTML = 'Invia email a gruppi (solo con destinatari <strong>«Partecipanti ad eventi»</strong>)';
                        break;
                    case 'never_participated':
                        infoBox.innerHTML = 'Invia email a gruppi (solo con destinatari <strong>«Mai partecipato»</strong>)';
                        break;
                    case 'low_participation':
                        infoBox.innerHTML = 'Invia email a gruppi (solo con destinatari <strong>«Attivati con meno di 2 eventi»</strong>)';
                        break;
                    default:
                        infoBox.innerHTML = 'Invia email a gruppi (solo con destinatari <strong>«News attiva»</strong>)';
                        break;
                }
            }
        }

        function syncNewsReceiptAdminRequired() {
            var groupsRadio = document.getElementById('news_send_groups');
            var sel = document.getElementById('news_receipt_admin_id');
            if (!sel) {
                return;
            }
            var need = groupsRadio && groupsRadio.checked;
            sel.required = !!need;
            if (!need) {
                sel.setCustomValidity('');
            }
        }

        function toggleNewsGroupsFields() {
            const groupsRadio = document.getElementById('news_send_groups');
            const box = document.getElementById('newsGroupsFields');
            if (!groupsRadio || !box) return;
            box.style.display = groupsRadio.checked ? 'block' : 'none';
            if (!groupsRadio.checked) {
                clearNewsletterManualExclusions();
            }
            if (groupsRadio.checked) {
                refreshNewsGroupsCheckboxes();
            }
            syncNewsReceiptAdminRequired();
            updateSelectionPreview();
        }

        function updateSelectionPreview() {
            const target = document.getElementById('target').value;
            const preview = document.getElementById('selectionPreview');
            let message = '';
            const groupsOn = document.getElementById('news_send_groups') && document.getElementById('news_send_groups').checked;
            let groupsExtra = '';
            if (groupsOn) {
                const n = document.querySelectorAll('.news-group-cb:checked').length;
                const sz = document.getElementById('news_group_size');
                const s = sz ? sz.value : '';
                const ex = countNewsletterManualExclusions();
                groupsExtra = '<br><small>Modalità gruppi: <strong>' + s + '</strong> persone/gruppo · ' +
                    n + ' gruppi selezionati in questo invio';
                if (ex > 0) {
                    groupsExtra += ' · <strong>' + ex + '</strong> destinatari esclusi manualmente';
                }
                groupsExtra += '</small>';
            }

            switch(target) {
                case 'all':
                    message = '<span><i class="fas fa-users"></i> Tutti gli utenti' + groupsExtra + '</span>';
                    break;
                case 'approved':
                    message = '<span><i class="fas fa-check-circle"></i> Tutti gli utenti attivati' + groupsExtra + '</span>';
                    break;
                case 'approved_newsletter_off':
                    message = '<span><i class="fas fa-newspaper"></i> Attivati con newsletter disattivata' + groupsExtra + '</span>';
                    break;
                case 'news':
                    {
                        message = '<span><i class="fas fa-newspaper"></i> Iscritti newsletter (News attiva)' + groupsExtra + '</span>';
                    }
                    break;
                case 'participants':
                    message = '<span><i class="fas fa-calendar-check"></i> Solo partecipanti ad eventi' + groupsExtra + '</span>';
                    break;
                case 'never_participated':
                    message = '<span><i class="fas fa-user-slash"></i> Solo utenti che non hanno mai partecipato ad eventi' + groupsExtra + '</span>';
                    break;
                case 'pending':
                    message = '<span><i class="fas fa-clock"></i> Solo utenti in attesa' + groupsExtra + '</span>';
                    break;
                case 'low_participation':
                    message = '<span><i class="fas fa-calendar-minus"></i> Attivati con meno di 2 eventi' + groupsExtra + '</span>';
                    break;
            }

            preview.innerHTML = message;
        }

        // Selezione multipla
        var selectAllUsersEl = document.getElementById('selectAllUsers');
        if (selectAllUsersEl) selectAllUsersEl.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectionPreview();
        });

        // Ricerca utenti
        var userSearchEl = document.getElementById('userSearch');
        if (userSearchEl) userSearchEl.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const userItems = document.querySelectorAll('.user-item');

            userItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(searchTerm) ? 'block' : 'none';
            });
        });

        // Aggiorna preview quando si selezionano utenti
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectionPreview);
        });

        document.querySelectorAll('input[name="news_send"]').forEach(function (r) {
            r.addEventListener('change', toggleNewsGroupsFields);
        });

        var newsGroupSizeEl = document.getElementById('news_group_size');
        if (newsGroupSizeEl) {
            newsGroupSizeEl.addEventListener('input', function () {
                refreshNewsGroupsCheckboxes();
                updateSelectionPreview();
            });
            newsGroupSizeEl.addEventListener('change', function () {
                refreshNewsGroupsCheckboxes();
                updateSelectionPreview();
            });
        }

        var newsGroupsCheckboxArea = document.getElementById('newsGroupsCheckboxArea');
        if (newsGroupsCheckboxArea) {
            newsGroupsCheckboxArea.addEventListener('click', function (e) {
                var btn = e.target.closest('.news-group-preview-btn');
                if (!btn) {
                    return;
                }
                e.preventDefault();
                var g = parseInt(btn.getAttribute('data-group'), 10);
                if (!isNaN(g)) {
                    openNewsGroupRecipientsModal(g);
                }
            });
        }

        var newsGroupRecipientsModalEl = document.getElementById('newsGroupRecipientsModal');
        if (newsGroupRecipientsModalEl) {
            newsGroupRecipientsModalEl.addEventListener('change', function (e) {
                var t = e.target;
                if (!t || !t.classList || !t.classList.contains('news-recipient-include')) {
                    return;
                }
                var id = t.getAttribute('data-user-id');
                if (!id) {
                    return;
                }
                if (t.checked) {
                    delete window.NEWSLETTER_EXCLUDED_IDS[id];
                } else {
                    window.NEWSLETTER_EXCLUDED_IDS[id] = true;
                }
                syncNewsletterExcludeHiddenInputs();
                updateSelectionPreview();
            });
        }

        var newsletterFormEl = document.getElementById('newsletterForm');
        if (newsletterFormEl) {
            newsletterFormEl.addEventListener('submit', function () {
                syncNewsletterExcludeHiddenInputs();
            });
        }

        var newsletterTestSendToReceiptBtn = document.getElementById('newsletterTestSendToReceiptBtn');
        if (newsletterTestSendToReceiptBtn && newsletterFormEl) {
            newsletterTestSendToReceiptBtn.addEventListener('click', function () {
                var flag = document.getElementById('newsletter_test_send_to_receipt_admin');
                var sel = document.getElementById('news_receipt_admin_id');
                if (!flag || !sel || !sel.value) {
                    window.alert('Scegli prima l\'amministratore responsabile dall\'elenco.');
                    return;
                }
                var subj = document.getElementById('subject');
                var msg = document.getElementById('message');
                if (!subj || !String(subj.value || '').trim()) {
                    window.alert('Compila l\'oggetto della newsletter.');
                    if (subj) {
                        subj.focus();
                    }
                    return;
                }
                if (!msg || String(msg.value || '').trim().length < 10) {
                    window.alert('Compila il messaggio (almeno 10 caratteri).');
                    if (msg) {
                        msg.focus();
                    }
                    return;
                }
                if (!window.confirm('Verrà inviata una sola email di PROVA al responsabile selezionato. Nessun invio ai gruppi iscritti. Continuare?')) {
                    return;
                }
                flag.value = '1';
                newsletterFormEl.submit();
            });
        }

        var newsletterReceiptHelpBtn = document.getElementById('newsletterReceiptHelpBtn');
        if (newsletterReceiptHelpBtn) {
            newsletterReceiptHelpBtn.addEventListener('click', function () {
                var t = document.getElementById('newsletterReceiptHelpText');
                if (!t) return;
                t.classList.toggle('d-none');
            });
        }

        // Inizializza
        document.addEventListener('DOMContentLoaded', function() {
            var testFlag = document.getElementById('newsletter_test_send_to_receipt_admin');
            if (testFlag) {
                testFlag.value = '0';
            }
            refreshNewsGroupsCheckboxes();
            toggleUserSelection();
            toggleNewsGroupsFields();
            updateSelectionPreview();
            updateTargetSelectTheme();

            var previewBtn = document.getElementById('previewRecipientsBtn');
            if (previewBtn) {
                previewBtn.addEventListener('click', function () {
                    openNewsletterPreviewRecipientsModal();
                });
            }
        });

        // Esclusione manuale dalla MODALE anteprima destinatari
        var previewModalEl = document.getElementById('newsletterPreviewRecipientsModal');
        if (previewModalEl) {
            previewModalEl.addEventListener('change', function (e) {
                var t = e.target;
                if (!t || !t.classList || !t.classList.contains('nl-preview-include')) {
                    return;
                }
                var id = t.getAttribute('data-user-id');
                if (!id) return;
                if (t.checked) {
                    delete window.NEWSLETTER_EXCLUDED_IDS[id];
                } else {
                    window.NEWSLETTER_EXCLUDED_IDS[id] = true;
                }
                syncNewsletterExcludeHiddenInputs();
                updateSelectionPreview();
                // aggiorna la meta (conteggio esclusi)
                var metaEl = document.getElementById('newsletterPreviewRecipientsMeta');
                if (metaEl) {
                    // forziamo refresh rapido riaprendo l'anteprima solo se necessario: qui aggiorniamo solo la parte "Esclusi"
                    // (il totale mostrato è già calcolato dal server).
                    // Non facciamo una nuova fetch per non appesantire.
                }
            });
        }
    </script>

    <style>
        .user-item {
            transition: all 0.3s ease;
        }
        .user-item:hover {
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        #usersList {
            max-height: 250px;
            overflow-y: auto;
        }

        /* Box "Invia Newsletter": bordo blu */
        .newsletter-send-card {
            border: 2px solid rgba(13, 110, 253, 0.55);
        }

        /* Titolo "Destinatari": grassetto blu */
        .newsletter-target-label {
            font-weight: 800;
            color: #0d6efd;
        }

        /* Evidenzia Oggetto/Messaggio in marrone */
        label[for="subject"],
        label[for="message"] {
            font-weight: 800;
            color: #8b5a2b; /* marrone */
        }
        #subject,
        #message {
            border: 2px solid #8b5a2b;
        }
        #subject:focus,
        #message:focus {
            border-color: #8b5a2b;
            box-shadow: 0 0 0 .2rem rgba(139, 90, 43, 0.25);
        }

        /* Box "Attenzione" compatto con contorno marrone */
        #newsletterWarningBox {
            border: 2px solid #8b5a2b;
            background-color: rgba(139, 90, 43, 0.08);
            color: #5a3a1b;
        }

        /* Prompt "Seleziona i gruppi..." rosso lampeggiante */
        @keyframes nl-red-blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.25; }
        }
        #newsGroupPanel .news-groups-prompt {
            color: #dc3545; /* rosso bootstrap */
            animation: nl-red-blink 1.1s ease-in-out infinite;
        }

        /* Box "Invio a gruppi": bordo blu + radio blu */
        #newsGroupPanel {
            border: 2px solid #0d6efd !important;
        }
        #newsGroupPanel .newsletter-groups-desc {
            color: #084298 !important;
            font-weight: 700;
            border-left: 4px solid #084298;
            padding-left: 0.6rem;
        }
        /* Bordi blu per tutte le caselle dentro "Invio a gruppi" */
        #newsGroupPanel .form-control,
        #newsGroupPanel .form-select,
        #newsGroupPanel input[type="number"],
        #newsGroupPanel input[type="text"],
        #newsGroupPanel textarea {
            border-color: #0d6efd !important;
        }
        #newsGroupPanel .form-check-input {
            border-color: #0d6efd !important;
        }
        #newsGroupPanel .form-check-input[type="radio"] {
            accent-color: #0d6efd;
            /* bordo grigio per rendere evidente il pallino */
            background-color: #fff;
            border: 2px solid #6c757d;
            box-shadow: none;
        }
        #newsGroupPanel .form-check-input[type="radio"]:checked {
            border-color: #0d6efd;
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, 0.25);
        }

        /* Rende evidente che "Destinatari" è una combo (freccetta a destra) */
        #target {
            font-weight: 800; /* testo selezionato in grassetto */
            padding-right: 2.75rem; /* spazio per la freccetta */
            cursor: pointer;
            background-repeat: no-repeat;
            background-position: right .85rem center;
            background-size: 1.15rem 1.15rem;
            /* freccetta più visibile (override dell'icona bootstrap) */
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23212529' stroke-linecap='round' stroke-linejoin='round' stroke-width='2.25' d='M2.5 5.75l5.5 5.5 5.5-5.5'/%3e%3c/svg%3e");
        }

        /* Voci del menu in grassetto (supporto variabile per browser/OS) */
        #target option {
            font-weight: 800;
        }

        /* Evidenziazione casella combinata "Destinatari" in base al target */
        #target.target-theme-all {
            border: 2px solid #6c757d !important;
            background-color: rgba(108, 117, 125, 0.10) !important;
        }
        #target.target-theme-approved {
            border: 2px solid #198754 !important;
            background-color: rgba(25, 135, 84, 0.12) !important;
        }
        #target.target-theme-approved-news-off {
            border: 2px solid #6610f2 !important;
            background-color: rgba(102, 16, 242, 0.10) !important;
        }
        #target.target-theme-news {
            border: 2px solid #0dcaf0 !important;
            background-color: rgba(13, 202, 240, 0.12) !important;
        }
        #target.target-theme-participants {
            border: 2px solid #ffc107 !important;
            background-color: rgba(255, 193, 7, 0.14) !important;
        }
        #target.target-theme-never {
            border: 2px solid #6f42c1 !important;
            background-color: rgba(111, 66, 193, 0.12) !important;
        }
        #target.target-theme-pending {
            border: 2px solid #fd7e14 !important;
            background-color: rgba(253, 126, 20, 0.12) !important;
        }
        #target.target-theme-low-participation {
            border: 2px solid #20c997 !important;
            background-color: rgba(32, 201, 151, 0.14) !important;
        }

        /* Titoli/anteprime: colori in base al target */
        #selectionPreview {
            color: #8b5a2b !important;
        }
        #selectionPreview small {
            color: #8b5a2b !important;
        }

        #newsletterPreviewRecipientsModalLabel.target-theme-all { color: #6c757d; }
        #newsletterPreviewRecipientsModalLabel.target-theme-approved { color: #198754; }
        #newsletterPreviewRecipientsModalLabel.target-theme-approved-news-off { color: #6610f2; }
        #newsletterPreviewRecipientsModalLabel.target-theme-news { color: #0aa2c0; }
        #newsletterPreviewRecipientsModalLabel.target-theme-participants { color: #b88400; }
        #newsletterPreviewRecipientsModalLabel.target-theme-never { color: #6f42c1; }
        #newsletterPreviewRecipientsModalLabel.target-theme-pending { color: #fd7e14; }
        #newsletterPreviewRecipientsModalLabel.target-theme-low-participation { color: #20c997; }

    </style>
@endsection
