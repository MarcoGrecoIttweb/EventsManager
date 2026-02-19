@extends('layouts.app')

@section('title', 'Registrati - EventSite')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Registrati</div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <p class="mb-0">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register.post') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cognome" class="form-label">Cognome *</label>
                                    <input type="text" class="form-control @error('cognome') is-invalid @enderror"
                                           id="cognome" name="cognome" value="{{ old('cognome') }}" required>
                                    @error('cognome')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="nickname" class="form-label">Nickname *</label>
                            <input type="text" class="form-control @error('nickname') is-invalid @enderror"
                                   id="nickname" name="nickname" value="{{ old('nickname') }}" required>
                            @error('nickname')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sesso" class="form-label">Sesso *</label>
                                    <select class="form-select @error('sesso') is-invalid @enderror" id="sesso" name="sesso" required>
                                        <option value="">Seleziona...</option>
                                        <option value="m" {{ old('sesso') === 'm' ? 'selected' : '' }}>Uomo</option>
                                        <option value="f" {{ old('sesso') === 'f' ? 'selected' : '' }}>Donna</option>
                                    </select>
                                    @error('sesso')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="residenza" class="form-label">Residenza</label>
                                    <input type="text" class="form-control @error('residenza') is-invalid @enderror"
                                           id="residenza" name="residenza" value="{{ old('residenza') }}">
                                    @error('residenza')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" required>
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Conferma Password *</label>
                            <input type="password" class="form-control"
                                   id="password_confirmation" name="password_confirmation" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Registrati</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}">Hai già un account? Accedi</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
