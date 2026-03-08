@extends('layouts.app')

@section('no_sidebar', '1')
@section('title', 'Password dimenticata - Excursio')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-key"></i> Recupero password</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <p class="text-muted small">Inserisci il tuo username o email. Ti invieremo un link per reimpostare la password.</p>

                <form method="POST" action="{{ route('password.send') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="identifier" class="form-label">Username o Email</label>
                        <input type="text" name="identifier" id="identifier"
                               class="form-control @error('identifier') is-invalid @enderror"
                               value="{{ old('identifier') }}" required autofocus>
                        @error('identifier')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Invia link di recupero
                        </button>
                    </div>
                </form>

                <hr>
                <div class="text-center">
                    <a href="{{ route('login') }}">Torna al login</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
