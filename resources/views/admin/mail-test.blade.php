@extends('layouts.app')

@section('title', 'Test invio email - Admin')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-paper-plane me-2"></i>Test invio email
                        </h4>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if($errors->has('mail'))
                            <div class="alert alert-danger">{{ $errors->first('mail') }}</div>
                        @endif

                        <div class="alert alert-info small mb-4">
                            <strong>Configurazione attuale (solo lettura):</strong><br>
                            <code>MAIL_MAILER</code> → <strong>{{ $mailDriver }}</strong>
                            @if($mailDriver === 'smtp' && !empty($mailHost))
                                <br><code>MAIL_HOST</code> → <strong>{{ $mailHost }}</strong>
                            @endif
                            <hr class="my-2">
                            In locale, per non inviare posta vera imposta in <code>.env</code>: <code>MAIL_MAILER=log</code>
                            (i messaggi finiscono in <code>storage/logs/laravel.log</code>).
                        </div>

                        <form method="POST" action="{{ route('admin.mail-test.send') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="to" class="form-label">Destinatario</label>
                                <input type="email" class="form-control @error('to') is-invalid @enderror" id="to" name="to"
                                       value="{{ old('to', $defaultTo) }}" required maxlength="255"
                                       placeholder="email@esempio.it">
                                @error('to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Oggetto <span class="text-muted">(opzionale)</span></label>
                                <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject"
                                       value="{{ old('subject') }}" maxlength="255"
                                       placeholder="Lascia vuoto per usare l'oggetto predefinito di prova">
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="body" class="form-label">Testo del messaggio <span class="text-muted">(opzionale)</span></label>
                                <textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body" rows="6"
                                          maxlength="5000" placeholder="Lascia vuoto per usare un testo di prova automatico">{{ old('body') }}</textarea>
                                @error('body')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Invia email di prova
                            </button>
                            <a href="{{ route('admin.newsletter.create') }}" class="btn btn-outline-secondary ms-2">Vai alla newsletter</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
