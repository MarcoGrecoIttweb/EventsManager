@extends('layouts.app')

@php
    use Illuminate\Support\Str;
@endphp

@section('title', 'Il salottino delle chat di Excursio - Excursio')

@section('content')
    <div class="container-fluid px-3 px-md-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-3 fs-2 fs-md-1 d-flex flex-wrap align-items-center gap-2 gap-md-3">
                    <i class="fas fa-comments align-middle" style="color:#157347;" aria-hidden="true"></i>
                    <span class="align-middle"
                          style="color:#0f5132; font-family: 'Curlz MT', 'Brush Script MT', cursive; font-style: italic; font-weight: 600; letter-spacing: 0.02em; text-shadow: 0 1px 3px rgba(15, 81, 50, 0.22);">
                        Il salottino delle chat di <span class="text-nowrap">Excursio</span>
                    </span>
                    <img src="{{ asset('upload_immagini/chat_salottino.webp') }}?v={{ file_exists(public_path('upload_immagini/chat_salottino.webp')) ? filemtime(public_path('upload_immagini/chat_salottino.webp')) : time() }}"
                         class="flex-shrink-0"
                         style="max-height: 6rem; width: auto; height: auto; object-fit: contain;"
                         alt="Excursio — salotto chat"
                         loading="lazy"
                         decoding="async">
                </h1>

                <div class="mb-4 text-center">
                    @if(!empty($headerImage))
                        @php
                            $cacheBuster = file_exists(public_path($headerImage)) ? filemtime(public_path($headerImage)) : time();
                        @endphp
                        <img src="{{ asset($headerImage) . '?v=' . $cacheBuster }}"
                             alt="Salotto di Excursio"
                             style="max-width: 260px; height:auto;"
                        >
                    @endif
                    @auth
                        @if(auth()->user()->isAdmin())
                            <form action="{{ route('chat.header-image') }}" method="POST" enctype="multipart/form-data" class="mt-2 d-inline-block">
                                @csrf
                                <div class="input-group input-group-sm">
                                    <input type="file" name="header_image" class="form-control form-control-sm" accept="image/*" required>
                                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-upload"></i> Cambia immagine
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-1">Solo amministratore: max 4MB.</small>
                            </form>
                        @endif
                    @endauth
                </div>

                <div class="alert alert-light border border-success border-opacity-25 mb-3 small" role="note">
                    <p class="mb-2">
                        <strong>Come funziona.</strong>
                        Il salottino è uno spazio dedicato agli utenti registrati di Excursio:
                        qui puoi scrivere messaggi brevi alla community (domande su eventi, suggerimenti, organizzazione o un saluto dopo un’uscita).
                    </p>
                    <p class="mb-2">
                        Usa il riquadro in basso e premi <strong>Invia</strong> per pubblicare.
                        Con <strong>Rispondi</strong> si antepone automaticamente <code>@nickname</code> al messaggio,
                        così è chiaro a chi ti stai rivolgendo.
                    </p>
                    <p class="mb-0 text-muted">
                        <strong>Perché c’è.</strong>
                        Tenere un contatto semplice e informale tra escursionisti, nel rispetto di tutti;
                        per le informazioni ufficiali continua a fare riferimento alle pagine evento.
                    </p>
                </div>

                <div class="mb-3" style="max-height: 400px; overflow-y: auto; border:1px solid #dee2e6; border-radius: .5rem; padding: .75rem; background:#fff;">
                    @forelse($messages as $message)
                        <div class="mb-3 p-2 rounded" style="background:#f8f9fa; border: 2px solid #5dade2;"
                             data-nickname="{{ $message->user?->nickname ?? 'Utente' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="small mb-1">
                                        <span class="d-inline-flex align-items-center gap-1 flex-wrap fw-semibold" style="color:#3E2723;">
                                            {{ $message->user?->nickname ?? 'Utente' }}
                                            @php
                                                $chatSesso = strtolower(trim((string) ($message->user?->sesso ?? '')));
                                            @endphp
                                            @if($chatSesso === 'm')
                                                <i class="fas fa-mars text-primary" title="Uomo" aria-hidden="true"></i>
                                                <span class="visually-hidden">Uomo</span>
                                            @elseif($chatSesso === 'f')
                                                <i class="fas fa-venus text-danger" title="Donna" aria-hidden="true"></i>
                                                <span class="visually-hidden">Donna</span>
                                            @endif
                                        </span>
                                        <span style="color:#1B5E20;"> — {{ $message->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    @php
                                        $lines = preg_split("/\r\n|\n|\r/", $message->content ?? '');
                                    @endphp
                                    @foreach($lines as $line)
                                        @php $trim = ltrim($line); @endphp
                                        @if(Str::startsWith($trim, '@'))
                                            <div class="fw-normal" style="background:#fff9c4; padding:2px 4px; border-radius:3px;">
                                                {{ $line }}
                                            </div>
                                        @else
                                            <div class="fw-normal">
                                                {{ $line }}
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                @auth
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary ms-2 chat-reply-btn"
                                            title="Rispondi a questo messaggio">
                                        <i class="fas fa-reply"></i>
                                    </button>
                                @endauth
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Nessun messaggio in chat. Scrivi tu il primo!</p>
                    @endforelse
                </div>

                @auth
                    <form action="{{ route('chat.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="content" class="form-label mb-2 d-block text-center w-100"
                                   style="font-family: 'Curlz MT', 'Brush Script MT', cursive; font-style: italic; color: #0f5132; font-weight: 600; letter-spacing: 0.02em; font-size: 1.75rem; line-height: 1.3; text-shadow: 0 1px 3px rgba(15, 81, 50, 0.22);">
                                Inserisci Il tuo messaggio
                            </label>
                            <textarea name="content" id="content" rows="3"
                                      class="form-control border border-2 border-success @error('content') is-invalid @enderror"
                                      maxlength="1000"
                                      placeholder="Scrivi qui il tuo messaggio per gli altri utenti registrati...">{{ old('content') }}</textarea>
                            @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Invia
                        </button>
                    </form>
                @else
                    <p class="text-muted">
                        Devi essere registrato per scrivere in chat.
                        <a href="{{ route('login') }}">Accedi</a> o <a href="{{ route('register') }}">registrati</a>.
                    </p>
                @endauth
            </div>
        </div>
    </div>
    <style>
        #content.form-control:not(.is-invalid) {
            border-width: 2px !important;
            border-color: #198754 !important;
        }
        #content.form-control:not(.is-invalid):focus {
            border-color: #146c43 !important;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }
    </style>
@endsection
@section('scripts')
    @parent
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const textarea = document.getElementById('content');
            if (!textarea) return;

            document.querySelectorAll('.chat-reply-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const box = this.closest('[data-nickname]');
                    if (!box) return;
                    const nick = box.getAttribute('data-nickname') || 'Utente';
                    const prefix = '@' + nick + ' ';

                    if (!textarea.value.startsWith(prefix)) {
                        textarea.value = prefix + textarea.value;
                    }
                    textarea.focus();
                    textarea.scrollIntoView({ behavior: 'smooth', block: 'end' });
                });
            });
        });
    </script>
@endsection

