@extends('layouts.app')

@php
    use Illuminate\Support\Str;
    $chatBaseUrl = rtrim(request()->getBaseUrl(), '/');
    $chatLogoRel = 'upload_immagini/chat_salottino.webp';
    $chatLogoV = time();
    $full = public_path($chatLogoRel);
    if (file_exists($full)) {
        $tmpMtime = filemtime($full);
        if (is_int($tmpMtime) && $tmpMtime > 0) {
            $chatLogoV = $tmpMtime;
        }
    }
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
                    <img src="{{ asset($chatLogoRel) }}?v={{ $chatLogoV }}"
                         class="flex-shrink-0"
                         style="max-height: 6rem; width: auto; height: auto; object-fit: contain;"
                         alt="Excursio — salotto chat"
                         loading="lazy"
                         decoding="async">
                </h1>

                <div class="mb-4 text-center">
                    @if(!empty($headerImage))
                        @php
                            $cacheBuster = time();
                            $headerFull = public_path($headerImage);
                            if (file_exists($headerFull)) {
                                $tmp = filemtime($headerFull);
                                if (is_int($tmp) && $tmp > 0) {
                                    $cacheBuster = $tmp;
                                }
                            }
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
                                @php
                                    $mc = (int) $mentionAlerts->count();
                                    $msgSuffix = 'io';
                                    $rivSuffix = 'to';
                                    if ($mc > 1) {
                                        $msgSuffix = 'i';
                                        $rivSuffix = 'ti';
                                    }
                                @endphp
                                <i class="fas fa-bell"></i> Avviso: hai {{ $mc }} messagg{{ $msgSuffix }} rivol{{ $rivSuffix }} a te.
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($mentionAlerts as $mAlert)
                                    <li class="mb-1">
                                        @php
                                            $mNick = optional($mAlert->user)->nickname;
                                            if (!is_string($mNick) || trim($mNick) === '') {
                                                $mNick = 'Utente';
                                            }
                                        @endphp
                                        <strong>{{ $mNick }}</strong>
                                        <span class="text-muted">({{ optional($mAlert->created_at)->format('d/m H:i') }})</span>:
                                        @php
                                            // Se il messaggio è HTML (admin), usa testo senza tag per l'anteprima.
                                            $alertRaw = $mAlert->content;
                                            if (!is_string($alertRaw)) {
                                                $alertRaw = '';
                                            }
                                            $alertText = strip_tags($alertRaw);
                                            $alertPreview = $alertText;
                                            if (strlen($alertText) > 120) {
                                                $alertPreview = substr($alertText, 0, 120) . '...';
                                            }
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
                             @php
                                 $msgNick = optional($message->user)->nickname;
                                 if (!is_string($msgNick) || trim($msgNick) === '') {
                                     $msgNick = 'Utente';
                                 }
                             @endphp
                             data-nickname="{{ $msgNick }}"
                             data-message-when="{{ $message->created_at->format('d/m/Y H:i') }}"
                             data-message-id="{{ $message->id }}"
                             data-message-update-url="{{ $chatBaseUrl . '/chat/' . $message->id }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="small mb-1">
                                        <span class="d-inline-flex align-items-center gap-1 flex-wrap fw-semibold chat-nickname">
                                            {{ $msgNick }}
                                            @php
                                                $rawSesso = optional($message->user)->sesso;
                                                if (!is_string($rawSesso)) {
                                                    $rawSesso = '';
                                                }
                                                $chatSesso = strtolower(trim($rawSesso));
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
                                    @if($message->user && $message->user->isAdmin())
                                        <div class="chat-rich-content">
                                            @php
                                                $html = $message->content;
                                                if (!is_string($html)) {
                                                    $html = '';
                                                }
                                                // Evidenzia "@ Risponde a nickname" (solo la scritta) in verde.
                                                $tmp = preg_replace('/(^\s*)@\s*Risponde\s+a\s+([^<\r\n]+?)(\s*\([^)]+\))?\s+/i', '$1<span class="chat-reply-prefix">@ Risponde a $2$3</span> ', $html, 1);
                                                if (is_string($tmp)) {
                                                    $html = $tmp;
                                                }
                                                $tmp2 = preg_replace('/(<p[^>]*>\s*)@\s*Risponde\s+a\s+([^<\r\n]+?)(\s*\([^)]+\))?\s+/i', '$1<span class="chat-reply-prefix">@ Risponde a $2$3</span> ', $html, 1);
                                                if (is_string($tmp2)) {
                                                    $html = $tmp2;
                                                }
                                            @endphp
                                            {!! $html !!}
                                        </div>
                                    @else
                                        @php
                                            $plain = $message->content;
                                            if (!is_string($plain)) {
                                                $plain = '';
                                            }
                                            $lines = preg_split("/\r\n|\n|\r/", $plain);
                                        @endphp
                                        @foreach($lines as $line)
                                            @php $trim = ltrim($line); @endphp
                                            @php
                                                $m = [];
                                                $isReplyLine = preg_match('/^@\s*Risponde\s+a\s+(\S+)(\s*\([^)]+\))?\s*(.*)$/i', $trim, $m) === 1;
                                            @endphp
                                            @if($isReplyLine)
                                                @php
                                                    $rest = '';
                                                    if (isset($m[3]) && is_string($m[3])) {
                                                        $rest = $m[3];
                                                    }
                                                @endphp
                                                <div class="fw-normal">
                                                    @php
                                                        $whenSuffix = '';
                                                        if (isset($m[2]) && is_string($m[2])) {
                                                            $whenSuffix = $m[2];
                                                        }
                                                    @endphp
                                                    <span class="chat-reply-prefix">@ Risponde a {{ $m[1] }}{{ $whenSuffix }}</span>
                                                    <span>{{ $rest }}</span>
                                                </div>
                                            @elseif(Str::startsWith($trim, '@'))
                                                <div class="fw-normal" style="background:#fff9c4; padding:2px 4px; border-radius:3px;">
                                                    {{ $line }}
                                                </div>
                                            @else
                                                <div class="fw-normal">
                                                    {{ $line }}
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
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
                        <input type="hidden" name="reply_to_nickname" id="reply_to_nickname" value="">
                        <input type="hidden" name="reply_to_when" id="reply_to_when" value="">
                        <div class="mb-3">
                            <label for="content" class="form-label mb-2 d-block text-center w-100"
                                   style="font-family: 'Curlz MT', 'Brush Script MT', cursive; font-style: italic; color: #0f5132; font-weight: 600; letter-spacing: 0.02em; font-size: 1.75rem; line-height: 1.3; text-shadow: 0 1px 3px rgba(15, 81, 50, 0.22);">
                                Inserisci Il tuo messaggio
                            </label>
                            <div class="chat-compose-box border border-2 border-success rounded">
                                <div id="replyBadge" class="d-none px-2 py-2 border-bottom border-2 border-success" style="background: rgba(25, 135, 84, 0.08);">
                                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                        <span class="chat-reply-prefix" id="replyBadgeText"></span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="replyBadgeClear">
                                            <i class="fas fa-times"></i> Annulla risposta
                                        </button>
                                    </div>
                                </div>
                            @if(auth()->user()->isAdmin())
                                <textarea name="content" id="chat_admin_content" rows="6"
                                          class="form-control border-0 @error('content') is-invalid @enderror"
                                          placeholder="Admin: puoi inserire testo formattato e immagini.">{{ old('content') }}</textarea>
                                @include('partials.ckeditor4-description', [
                                    'field' => 'chat_admin_content',
                                    'height' => 260,
                                    'editable_line_height' => 1.35,
                                    'editable_p_margin' => '0.25em'
                                ])
                                <div class="small text-muted mt-1">
                                    Solo amministratore: editor con immagini. Il contenuto viene ripulito automaticamente.
                                </div>
                            @else
                                <textarea name="content" id="content" rows="3"
                                          class="form-control border-0 @error('content') is-invalid @enderror"
                                          maxlength="1000"
                                          placeholder="Scrivi qui il tuo messaggio per gli altri utenti registrati...">{{ old('content') }}</textarea>
                            @endif
                            </div>
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
            box-shadow: none !important;
        }
        #chat_admin_content.form-control:not(.is-invalid) {
            box-shadow: none !important;
        }
        #content.form-control:not(.is-invalid):focus {
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }
        #chat_admin_content.form-control:not(.is-invalid):focus {
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

        .chat-rich-content img {
            max-width: 100%;
            height: auto;
        }
        .chat-emoji-btn {
            border: 1px solid #ced4da;
            font-size: 1.15rem;
            line-height: 1;
            padding: 0.25rem 0.45rem;
        }
        .chat-reply-prefix {
            color: #198754;
        }
    </style>
