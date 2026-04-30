@extends('layouts.app')

@section('title', 'Richiesta abilitazione organizzatore')

@section('content')
    <div class="container py-3" style="max-width: 52rem;">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <h1 class="h3 mb-0">
                <i class="fas fa-calendar-plus text-success"></i> Vuoi organizzare eventi?
            </h1>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Torna alla home
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <p class="mb-2 fw-semibold">
                    Vuoi organizzare un evento?
                </p>
                <p class="mb-2">
                    Siamo felici di accogliere nuovi organizzatori e nuove idee!
                </p>
                <p class="mb-3 text-muted">
                    Per richiedere l’abilitazione, invia una mail all’amministratore. Ti ricordiamo che per candidarti devi essere
                    un utente registrato, aver partecipato ad almeno due eventi della nostra community e proporre esclusivamente
                    attività lecite. Riceverai una notifica non appena il tuo profilo sarà abilitato.
                </p>

                @if(($adminNotifyEmail ?? '') !== '')
                    <div class="mb-3">
                        <div class="small text-muted">Email amministratore</div>
                        <div class="fw-semibold">
                            <a href="mailto:{{ $adminNotifyEmail }}">{{ $adminNotifyEmail }}</a>
                        </div>
                    </div>

                    <a class="btn btn-success"
                       href="mailto:{{ $adminNotifyEmail }}?subject={{ rawurlencode('Excursio - Richiesta abilitazione organizzatore') }}">
                        <i class="fas fa-envelope"></i> Scrivi email
                    </a>
                @else
                    <div class="alert alert-warning mb-0">
                        Non è stato possibile determinare l’email dell’amministratore. Contatta un amministratore tramite i canali del sito.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

