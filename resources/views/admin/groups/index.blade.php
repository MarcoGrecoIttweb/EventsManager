@extends('layouts.app')
@section('title', 'Gestione Gruppi - Admin')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users-cog"></i> Gestione Gruppi</h2>
        <a href="{{ route('admin.groups.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Nuovo Gruppo</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr><th>Nome</th><th>Membri</th><th>Azioni</th></tr>
            </thead>
            <tbody>
                @forelse($groups as $group)
                <tr>
                    <td><strong>{{ $group->nome }}</strong></td>
                    <td><span class="badge bg-primary">{{ $group->members_count }}</span></td>
                    <td>
                        <a href="{{ route('admin.groups.show', $group) }}" class="btn btn-sm btn-outline-info" title="Gestisci membri"><i class="fas fa-users"></i></a>
                        <a href="{{ route('admin.groups.edit', $group) }}" class="btn btn-sm btn-outline-warning" title="Rinomina"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.groups.destroy', $group) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Eliminare il gruppo {{ $group->nome }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Elimina"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-muted">Nessun gruppo presente</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
