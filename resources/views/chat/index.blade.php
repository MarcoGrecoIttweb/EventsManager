@extends('layouts.app')

@php
    use Illuminate\Support\Str;
    $chatBaseUrl = rtrim(request()->getBaseUrl(), '/');
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

                <div class="mb-3">
                    <button type="button"
                            class="btn btn-success w-100 chat-howto-toggle"
                            data-bs-toggle="collapse"
                            data-bs-target="#chatHowtoCollapse"
                            aria-expanded="false"
                            aria-controls="chatHowtoCollapse">
                        <i class="fas fa-circle-info me-1"></i> Come funziona
                    </button>
                    <div class="collapse mt-2" id="chatHowtoCollapse">
                        <div class="alert alert-light mb-0 small chat-howto-box" role="note">
                            <div class="d-flex justify-content-end mb-2">
                                <button type="button"
                                        class="btn btn-sm chat-howto-close-btn"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#chatHowtoCollapse"
                                        aria-expanded="true"
                                        aria-controls="chatHowtoCollapse">
                                    <i class="fas fa-times"></i> Chiudi
                                </button>
                            </div>
                            <p class="mb-2">
                                <strong>✨ Le "regole della casa" (Buon senso e Rispetto)</strong><br>
                                Vogliamo che questo spazio sia piacevole e accogliente per tutti.
                                Per far sì che funzioni, ti chiediamo di seguire queste piccole ma importanti regole di convivenza:
                            </p>
                            <ul class="mb-2 ps-3">
                                <li class="mb-1">
                                    <strong>Rispetto e Buone maniere</strong>: Sii sempre gentile. Non sono ammessi insulti, parolacce o toni aggressivi.
                                </li>
                                <li class="mb-1">
                                    <strong>Argomenti delicati</strong>: Per mantenere un clima sereno, ti chiediamo di evitare discussioni su politica e sesso, che spesso portano a inutili tensioni.
                                </li>
                                <li class="mb-1">
                                    <strong>No Discriminazione</strong>: Non tolleriamo alcun tipo di commento razzista, sessista o discriminatorio. Siamo qui per stare insieme, non per dividere!
                                </li>
                                <li class="mb-1">
                                    <strong>Niente Spam</strong>: Evita di inondare il Salottino con pubblicità, link esterni non richiesti o messaggi ripetitivi.
                                </li>
                                <li class="mb-1">
                                    <strong>La tua firma</strong>: Ricorda che sei l'unico responsabile di ciò che scrivi.
                                </li>
                                <li class="mb-0">
                                    <strong>Conseguenze</strong>:
                                    <span class="text-danger fw-semibold">
                                        In caso di lamentele fondate o comportamenti che violano queste regole, saremo costretti a ricorrere al ban dell'account. Ci teniamo troppo alla serenità del gruppo!
                                    </span>
                                </li>
                            </ul>
                            <p class="mb-0 fw-semibold">Buon divertimento!</p>
                        </div>
                    </div>
                </div>

                @auth
                    @if(isset($mentionAlerts) && $mentionAlerts->count() > 0)
                        <div class="alert alert-warning border border-2 border-warning-subtle mb-3 small" role="alert">
                            <div class="fw-semibold mb-2">
                                <i class="fas fa-bell"></i> Avviso: hai {{ $mentionAlerts->count() }} messagg{{ $mentionAlerts->count() > 1 ? 'i' : 'io' }} rivol{{ $mentionAlerts->count() > 1 ? 'ti' : 'to' }} a te.
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($mentionAlerts as $mAlert)
                                    <li class="mb-1">
                                        <strong>{{ $mAlert->user?->nickname ?? 'Utente' }}</strong>
                                        <span class="text-muted">({{ optional($mAlert->created_at)->format('d/m H:i') }})</span>:
                                        @php
                                            $alertText = (string) ($mAlert->content ?? '');
                                            $alertPreview = strlen($alertText) > 120 ? (substr($alertText, 0, 120) . '...') : $alertText;
                                        @endphp
                                        {{ $alertPreview }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endauth

                <div class="mb-3 chat-messages-box" style="max-height: 400px; overflow-y: auto;">
                    @forelse($messages as $message)
                        <div class="mb-3 p-2 rounded chat-message-item" style="background:#f8f9fa; border: 2px solid #5dade2;"
                             data-nickname="{{ $message->user?->nickname ?? 'Utente' }}"
                             data-message-id="{{ $message->id }}"
                             data-message-update-url="{{ $chatBaseUrl . '/chat/' . $message->id }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="small mb-1">
                                        <span class="d-inline-flex align-items-center gap-1 flex-wrap fw-semibold chat-nickname">
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
                                    <div class="d-flex align-items-start gap-1 ms-2">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary chat-reply-btn"
                                                title="Rispondi a questo messaggio">
                                            <i class="fas fa-reply"></i>
                                        </button>
                                        @if((int) auth()->id() === (int) $message->user_id || auth()->user()->isAdmin())
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary chat-edit-btn"
                                                    title="Modifica messaggio">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" action="{{ $chatBaseUrl . '/chat/' . $message->id }}" class="d-inline"
                                                  onsubmit="return confirm('Vuoi davvero eliminare questo messaggio?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Elimina messaggio">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endauth
                            </div>
                            @auth
                                @if((int) auth()->id() === (int) $message->user_id || auth()->user()->isAdmin())
                                    <div class="chat-edit-panel mt-2" style="display:none;">
                                        <form method="POST" action="{{ $chatBaseUrl . '/chat/' . $message->id }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="mb-2">
                                                <textarea name="content" class="form-control form-control-sm" rows="3" maxlength="1000" required>{{ $message->content }}</textarea>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-save"></i> Salva
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary chat-edit-cancel-btn">
                                                    <i class="fas fa-times"></i> Annulla
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            @endauth
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

        .chat-howto-box {
            border: 2px solid rgba(25, 135, 84, 0.75) !important;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.85);
            border-radius: 0.5rem;
            background: #fff9db !important; /* giallo chiaro */
        }

        .chat-messages-box {
            border: 2px solid rgba(25, 135, 84, 0.75);
            border-radius: 0.5rem;
            padding: 0.75rem;
            background: #fff;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.85);
        }

        .chat-howto-toggle {
            border-width: 2px;
            font-weight: 800;
        }

        .chat-howto-close-btn {
            background: #e9ecef;
            border: 2px solid #ced4da;
            color: #0d6efd;
            font-weight: 700;
        }
        .chat-howto-close-btn:hover,
        .chat-howto-close-btn:focus {
            background: #dee2e6;
            border-color: #adb5bd;
            color: #0b5ed7;
        }

        .chat-nickname {
            color: #4b2aad;
            font-size: 1.02rem;
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

            document.querySelectorAll('.chat-edit-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const box = this.closest('.chat-message-item');
                    if (!box) return;
                    const panel = box.querySelector('.chat-edit-panel');
                    if (!panel) return;
                    panel.style.display = '';
                    const ta = panel.querySelector('textarea[name="content"]');
                    if (ta) ta.focus();
                });
            });

            document.querySelectorAll('.chat-edit-cancel-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const panel = this.closest('.chat-edit-panel');
                    if (!panel) return;
                    panel.style.display = 'none';
                });
            });
        });
    </script>
@endsection

