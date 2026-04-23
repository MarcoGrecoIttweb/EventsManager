@extends('layouts.app')

@section('title', 'La Tua Agenda Eventi - Excursio')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-calendar-plus"></i> La Tua Agenda Eventi</h2>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('home') }}" class="btn btn-outline-primary">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="{{ route('manage.events.create') }}" class="btn btn-success">
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
                                <span class="badge bg-secondary">Disattivato</span>
                            @else
                                <span class="badge bg-success">Pubblicato</span>
                            @endif
                            @if($event->isFull())
                                <span class="badge bg-danger">Completo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('events.show', $event) }}" class="btn btn-sm btn-outline-primary" title="Visualizza">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('manage.events.edit', $event) }}" class="btn btn-sm btn-outline-warning" title="Modifica">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('manage.events.destroy', $event) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Eliminare questo evento?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Elimina">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

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
