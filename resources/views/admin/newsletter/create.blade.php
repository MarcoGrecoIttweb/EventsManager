@extends('layouts.app')

@section('title', 'Newsletter - Admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
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
                                <label for="target" class="form-label">Destinatari</label>
                                <select class="form-select" id="target" name="target" required onchange="toggleUserSelection()">
                                    <option value="all">Tutti gli utenti</option>
                                    <option value="approved">Solo utenti approvati</option>
                                    <option value="news" selected>Solo utenti con News attiva (newsletter)</option>
                                    <option value="participants">Solo utenti che partecipano ad eventi</option>
                                    <option value="pending">Solo utenti in attesa di approvazione</option>
                                    <option value="selected">Seleziona utenti specifici</option>
                                </select>
                                <small class="form-text text-muted">
                                    <strong>Consigliato:</strong> usa «Solo utenti con News attiva» per inviare solo a chi ha accettato di ricevere comunicazioni (campo <em>News</em> nel profilo).
                                </small>
                            </div>

                            <div class="mb-3 border rounded p-3 bg-light" id="newsGroupPanel" style="display: none;">
                                <h6 class="mb-2"><i class="fas fa-layer-group me-1"></i> Invio a gruppi (solo con destinatari «News attiva»)</h6>
                                <p class="small text-muted mb-3">
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
                                    <p class="small fw-semibold mb-1">Seleziona i gruppi da includere in questo invio:</p>
                                    <div id="newsGroupsCheckboxArea" class="d-flex flex-column gap-1 mb-2"></div>
                                    <p id="newsGroupsEmptyHint" class="small text-warning mb-0 d-none">Nessun iscritto con email: non ci sono gruppi.</p>

                                    <div class="mt-3 pt-2 border-top">
                                        @php
                                            if (old('news_receipt_admin_id') !== null) {
                                                $newsReceiptSelectedId = (string) old('news_receipt_admin_id');
                                            } elseif (auth()->check() && auth()->user()->isAdmin()) {
                                                $newsReceiptSelectedId = (string) auth()->user()->userID;
                                            } else {
                                                $newsReceiptSelectedId = '';
                                            }
                                        @endphp
                                        <div class="d-flex flex-wrap align-items-end gap-2">
                                            <div class="flex-grow-1" style="min-width: 220px;">
                                                <label for="news_receipt_admin_id" class="form-label small mb-1">
                                                    Responsabile riscontro invio (amministratore) <span class="text-danger">*</span>
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
                                                <button type="button" class="btn btn-outline-secondary btn-sm" id="newsletterTestSendToReceiptBtn"
                                                        title="Invia una sola email di prova al responsabile scelto, per controllare oggetto, formattazione e consegna">
                                                    <i class="fas fa-envelope me-1"></i>Prova invio al responsabile
                                                </button>
                                            </div>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            Solo per invio a gruppi: scegli chi viene indicato come referente interno per questo invio (compare nel messaggio di conferma e nel registro attività).
                                            Il pulsante «Prova invio» manda <strong>solo a quel destinatario</strong> una copia (oggetto con prefisso [PROVA]), senza inviare ai gruppi.
                                        </small>
                                        @php
                                            $__nrAdm = $newsletterReceiptAdmins ?? collect();
                                        @endphp
                                        @if($__nrAdm->isEmpty())
                                            <p class="small text-danger mb-0 mt-1">Nessun amministratore trovato: serve almeno un utente con ruolo Admin.</p>
                                        @endif
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
                                                    <span class="badge bg-{{ $user->status === 'approved' ? 'success' : ($user->status === 'pending' ? 'warning' : 'danger') }} ms-2">
                                                    {{ $user->status }}
                                                </span>
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
                                <label for="subject" class="form-label">Oggetto</label>
                                <input type="text" class="form-control" id="subject" name="subject"
                                       placeholder="Oggetto della newsletter" required>
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label">Messaggio</label>
                                <textarea class="form-control" id="message" name="message"
                                          rows="10" placeholder="Scrivi il contenuto della newsletter..."
                                          required></textarea>
                                <small class="form-text text-muted">
                                    Puoi usare HTML base per formattare il testo.
                                </small>
                            </div>

                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Attenzione:</strong> Questa newsletter verrà inviata a tutti gli utenti selezionati.
                                Assicurati del contenuto prima di inviare.
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg"
                                    onclick="return confirm('Sei sicuro di voler inviare la newsletter?')">
                                <i class="fas fa-paper-plane"></i> Invia Newsletter
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Statistiche -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Statistiche Destinatari</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Tutti gli utenti (non admin):</strong>
                            <span class="badge bg-primary float-end">{{ number_format($totalUsersCount ?? $usersCount) }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Utenti approvati:</strong>
                            <span class="badge bg-success float-end">{{ number_format($usersCount) }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Con News attiva (newsletter):</strong>
                            <span class="badge bg-info float-end">{{ number_format($newsSubscribersCount) }}</span>
                        </div>
                        @if(($newsBatchCount ?? 0) > 0)
                            <div class="mb-3">
                                <strong>Gruppi da ~{{ $newsGroupSizePreview ?? 80 }} iscritti:</strong>
                                <span class="badge bg-dark float-end">{{ $newsBatchCount }}</span>
                            </div>
                        @endif
                        <div class="mb-3">
                            <strong>Partecipanti ad eventi:</strong>
                            <span class="badge bg-warning float-end">{{ number_format($participantsCount) }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>In attesa di approvazione:</strong>
                            <span class="badge bg-secondary float-end">{{ number_format($users->where('status', 'pending')->count()) }}</span>
                        </div>
                        <hr>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> I numeri si aggiornano in tempo reale.
                        </small>
                    </div>
                </div>

                <!-- Anteprima selezione -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Anteprima Selezione</h5>
                    </div>
                    <div class="card-body">
                        <div id="selectionPreview">
                            <p class="text-muted">Seleziona un'opzione per vedere l'anteprima</p>
                        </div>
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

    <script>
        window.NEWS_SUBSCRIBERS_TOTAL = {{ (int) ($newsSubscribersCount ?? 0) }};
        window.NEWS_GROUP_RECIPIENTS_URL = @json(route('admin.newsletter.group-recipients'));
        window.NEWSLETTER_EXCLUDED_IDS = Object.create(null);

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
            var total = window.NEWS_SUBSCRIBERS_TOTAL || 0;
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
                'persone per gruppo: <strong>' + size + '</strong> · iscritti newsletter: <strong>' + total + '</strong> · ' +
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

        function toggleUserSelection() {
            const target = document.getElementById('target').value;
            const userSelection = document.getElementById('userSelection');
            const newsPanel = document.getElementById('newsGroupPanel');
            userSelection.style.display = target === 'selected' ? 'block' : 'none';
            if (newsPanel) {
                newsPanel.style.display = target === 'news' ? 'block' : 'none';
            }
            if (target !== 'news') {
                clearNewsletterManualExclusions();
                const allRadio = document.getElementById('news_send_all');
                if (allRadio) {
                    allRadio.checked = true;
                }
                toggleNewsGroupsFields();
            }
            if (target === 'news') {
                refreshNewsGroupsCheckboxes();
            } else {
                var rsa = document.getElementById('news_receipt_admin_id');
                if (rsa) {
                    rsa.required = false;
                    rsa.setCustomValidity('');
                }
            }
            syncNewsReceiptAdminRequired();
            updateSelectionPreview();
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

            switch(target) {
                case 'all':
                    message = '<span class="text-success"><i class="fas fa-users"></i> Tutti gli utenti</span>';
                    break;
                case 'approved':
                    message = '<span class="text-success"><i class="fas fa-check-circle"></i> Solo utenti approvati</span>';
                    break;
                case 'news':
                    {
                        const groupsOn = document.getElementById('news_send_groups') && document.getElementById('news_send_groups').checked;
                        let extra = '';
                        if (groupsOn) {
                            const n = document.querySelectorAll('.news-group-cb:checked').length;
                            const sz = document.getElementById('news_group_size');
                            const s = sz ? sz.value : '';
                            const ex = countNewsletterManualExclusions();
                            extra = '<br><small class="text-muted">Modalità gruppi: <strong>' + s + '</strong> persone/gruppo · ' +
                                n + ' gruppi selezionati in questo invio';
                            if (ex > 0) {
                                extra += ' · <strong>' + ex + '</strong> destinatari esclusi manualmente';
                            }
                            extra += '</small>';
                        }
                        message = '<span class="text-info"><i class="fas fa-newspaper"></i> Iscritti newsletter (News attiva)' + extra + '</span>';
                    }
                    break;
                case 'participants':
                    message = '<span class="text-warning"><i class="fas fa-calendar-check"></i> Solo partecipanti ad eventi</span>';
                    break;
                case 'pending':
                    message = '<span class="text-warning"><i class="fas fa-clock"></i> Solo utenti in attesa</span>';
                    break;
                case 'selected':
                    const selectedCount = document.querySelectorAll('.user-checkbox:checked').length;
                    message = `<span class="text-info"><i class="fas fa-user-check"></i> ${selectedCount} utenti selezionati</span>`;
                    break;
            }

            preview.innerHTML = message;
        }

        // Selezione multipla
        document.getElementById('selectAllUsers').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectionPreview();
        });

        // Ricerca utenti
        document.getElementById('userSearch').addEventListener('input', function() {
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
                var groupsRadio = document.getElementById('news_send_groups');
                if (!groupsRadio || !groupsRadio.checked) {
                    window.alert('Attiva prima «Solo gruppi selezionati» e compila i campi obbligatori.');
                    return;
                }
                if (!window.confirm('Verrà inviata una sola email di PROVA al responsabile selezionato. Nessun invio ai gruppi iscritti. Continuare?')) {
                    return;
                }
                flag.value = '1';
                newsletterFormEl.submit();
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
        });
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
    </style>
@endsection
