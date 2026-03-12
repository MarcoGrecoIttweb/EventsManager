@extends('layouts.app')
@section('title', 'Modifica Gruppo - Admin')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Modifica Gruppo</h5></div>
                <div class="card-body">
                    <form action="{{ route('admin.groups.update', $group) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome gruppo</label>
                            <input type="text" name="nome" id="nome" class="form-control @error('nome') is-invalid @enderror"
                                   value="{{ old('nome', $group->nome) }}" required maxlength="250">
                            @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Salva</button>
                            <a href="{{ route('admin.groups.index') }}" class="btn btn-outline-secondary">Annulla</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