@endsection
@section('scripts')
    @parent
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const textarea = document.getElementById('content');
            const adminTextarea = document.getElementById('chat_admin_content');

            document.querySelectorAll('.chat-emoji-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (!textarea) return;
                    const emoji = btn.getAttribute('data-emoji') || '';
                    if (!emoji) return;
                    const start = (typeof textarea.selectionStart === 'number') ? textarea.selectionStart : textarea.value.length;
                    const end = (typeof textarea.selectionEnd === 'number') ? textarea.selectionEnd : textarea.value.length;
                    const before = textarea.value.slice(0, start);
                    const after = textarea.value.slice(end);
                    textarea.value = before + emoji + after;
                    const pos = start + emoji.length;
                    textarea.focus();
                    textarea.setSelectionRange(pos, pos);
                });
            });

            document.querySelectorAll('.chat-reply-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const box = this.closest('[data-nickname]');
                    if (!box) return;
                    const nick = box.getAttribute('data-nickname') || 'Utente';
                    const when = box.getAttribute('data-message-when') || '';
                    const suffix = when ? (' (' + when + ')') : '';
                    const prefix = '@ Risponde a ' + nick + suffix + ' ';

                    // Reply badge non cancellabile: salva metadati in hidden inputs.
                    const replyNickInput = document.getElementById('reply_to_nickname');
                    const replyWhenInput = document.getElementById('reply_to_when');
                    const replyBadge = document.getElementById('replyBadge');
                    const replyBadgeText = document.getElementById('replyBadgeText');
                    if (replyNickInput) replyNickInput.value = nick;
                    if (replyWhenInput) replyWhenInput.value = when;
                    if (replyBadgeText) replyBadgeText.textContent = '@ Risponde a ' + nick + suffix;
                    if (replyBadge) replyBadge.classList.remove('d-none');

                    // Focus editor/textarea
                    if (adminTextarea && typeof CKEDITOR !== 'undefined' && CKEDITOR.instances && CKEDITOR.instances['chat_admin_content']) {
                        CKEDITOR.instances['chat_admin_content'].focus();
                    } else if (adminTextarea) {
                        adminTextarea.focus();
                    } else if (textarea) {
                        textarea.focus();
                        textarea.scrollIntoView({ behavior: 'smooth', block: 'end' });
                    }
                });
            });

            const clearBtn = document.getElementById('replyBadgeClear');
            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    const replyNickInput = document.getElementById('reply_to_nickname');
                    const replyWhenInput = document.getElementById('reply_to_when');
                    const replyBadge = document.getElementById('replyBadge');
                    const replyBadgeText = document.getElementById('replyBadgeText');
                    if (replyNickInput) replyNickInput.value = '';
                    if (replyWhenInput) replyWhenInput.value = '';
                    if (replyBadgeText) replyBadgeText.textContent = '';
                    if (replyBadge) replyBadge.classList.add('d-none');
                });
            }

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

@section('sidebar_after_my_events')
    @auth
        @if(!auth()->user()->isAdmin())
            <div class="card card-sidebar sidebar-box--green mb-3">
                <div class="card-header py-2">
                    <small class="fw-bold">
                        <i class="fas fa-face-smile text-warning me-1"></i> Smile chat
                    </small>
                </div>
                <div class="card-body p-2">
                    @php
                        $emojis = ['😀','😁','😂','🤣','😊','😍','😘','😉','🤗','😎','🤔','😢','😭','😡','🙏','👏','👍','👎','❤️','🔥'];
                    @endphp
                    <div class="d-flex flex-wrap gap-1 justify-content-start">
                        @foreach($emojis as $emoji)
                            <button type="button" class="btn btn-light btn-sm chat-emoji-btn" data-emoji="{{ $emoji }}">{{ $emoji }}</button>
                        @endforeach
                    </div>
                    <small class="text-muted d-block mt-2">Clicca una faccina per inserirla nel messaggio.</small>
                </div>
            </div>
        @endif
    @endauth
@endsection

