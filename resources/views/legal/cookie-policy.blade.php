@extends('layouts.app')

@section('title', 'Cookie Policy')
@section('suppress_cookie_modal', true)

@section('content')
    <div class="container py-4" style="max-width: 58rem;">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <h1 class="h3 mb-0"><i class="fas fa-cookie-bite text-warning me-2"></i>Cookie Policy</h1>
            <a href="{{ route('home') }}" class="btn btn-success text-white">
                <i class="fas fa-home"></i> Home
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Ultimo aggiornamento: {{ now()->format('d/m/Y') }}
                </p>

                <h2 class="h5">Cosa sono i cookie</h2>
                <p>
                    I cookie sono piccoli file di testo che i siti possono salvare sul dispositivo dell’utente per far funzionare alcune funzioni
                    o memorizzare preferenze.
                </p>

                <h2 class="h5 mt-4">Cookie usati da questo Sito</h2>
                <ul>
                    <li>
                        <strong>Necessari (sempre attivi)</strong>: indispensabili per login, sessione e sicurezza.
                        Senza questi cookie il Sito non può funzionare correttamente.
                    </li>
                    <li>
                        <strong>Contenuti esterni (Mappe)</strong>: se abilitati, permettono di caricare contenuti esterni come Google Maps
                        nelle pagine evento. Questi servizi possono impostare cookie e raccogliere dati secondo le loro policy.
                    </li>
                </ul>

                <h2 class="h5 mt-4">Gestione preferenze</h2>
                <p>
                    Puoi scegliere se abilitare i contenuti esterni dal popup “Preferenze cookie” mostrato al primo accesso.
                    Se rifiuti i cookie non necessari, i contenuti esterni (es. mappe) verranno disattivati.
                </p>

                <h2 class="h5 mt-4">Durata del consenso</h2>
                <p>
                    Il consenso viene memorizzato per un periodo limitato (attualmente 180 giorni), poi potrà essere richiesto nuovamente.
                </p>

                <h2 class="h5 mt-4">Privacy</h2>
                <p>
                    Per informazioni sul trattamento dei dati personali consulta la <a href="{{ url('/privacy-policy') }}">Privacy Policy</a>.
                </p>
            </div>
        </div>
    </div>
@endsection

