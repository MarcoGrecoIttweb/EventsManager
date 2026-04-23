@extends('layouts.app')

@section('title', 'Link foto - ' . $event->title)

@section('content')
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h1 class="h3 mb-1"><i class="fas fa-camera"></i> Link foto</h1>
                <div class="text-muted small">
                    Evento: <a href="{{ route('events.show', $event) }}" class="text-decoration-none">{{ $event->title }}</a>
                </div>
            </div>
            <a href="{{ route('events.show', $event) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Torna all’evento
            </a>
        </div>

        <div class="alert alert-light border shadow-sm">
            Qui trovi i link diretti alle immagini rilevate nella pagina dell’evento (descrizione/cover/immagini dedicate).
            Puoi copiarli e incollarli dove vuoi.
        </div>

        @if(isset($rangeLinks) && $rangeLinks->count() > 0)
            <div class="card border-dark mb-4">
                <div class="card-header bg-dark text-white py-2">
                    <strong><i class="fas fa-list"></i> Foto 2011 → 2022</strong>
                    <span class="badge bg-light text-dark ms-2">{{ $rangeLinks->count() }}</span>
                </div>
                <div class="card-body">
                    @include('events.partials.photo-links-list', ['links' => $rangeLinks])
                </div>
            </div>
        @endif

        <div class="card border-dark">
            <div class="card-header bg-secondary text-white py-2">
                <strong><i class="fas fa-images"></i> Tutte le immagini trovate</strong>
                <span class="badge bg-light text-dark ms-2">{{ $links->count() }}</span>
            </div>
            <div class="card-body">
                @if($links->count() === 0)
                    <div class="text-muted">Nessuna immagine trovata nella descrizione/cover.</div>
                @else
                    @include('events.partials.photo-links-list', ['links' => $links])
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-copy-url]').forEach(function (btn) {
                btn.addEventListener('click', async function () {
                    var inputId = btn.getAttribute('data-copy-url');
                    var input = document.getElementById(inputId);
                    if (!input) return;
                    try {
                        await navigator.clipboard.writeText(input.value);
                        btn.innerText = 'Copiato';
                        setTimeout(function () { btn.innerText = 'Copia'; }, 900);
                    } catch (e) {
                        input.focus();
                        input.select();
                        document.execCommand('copy');
                        btn.innerText = 'Copiato';
                        setTimeout(function () { btn.innerText = 'Copia'; }, 900);
                    }
                });
            });
        });
    </script>
@endsection

