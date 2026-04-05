@extends('layouts.app')

@section('title', 'Statistiche Newsletter - Admin')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistiche Newsletter</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            Gli invii dalla pagina Newsletter usano il flag <strong>News</strong> (<code>invia</code>) sul profilo utente.
                            Per inviare solo agli iscritti, scegli il destinatario «Solo utenti con News attiva».
                        </p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Utenti totali (non admin)
                                <span class="badge bg-primary rounded-pill">{{ number_format($totalUsers) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Approvati
                                <span class="badge bg-success rounded-pill">{{ number_format($approvedUsers) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><strong>News attiva</strong> (newsletter, con email)</span>
                                <span class="badge bg-info rounded-pill">{{ number_format($newsSubscribers) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Partecipanti ad almeno un evento
                                <span class="badge bg-warning text-dark rounded-pill">{{ number_format($participants) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                In attesa di approvazione
                                <span class="badge bg-secondary rounded-pill">{{ number_format($pendingUsers) }}</span>
                            </li>
                        </ul>
                        <a href="{{ route('admin.newsletter.create') }}" class="btn btn-primary mt-3">
                            <i class="fas fa-envelope me-1"></i> Vai all’invio newsletter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
