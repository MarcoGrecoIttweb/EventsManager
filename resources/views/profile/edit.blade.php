@extends('layouts.app')

@section('title', 'Modifica Profilo')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">Modifica Profilo</h2>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-4">
                            Puoi modificare solo <strong>email</strong> e <strong>telefono</strong>.
                        </p>

                        <form action="{{ route('profile.update', $user) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email"
                                       value="{{ old('email', $user->email) }}" required autocomplete="email">
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="telefono" class="form-label">Telefono</label>
                                <input type="text" class="form-control @error('telefono') is-invalid @enderror"
                                       id="telefono" name="telefono"
                                       value="{{ old('telefono', $user->telefono) }}" autocomplete="tel">
                                @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid gap-2 d-md-flex">
                                <button type="submit" class="btn btn-primary">Salva Modifiche</button>
                                <a href="{{ route('profile.show', $user) }}" class="btn btn-secondary">Annulla</a>
                            </div>
                        </form>

                        <hr class="my-4">

                        <button type="button"
                                class="btn btn-link btn-sm text-muted text-decoration-none p-0 d-inline-flex align-items-center gap-1"
                                data-bs-toggle="collapse"
                                data-bs-target="#selfPasswordCollapse"
                                aria-expanded="{{ $errors->has('current_password') || $errors->has('password') ? 'true' : 'false' }}"
                                aria-controls="selfPasswordCollapse">
                            <i class="fas fa-key" style="font-size:0.85em;"></i>
                            <span>Cambia password</span>
                            <i class="fas fa-chevron-down small opacity-75" aria-hidden="true"></i>
                        </button>

                        <div class="collapse {{ $errors->has('current_password') || $errors->has('password') ? 'show' : '' }}" id="selfPasswordCollapse">
                            <div class="border rounded bg-light px-3 py-3 mt-2 small">
                                <form method="POST" action="{{ route('profile.password.self', $user) }}">
                                    @csrf
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <label for="current_password" class="form-label mb-0">Password attuale</label>
                                            <input type="password" id="current_password" name="current_password"
                                                   class="form-control form-control-sm @error('current_password') is-invalid @enderror"
                                                   required autocomplete="current-password">
                                            @error('current_password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label for="new_password" class="form-label mb-0">Nuova password</label>
                                            <input type="password" id="new_password" name="password"
                                                   class="form-control form-control-sm @error('password') is-invalid @enderror"
                                                   required minlength="8" autocomplete="new-password">
                                            @error('password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label for="new_password_confirmation" class="form-label mb-0">Conferma password</label>
                                            <input type="password" id="new_password_confirmation" name="password_confirmation"
                                                   class="form-control form-control-sm"
                                                   required minlength="8" autocomplete="new-password">
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-outline-secondary btn-sm mt-2">
                                        <i class="fas fa-save"></i> Salva nuova password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
