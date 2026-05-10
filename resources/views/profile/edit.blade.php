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
                        @php
                            $isAdminViewer = auth()->check() && auth()->user()->isAdmin();
                        @endphp
                        <p class="text-muted small mb-4">
                            @if($isAdminViewer)
                                Come amministratore puoi modificare <strong>tutti i campi</strong> dell’utente, inclusa la <strong>foto profilo</strong>.
                            @else
                                Puoi modificare solo <strong>email</strong> e <strong>cellulare</strong>. Se vuoi modificare la foto, per questioni tecniche devi inviarla all'amministratore che provvederà ad inserirla.
                            @endif
                        </p>

                        <form action="{{ route('profile.update', $user) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            @if($isAdminViewer)
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username *</label>
                                    <input type="text" class="form-control @error('username') is-invalid @enderror"
                                           id="username" name="username"
                                           value="{{ old('username', $user->username) }}" required maxlength="20" autocomplete="username">
                                    @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="nome" class="form-label">Nome *</label>
                                            <input type="text" class="form-control @error('nome') is-invalid @enderror"
                                                   id="nome" name="nome"
                                                   value="{{ old('nome', $user->nome) }}" required maxlength="20" autocomplete="given-name">
                                            @error('nome')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="cognome" class="form-label">Cognome *</label>
                                            <input type="text" class="form-control @error('cognome') is-invalid @enderror"
                                                   id="cognome" name="cognome"
                                                   value="{{ old('cognome', $user->cognome) }}" required maxlength="20" autocomplete="family-name">
                                            @error('cognome')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="sesso" class="form-label">Sesso *</label>
                                            <select id="sesso" name="sesso" class="form-select @error('sesso') is-invalid @enderror" required>
                                                <option value="m" {{ old('sesso', $user->sesso) === 'm' ? 'selected' : '' }}>Uomo</option>
                                                <option value="f" {{ old('sesso', $user->sesso) === 'f' ? 'selected' : '' }}>Donna</option>
                                            </select>
                                            @error('sesso')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="datanascita" class="form-label">Data di nascita</label>
                                            <input type="date" class="form-control @error('datanascita') is-invalid @enderror"
                                                   id="datanascita" name="datanascita"
                                                   value="{{ old('datanascita', optional($user->datanascita)->format('Y-m-d')) }}">
                                            @error('datanascita')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="residenza" class="form-label">Residenza</label>
                                    <input type="text" class="form-control @error('residenza') is-invalid @enderror"
                                           id="residenza" name="residenza"
                                           value="{{ old('residenza', $user->residenza) }}" maxlength="30" autocomplete="address-level2">
                                    @error('residenza')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

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
                                <label for="telefono" class="form-label">Cellulare</label>
                                <input type="text" class="form-control @error('telefono') is-invalid @enderror"
                                       id="telefono" name="telefono"
                                       value="{{ old('telefono', $user->telefono) }}" autocomplete="tel">
                                @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if($isAdminViewer)
                                <div class="mb-4">
                                    <label for="description" class="form-label">Descrizione</label>
                                    <textarea id="description" name="description" rows="5"
                                              class="form-control @error('description') is-invalid @enderror"
                                              maxlength="65535"
                                              placeholder="Descrizione profilo...">{{ old('description', $user->descr) }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <span class="form-label d-block">Foto profilo</span>
                                    @if($user->photo_url)
                                        <div class="mb-2">
                                            <img src="{{ $user->photo_url }}"
                                                 alt="Foto attuale"
                                                 class="rounded border border-secondary"
                                                 style="max-height: 140px; width: auto; object-fit: contain;">
                                        </div>
                                    @else
                                        <p class="small text-muted mb-2">Nessuna foto impostata.</p>
                                    @endif
                                    <input type="file"
                                           name="foto_profilo"
                                           id="foto_profilo"
                                           class="form-control @error('foto_profilo') is-invalid @enderror"
                                           accept="image/jpeg,image/png,image/webp,image/gif,.jpg,.jpeg,.png,.webp,.gif">
                                    <div class="form-text">JPG, PNG, WebP o GIF, massimo 4&nbsp;MB. Lascia vuoto per mantenere la foto attuale; con un nuovo file la precedente viene sostituita.</div>
                                    @error('foto_profilo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <div class="d-grid gap-2 d-md-flex">
                                <button type="submit" class="btn btn-primary">Salva Modifiche</button>
                                <a href="{{ route('profile.show', $user) }}" class="btn btn-secondary">Annulla</a>
                            </div>
                        </form>

                        <hr class="my-4">

                        @php
                            $openPasswordFromQuery = request()->boolean('openPassword');
                            $isAdminResettingOther = $isAdminViewer && auth()->id() !== $user->getKey();
                            $selfPwdOpen = $openPasswordFromQuery || $errors->has('current_password') || $errors->has('password');
                        @endphp

                        <button type="button"
                                class="btn btn-link btn-sm text-muted text-decoration-none p-0 d-inline-flex align-items-center gap-1"
                                data-bs-toggle="collapse"
                                data-bs-target="#selfPasswordCollapse"
                                aria-expanded="{{ $selfPwdOpen ? 'true' : 'false' }}"
                                aria-controls="selfPasswordCollapse">
                            <i class="fas fa-key" style="font-size:0.85em;"></i>
                            <span>{{ $isAdminResettingOther ? 'Reimposta password utente' : 'Cambia password' }}</span>
                            <i class="fas fa-chevron-down small opacity-75" aria-hidden="true"></i>
                        </button>

                        <div class="collapse {{ $selfPwdOpen ? 'show' : '' }}" id="selfPasswordCollapse">
                            <div class="border rounded bg-light px-3 py-3 mt-2 small">
                                <form method="POST" action="{{ route('profile.password.self', $user) }}">
                                    @csrf
                                    <div class="row g-2">
                                        @if(!$isAdminResettingOther)
                                        <div class="col-12">
                                            <label for="current_password" class="form-label mb-0">Password attuale</label>
                                            <input type="password" id="current_password" name="current_password"
                                                   class="form-control form-control-sm @error('current_password') is-invalid @enderror"
                                                   required autocomplete="current-password">
                                            @error('current_password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        @endif
                                        <div class="col-12">
                                            <label for="new_password" class="form-label mb-0">Nuova password</label>
                                            <input type="password" id="new_password" name="password"
                                                   class="form-control form-control-sm @error('password') is-invalid @enderror"
                                                   required minlength="4" autocomplete="new-password">
                                            @error('password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12">
                                            <label for="new_password_confirmation" class="form-label mb-0">Conferma password</label>
                                            <input type="password" id="new_password_confirmation" name="password_confirmation"
                                                   class="form-control form-control-sm"
                                                   required minlength="4" autocomplete="new-password">
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-outline-secondary btn-sm mt-2">
                                        <i class="fas fa-save"></i> {{ $isAdminResettingOther ? 'Salva password utente' : 'Salva nuova password' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Modifica profilo: bordi verdi + label in grassetto */
        .form-label {
            font-weight: 700;
        }
        .form-control,
        .form-select {
            border: 2px solid #198754; /* Bootstrap success */
        }
        .form-control:focus,
        .form-select:focus {
            border-color: #146c43;
            box-shadow: 0 0 0 .2rem rgba(25, 135, 84, 0.25);
        }
        .card {
            border: 2px solid #198754;
        }
        /* Box "Cambia password" */
        #selfPasswordCollapse .border {
            border-color: #198754 !important;
        }
        /* Password: campi più compatti */
        #selfPasswordCollapse .form-control.form-control-sm {
            height: 31px !important;
            min-height: 31px !important;
            padding-top: 0.1rem !important;
            padding-bottom: 0.1rem !important;
            line-height: 1.1;
        }
    </style>
@endsection
