@extends('layouts.app')

@section('title', 'Mercatino delle chat — in arrivo')

@section('content')
    <div class="container py-4" style="max-width: 58rem;">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <h1 class="h3 mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-comments text-success"></i>
                Mercatino delle chat
            </h1>
            <a href="{{ route('home') }}" class="btn btn-success text-white">
                <i class="fas fa-home"></i> Home
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="alert alert-warning border mb-0">
                    <div class="fw-bold mb-1">Pagina in attesa di pubblicazione</div>
                    <div>
                        Stiamo preparando il “Mercatino delle chat”: a breve sarà disponibile per tutti gli utenti approvati.
                        <br>
                        Nel frattempo puoi continuare a usare le sezioni già attive del sito.
                    </div>
                    <div class="mt-3 small text-muted">
                        Nota: l’amministratore può attivare/disattivare questa sezione dalla Dashboard.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

