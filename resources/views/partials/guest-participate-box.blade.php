{{-- Box "Non sei registrato e vuoi partecipare?": riutilizzato sia in sidebar sia
     accanto alla galleria (guest, smartphone). $idSuffix evita id duplicati quando
     il partial viene incluso piu' volte nella stessa pagina. --}}
@php($collapseId = 'guestParticipateBox' . ($idSuffix ?? ''))
<div class="card card-sidebar mb-3" style="border: 1px solid #198754;">
    <div class="card-header py-2" role="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}" style="cursor:pointer;">
        <small class="fw-bold">
            <i class="fas fa-envelope text-danger me-1"></i> Non sei registrato e vuoi partecipare?
        </small>
    </div>
    <div class="collapse" id="{{ $collapseId }}">
        <div class="card-body p-2">
            <div class="small mb-2">
                Se sei interessato a partecipare a un evento in programma per cominciare a conoscerci e per provare, scrivici una email e ti daremo tutte le informazioni.
            </div>
            <a href="mailto:excursio@libero.it?subject=Richiesta%20partecipazione%20evento&body=Ciao,%20vorrei%20partecipare%20all%27evento%3A%20%5BTITOLO%20EVENTO%5D%0AMio%20nome%3A%20%5BNOME%5D%0AMio%20numero%20di%20telefono%3A%20%5BTELEFONO%5D%0A%0AGrazie."
               class="btn btn-danger btn-sm w-100">
                <i class="fas fa-paper-plane me-1"></i> Scrivici per partecipare
            </a>
        </div>
    </div>
</div>
