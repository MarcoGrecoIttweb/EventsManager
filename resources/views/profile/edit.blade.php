@extends('layouts.app')

@section('title', 'Modifica Profilo')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">Modifica Profilo</h2>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('profile.update', $user) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-4 text-center mb-4">
                                    @if($user->photo_url)
                                        <img src="{{ $user->photo_url }}"
                                             alt="{{ $user->name }}"
                                             class="rounded-circle mb-3"
                                             style="width: 150px; height: 150px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3"
                                             style="width: 150px; height: 150px;">
                                            <i class="fas fa-user fa-3x text-white"></i>
                                        </div>
                                    @endif

                                    <div class="mb-3">
                                        <label for="photo" class="form-label">Foto Profilo</label>
                                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Nome *</label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                       id="name" name="name"
                                                       value="{{ old('name', $user->nome) }}" required>
                                                @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="cognome" class="form-label">Cognome *</label>
                                                <input type="text" class="form-control @error('cognome') is-invalid @enderror"
                                                       id="cognome" name="cognome"
                                                       value="{{ old('cognome', $user->cognome) }}" required>
                                                @error('cognome')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="nickname" class="form-label">Nickname *</label>
                                        <input type="text" class="form-control @error('nickname') is-invalid @enderror"
                                               id="nickname" name="nickname"
                                               value="{{ old('nickname', $user->username) }}" required>
                                        @error('nickname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               id="email" name="email"
                                               value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="mail_visibile" name="mail_visibile" value="1"
                                            {{ old('mail_visibile', $user->mail_visibile) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="mail_visibile">
                                            Mostra email nel profilo pubblico
                                        </label>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="sesso" class="form-label">Sesso *</label>
                                                <select class="form-select @error('sesso') is-invalid @enderror" id="sesso" name="sesso" required>
                                                    <option value="m" {{ old('sesso', $user->sesso) === 'm' ? 'selected' : '' }}>Uomo</option>
                                                    <option value="f" {{ old('sesso', $user->sesso) === 'f' ? 'selected' : '' }}>Donna</option>
                                                </select>
                                                @error('sesso')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="datanascita" class="form-label">Data di Nascita</label>
                                                <input type="date" class="form-control @error('datanascita') is-invalid @enderror"
                                                       id="datanascita" name="datanascita"
                                                       value="{{ old('datanascita', $user->datanascita ? $user->datanascita->format('Y-m-d') : '') }}">
                                                @error('datanascita')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="residenza" class="form-label">Residenza</label>
                                                <input type="text" class="form-control @error('residenza') is-invalid @enderror"
                                                       id="residenza" name="residenza"
                                                       value="{{ old('residenza', $user->residenza) }}">
                                                @error('residenza')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="telefono" class="form-label">Telefono</label>
                                                <input type="text" class="form-control @error('telefono') is-invalid @enderror"
                                                       id="telefono" name="telefono"
                                                       value="{{ old('telefono', $user->telefono) }}">
                                                @error('telefono')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Descrizione</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror"
                                                  id="description" name="description"
                                                  rows="4">{{ old('description', $user->descr) }}</textarea>
                                        @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex">
                                <button type="submit" class="btn btn-primary">Salva Modifiche</button>
                                <a href="{{ route('profile.show', $user) }}" class="btn btn-secondary">Annulla</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
