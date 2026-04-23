@extends('layouts.app')

@section('title', 'Privacy Policy')
@section('suppress_cookie_modal', true)

@section('content')
    <div class="container py-4" style="max-width: 58rem;">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <h1 class="h3 mb-0"><i class="fas fa-user-shield text-primary me-2"></i>Privacy Policy</h1>
            <a href="{{ route('home') }}" class="btn btn-success text-white">
                <i class="fas fa-home"></i> Home
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Ultimo aggiornamento: {{ now()->format('d/m/Y') }}
                </p>

                <h2 class="h5">Titolare del trattamento</h2>
                <p>
                    Excursio (di seguito “Sito”). Per richieste privacy puoi contattarci tramite i recapiti indicati nel Sito.
                </p>

                <h2 class="h5 mt-4">Dati trattati</h2>
                <ul>
                    <li><strong>Dati account</strong>: username, e-mail, password (in forma protetta), informazioni profilo inserite dall’utente.</li>
                    <li><strong>Dati di utilizzo</strong>: dati tecnici necessari al funzionamento (es. sessione, log di sicurezza).</li>
                    <li><strong>Contenuti</strong>: messaggi/commenti pubblicati nelle aree del Sito.</li>
                </ul>

                <h2 class="h5 mt-4">Finalità e base giuridica</h2>
                <ul>
                    <li><strong>Erogazione del servizio</strong> (account, eventi, funzionalità riservate): esecuzione del servizio richiesto.</li>
                    <li><strong>Sicurezza</strong> (prevenzione abusi, protezione accessi): legittimo interesse.</li>
                    <li><strong>Funzioni opzionali</strong> (es. mappe esterne): consenso, quando necessario.</li>
                </ul>

                <h2 class="h5 mt-4">Cookie e servizi esterni</h2>
                <p>
                    Il Sito usa cookie tecnici necessari. Alcuni contenuti esterni (es. mappe) possono essere caricati solo dopo consenso.
                    Per dettagli vedi la <a href="{{ url('/cookie-policy') }}">Cookie Policy</a>.
                </p>

                <h2 class="h5 mt-4">Conservazione</h2>
                <p>
                    Conserviamo i dati per il tempo necessario a fornire il servizio e adempiere obblighi di legge, oppure fino a richiesta di cancellazione
                    compatibilmente con i vincoli normativi e di sicurezza.
                </p>

                <h2 class="h5 mt-4">Diritti dell’interessato</h2>
                <p>
                    Hai diritto di accesso, rettifica, cancellazione, limitazione e opposizione nei limiti previsti dalla normativa applicabile.
                </p>
            </div>
        </div>
    </div>
@endsection

