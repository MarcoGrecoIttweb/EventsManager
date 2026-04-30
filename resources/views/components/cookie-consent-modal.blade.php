@php
    /** @var bool $show */
    $show = $show ?? true;
    /** @var bool $autoShow */
    $autoShow = $autoShow ?? false;
    /** @var array $consent */
    $consent = $consent ?? [];

    $status = (string) ($consent['status'] ?? '');
    $cats = $consent['categories'] ?? [];
    if (!is_array($cats)) {
        $cats = [];
    }

    $hasExternalMedia = in_array('external_media', $cats, true);

    $redirect = url()->current();
@endphp

@if($show)
    <div class="modal fade" id="cookieConsentModal" tabindex="-1" aria-labelledby="cookieConsentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cookieConsentModalLabel">
                        <i class="fas fa-cookie-bite me-2"></i>Preferenze cookie
                    </h5>
                </div>
                <div class="modal-body">
                    <p class="mb-2">
                        Usiamo <strong>cookie tecnici</strong> necessari per login, sessione e sicurezza.
                        Alcune funzioni (es. mappe) possono contattare servizi esterni: le attiviamo solo con il tuo consenso.
                    </p>

                    <div class="small text-muted mb-3">
                        Leggi:
                        <a href="{{ url('/privacy-policy') }}" class="text-decoration-none">Privacy Policy</a>
                        ·
                        <a href="{{ url('/cookie-policy') }}" class="text-decoration-none">Cookie Policy</a>
                    </div>

                    <form method="POST" action="{{ route('cookie.consent.store') }}" id="cookieConsentSaveForm">
                        @csrf
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="redirect" value="{{ $redirect }}">

                        <div class="list-group">
                            <div class="list-group-item d-flex align-items-start justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold">Necessari</div>
                                    <div class="small text-muted">Sempre attivi: sessione, autenticazione, sicurezza.</div>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" checked disabled>
                                </div>
                            </div>

                            <div class="list-group-item d-flex align-items-start justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold">Contenuti esterni (Mappe)</div>
                                    <div class="small text-muted">Abilita elementi che caricano servizi esterni (es. Google Maps).</div>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="categories[]" value="external_media"
                                           id="cookieCatExternalMedia" {{ $hasExternalMedia ? 'checked' : '' }}>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
                <div class="modal-footer d-flex flex-wrap gap-2 justify-content-between">
                    <div class="d-flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('cookie.consent.store') }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="redirect" value="{{ $redirect }}">
                            <button type="submit" class="btn btn-outline-secondary">
                                Rifiuta non necessari
                            </button>
                        </form>

                        <form method="POST" action="{{ route('cookie.consent.store') }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="action" value="accept">
                            <input type="hidden" name="redirect" value="{{ $redirect }}">
                            <button type="submit" class="btn btn-success">
                                Accetta tutto
                            </button>
                        </form>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" form="cookieConsentSaveForm" class="btn btn-primary">
                            Salva preferenze
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($autoShow)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var el = document.getElementById('cookieConsentModal');
                if (!el || typeof bootstrap === 'undefined') return;
                var modal = bootstrap.Modal.getOrCreateInstance(el, {
                    backdrop: 'static',
                    keyboard: false
                });
                modal.show();
            });
        </script>
    @endif
@endif

