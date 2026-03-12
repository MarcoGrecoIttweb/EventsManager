@extends('layouts.app')
@section('title', 'Gruppo {{ $group->nome }} - Admin')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users"></i> {{ $group->nome }}</h2>
        <a href="{{ route('admin.groups.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Torna ai gruppi</a>
    </div>
    <div class="row">
        <div class="col-md-7">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Membri ({{ $group->members->count() }})</h5></div>
                <div class="card-body p-0">
                    @forelse($group->members as $member)
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                        <a href="{{ route('profile.show', $member) }}" class="text-decoration-none">
                            {{ $member->nome }} {{ $member->cognome }} <span class="text-muted">@{{ $member->username }}</span>
                        </a>
                        <form action="{{ route('admin.groups.remove-member', [$group, $member]) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i></button>
                        </form>
                    </div>
                    @empty
                    <p class="text-muted text-center p-3">Nessun membro</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Aggiungi membro</h5></div>
                <div class="card-body">
                    <form action="{{ route('admin.groups.add-member', $group) }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <select name="user_id" class="form-select" required>
                                <option value="">-- Seleziona utente --</option>
                                @foreach($nonMembers as $user)
                                <option value="{{ $user->userID }}">{{ $user->username }} ({{ $user->nome }} {{ $user->cognome }})</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100"><i class="fas fa-plus"></i> Aggiungi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
