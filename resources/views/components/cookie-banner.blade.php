@php
    /** @var bool $show */
    $show = $show ?? true;
@endphp

@if($show)
    <div id="cookie-banner" class="cookie-banner shadow-sm">
        <div class="cookie-banner__inner">
            <div class="cookie-banner__text">
                <div class="fw-semibold">Cookie</div>
                <div class="small text-muted">
                    Usiamo solo cookie tecnici per login, sessione e sicurezza. Eventuali servizi esterni (terze parti)
                    vengono caricati solo dopo il tuo consenso.
                </div>
            </div>
            <div class="cookie-banner__actions">
                <form method="POST" action="{{ route('cookie.consent.store') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="redirect" value="{{ url()->current() }}">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Rifiuta</button>
                </form>

                <form method="POST" action="{{ route('cookie.consent.store') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="action" value="accept">
                    <input type="hidden" name="redirect" value="{{ url()->current() }}">
                    <button type="submit" class="btn btn-success btn-sm">Accetta</button>
                </form>
            </div>
        </div>
    </div>

    <style>
        .cookie-banner {
            position: fixed;
            left: 1rem;
            right: 1rem;
            bottom: 1rem;
            z-index: 2000;
            background: #fff;
            border: 1px solid rgba(0,0,0,.12);
            border-radius: 12px;
            padding: .9rem 1rem;
        }
        .cookie-banner__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .cookie-banner__text {
            min-width: 0;
        }
        .cookie-banner__actions {
            display: flex;
            gap: .5rem;
            flex-shrink: 0;
        }
        @media (max-width: 575.98px) {
            .cookie-banner__inner {
                flex-direction: column;
                align-items: stretch;
            }
            .cookie-banner__actions {
                justify-content: flex-end;
            }
        }
    </style>
@endif

