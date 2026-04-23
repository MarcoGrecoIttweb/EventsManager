@extends('layouts.app')

@section('title', 'I Tuoi Eventi Attivi - Excursio')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <h2 class="mb-0">
            <i class="fas fa-calendar-check"></i> I Tuoi Eventi Attivi
        </h2>
        <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-home"></i> Home
        </a>
    </div>

    @if($events->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                <tr>
                    <th>Evento</th>
                    <th>Data</th>
                    <th>Città</th>
                    <th>Azioni</th>
                </tr>
                </thead>
                <tbody>
                @foreach($events as $event)
                    <tr>
                        <td><strong>{{ $event->title }}</strong></td>
                        <td>{{ $event->dataevento ? $event->dataevento->format('d/m/Y H:i') : '-' }}</td>
                        <td>{{ $event->citta }}</td>
                        <td>
                            <a href="{{ route('events.show', $event) }}" class="btn btn-sm btn-outline-primary" title="Visualizza">
                                <i class="fas fa-eye"></i>
                            </a>
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
            <h5>Non risulti iscritto a eventi futuri pubblicati</h5>
            <a href="{{ route('events.index') }}" class="btn btn-primary mt-2">
                <i class="fas fa-calendar-alt"></i> Vedi eventi in programma
            </a>
        </div>
    @endif
</div>
@endsection

