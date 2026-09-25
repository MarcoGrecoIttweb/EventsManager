{{-- Box "Unisciti a noi": riutilizzato sia in sidebar sia accanto alla galleria
     (guest, smartphone). $idSuffix evita id duplicati quando il partial viene
     incluso piu' volte nella stessa pagina. Il testo si apre in una finestra
     (modale) invece che scorrendo in basso in linea, per non spostare il
     resto del contenuto della pagina. --}}
@php($modalId = 'guestParticipateModal' . ($idSuffix ?? ''))
<div class="card card-sidebar mb-3" style="border: 1px solid #198754;">
    <div class="card-header py-2" role="button" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}" style="cursor:pointer;">
        <small class="fw-bold">
            <i class="fas fa-envelope text-danger me-1"></i> Unisciti a noi
        </small>
    </div>
</div>

@push('modals')
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    <i class="fas fa-envelope text-danger me-1"></i> Unisciti a noi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">
                    Se sei interessato a partecipare a un evento in programma per cominciare a conoscerci e per provare, scrivici una email e ti daremo tutte le informazioni.
                </p>
            </div>
            <div class="modal-footer">
                <a href="mailto:excursio@libero.it?subject=Richiesta%20partecipazione%20evento&body=Ciao,%20vorrei%20partecipare%20all%27evento%3A%20%5BTITOLO%20EVENTO%5D%0AMio%20nome%3A%20%5BNOME%5D%0AMio%20numero%20di%20telefono%3A%20%5BTELEFONO%5D%0A%0AGrazie."
                   class="btn btn-danger btn-sm">
                    <i class="fas fa-paper-plane me-1"></i> Scrivici per partecipare
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>
@endpush
