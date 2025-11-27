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
                                    @if($user->photo)
                                        <img src="{{ asset('storage/photos/' . $user->photo) }}"
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
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nome Completo</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                               value="{{ old('name', $user->name) }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="nickname" class="form-label">Nickname</label>
                                        <input type="text" class="form-control" id="nickname" name="nickname"
                                               value="{{ old('nickname', $user->nickname) }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                               value="{{ old('email', $user->email) }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Descrizione</label>
                                        <textarea class="form-control" id="description" name="description"
                                                  rows="4">{{ old('description', $user->description) }}</textarea>
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
