@extends('layouts.app')

@section('title', 'La Tua Agenda Eventi - Excursio')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-calendar-plus"></i> La Tua Agenda Eventi</h2>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('home') }}" class="btn btn-outline-primary" data-hint="Torna alla homepage">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="{{ route('manage.events.create') }}" class="btn btn-success" data-hint="Crea un nuovo evento">
                <i class="fas fa-plus"></i> Crea evento
            </a>
        </div>
    </div>

    @if($events->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Evento</th>
                        <th>Data</th>
                        <th>Città</th>
                        <th>Partecipanti</th>
                        <th>Stato</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $event)
                    <tr>
                        <td>
                            <strong>{{ $event->title }}</strong>
                            @if($event->costo)
                                <br><small class="text-muted">€ {{ number_format($event->costo, 2, ',', '.') }}</small>
                            @endif
                        </td>
                        <td>
                            {{ $event->dataevento ? $event->dataevento->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td>{{ $event->citta }}</td>
                        <td>
                            {{ $event->participants_count }}
                            @if($event->numeromax)
                                / {{ $event->numeromax }}
                            @endif
                        </td>
                        <td>
                            @if($event->is_past_event)
                                <span class="badge text-primary border border-primary">Scaduto</span>
                            @elseif(!$event->pubblicato)
                                <span class="badge bg-danger">Disattivato</span>
                            @else
                                <span class="badge bg-success">Pubblicato</span>
                            @endif
                            @if($event->isFull())
                                <span class="badge bg-danger">Completo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('events.show', $event) }}" class="btn btn-sm btn-outline-primary" title="Visualizza" data-hint="Apri la pagina evento">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('manage.events.edit', $event) }}" class="btn btn-sm btn-outline-warning" title="Modifica" data-hint="Apri la modifica di questo evento">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if(!$event->is_past_event)
                                <form action="{{ route('manage.events.toggle-status', $event) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('{{ $event->pubblicato ? 'Vuoi disattivare questo evento?' : 'Vuoi attivare questo evento?' }}')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm manage-toggle-btn {{ $event->pubblicato ? 'btn-danger' : 'btn-success' }}"
                                            title="{{ $event->pubblicato ? 'Disattiva' : 'Attiva' }}"
                                            data-hint="{{ $event->pubblicato ? 'Disattiva l’evento (lo nasconde dalla homepage)' : 'Attiva l’evento (lo rende visibile in homepage)' }}">
                                        <i class="fas {{ $event->pubblicato ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                        {{ $event->pubblicato ? 'Disattiva' : 'Attiva' }}
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('manage.events.destroy', $event) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Eliminare questo evento?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Elimina" data-hint="Elimina definitivamente questo evento">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <style>
            /* Pulsanti Attiva/Disattiva: stessa larghezza */
            .manage-toggle-btn {
                min-width: 6.8rem;
                text-align: center;
            }
        </style>

        <div class="d-flex justify-content-center mt-3">
            {{ $events->links() }}
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
            <h5>Nessun evento creato</h5>
            <a href="{{ route('manage.events.create') }}" class="btn btn-primary mt-2">
                <i class="fas fa-plus"></i> Crea il primo evento
            </a>
        </div>
    @endif
</div>
@endsection
