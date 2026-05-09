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
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <button type="button"
                                class="btn btn-sm btn-success chat-howto-toggle"
                                data-bs-toggle="collapse"
                                data-bs-target="#chatHouseRulesCollapse"
                                aria-expanded="false"
                                aria-controls="chatHouseRulesCollapse">
                            <i class="fas fa-hand-sparkles me-1" aria-hidden="true"></i> Le regole della casa
                        </button>
                        <button type="button"
                                class="btn btn-sm btn-outline-success chat-howto-toggle"
                                data-bs-toggle="collapse"
                                data-bs-target="#chatHowtoCollapse"
                                aria-expanded="false"
                                aria-controls="chatHowtoCollapse">
                            <i class="fas fa-circle-info me-1" aria-hidden="true"></i> Salottino: come funziona
                        </button>
                    </div>

                    <div class="collapse mt-2" id="chatHouseRulesCollapse">
                        <div class="alert alert-light mb-2 small chat-howto-box" role="note">
                            <div class="d-flex justify-content-end mb-2">
                                <button type="button"
                                        class="btn btn-sm chat-howto-close-btn"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#chatHouseRulesCollapse"
                                        aria-expanded="true"
                                        aria-controls="chatHouseRulesCollapse">
                                    <i class="fas fa-times" aria-hidden="true"></i> Chiudi
                                </button>
                            </div>
                            <p class="mb-2">
                                <strong>✨ Le “regole della casa” (Buon senso e rispetto)</strong><br>
                                Vogliamo che questo spazio sia piacevole e accogliente per tutti.
                                Per far sì che funzioni, ti chiediamo di seguire queste piccole ma importanti regole di convivenza:
                            </p>
                            <ul class="mb-2 ps-3">
                                <li class="mb-1">
                                    <strong>Rispetto e buone maniere</strong>: sii sempre gentile. Non sono ammessi insulti, parolacce o toni aggressivi.
                                </li>
                                <li class="mb-1">
                                    <strong>Argomenti delicati</strong>: per mantenere un clima sereno, ti chiediamo di evitare discussioni su politica e sesso, che spesso portano a inutili tensioni.
                                </li>
                                <li class="mb-1">
                                    <strong>No discriminazione</strong>: non tolleriamo alcun tipo di commento razzista, sessista o discriminatorio. Siamo qui per stare insieme, non per dividere!
                                </li>
                                <li class="mb-1">
                                    <strong>Niente spam</strong>: evita di inondare il Salottino con pubblicità, link esterni non richiesti o messaggi ripetitivi.
                                </li>
                                <li class="mb-1">
                                    <strong>Indirizzare un messaggio (solo amministratore)</strong>: l’admin può cercare un utente abilitato e scegliere se inviare anche l’email di notifica o solo il messaggio in chat.
                                </li>
                                <li class="mb-1">
                                    <strong>La tua firma</strong>: ricorda che sei l’unico responsabile di ciò che scrivi.
                                </li>
                                <li class="mb-0">
                                    <strong>Conseguenze</strong>:
                                    <span class="text-danger fw-semibold">
                                        In caso di lamentele fondate o comportamenti che violano queste regole, saremo costretti a ricorrere al ban dell’account. Ci teniamo troppo alla serenità del gruppo!
                                    </span>
                                </li>
                            </ul>
                            <p class="mb-0 fw-semibold">Buon divertimento!</p>
                        </div>
                    </div>

                    <div class="collapse mt-2" id="chatHowtoCollapse">
                        <div class="alert alert-light mb-0 small chat-howto-box" role="note">
                            <div class="d-flex justify-content-end mb-2">
                                <button type="button"
                                        class="btn btn-sm chat-howto-close-btn"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#chatHowtoCollapse"
                                        aria-expanded="true"
                                        aria-controls="chatHowtoCollapse">
                                    <i class="fas fa-times" aria-hidden="true"></i> Chiudi
                                </button>
                            </div>
                            <h2 class="h6 fw-semibold mb-3">Salottino chat di Excursio — come funziona</h2>

                            <p class="mb-2"><strong>Cos’è:</strong> è una chat “pubblica” tipo bacheca (un unico salotto comune), dove i messaggi vengono salvati nel database e mostrati a tutti gli utenti abilitati.</p>

                            <p class="mb-2"><strong>Chi può entrare:</strong> solo utenti loggati e approvati (<code class="small">/chat</code>).</p>

                            <p class="mb-2"><strong>Cosa viene salvato:</strong> ogni messaggio finisce nella tabella <code class="small">chat_messages</code> con: <code class="small">user_id</code> (chi scrive), contenuto (testo/HTML), data/ora.</p>

                            <p class="mb-2"><strong>Cosa si vede in pagina:</strong> vengono caricati gli ultimi 100 messaggi, ordinati dal più vecchio al più nuovo, con nickname e data/ora.</p>

                            <p class="mb-2"><strong>Invio messaggi:</strong> per gli utenti, testo semplice (max ~1000 caratteri).</p>

                            <p class="mb-2"><strong>Regola importante (moderazione):</strong> se un messaggio contiene email o numeri di telefono, il contenuto viene <strong>bloccato</strong>: è vietato inviare email e numeri di telefono, per rispetto della privacy.</p>

                            <p class="mb-2"><strong>Risposte:</strong> puoi cliccare “Rispondi” su un messaggio: il sistema aggiunge in testa al testo <span class="text-nowrap">@ Risponde a NICK (data/ora)</span> e in pagina evidenzia visivamente risposta e messaggio “target”.</p>

                            <p class="mb-2"><strong>Notifiche:</strong> quando rispondi a qualcuno, può partire un’email di notifica al destinatario (se risolvibile e se l’opzione è attiva).</p>

                            <p class="mb-2"><strong>Menzioni (@nickname):</strong> se qualcuno scrive <span class="text-nowrap">@tuonick</span>, in alto compare un avviso; puoi chiuderlo (viene ricordato nel browser).</p>

                            <p class="mb-3"><strong>Gestione messaggi:</strong> chi scrive (o l’admin) può modificare o eliminare i propri messaggi.</p>

                            <p class="mb-0"><span class="fst-italic">Buone conversazioni</span><br><span class="text-muted">Lorise</span></p>
                        </div>
                    </div>
                </div>

                @auth
                    @if(isset($mentionAlerts) && $mentionAlerts->count() > 0)
                        <div class="alert alert-warning border border-2 border-warning-subtle mb-3 small chat-mention-alerts" role="alert">
                            <div class="fw-semibold mb-2 d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                @php
                                    $mc = (int) $mentionAlerts->count();
                                    $msgSuffix = 'io';
                                    $rivSuffix = 'to';
                                    if ($mc > 1) {
                                        $msgSuffix = 'i';
                                        $rivSuffix = 'ti';
                                    }
                                @endphp
                                <div class="min-w-0">
                                    <i class="fas fa-bell"></i> Avviso: hai {{ $mc }} messagg{{ $msgSuffix }} rivol{{ $rivSuffix }} a te.
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-dark chat-mention-clear-all" title="Nascondi tutti questi avvisi">
                                    <i class="fas fa-times"></i> Chiudi tutti
                                </button>
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($mentionAlerts as $mAlert)
                                    <li class="mb-1 d-flex align-items-start justify-content-between gap-2 chat-mention-item"
                                        data-alert-id="{{ (int) $mAlert->id }}">
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
                                        <span class="flex-grow-1 min-w-0">
                                            {{ $alertPreview }}
                                        </span>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary chat-mention-dismiss"
                                                title="Nascondi questo avviso">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endauth

                <div class="mb-3 chat-messages-box">
                    @forelse($messages as $message)
                        <div class="mb-3 p-2 rounded chat-message-item" id="msg-{{ (int) $message->id }}"
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
                                                // Evidenzia "@ Risponde a nickname" e manda a capo il testo successivo.
                                                $tmp = preg_replace('/(^\s*)@\s*Risponde\s+a\s+([^<\r\n]+?)(\s*\([^)]+\))?\s+/i', '$1<span class="chat-reply-prefix">@ Risponde a $2$3</span><br>', $html, 1);
                                                if (is_string($tmp)) {
                                                    $html = $tmp;
                                                }
                                                $tmp2 = preg_replace('/(<p[^>]*>\s*)@\s*Risponde\s+a\s+([^<\r\n]+?)(\s*\([^)]+\))?\s+/i', '$1<span class="chat-reply-prefix">@ Risponde a $2$3</span><br>', $html, 1);
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
                                            $isRemovedNotice = strip_tags($plain) === 'Messaggio rimosso: è vietato inviare Email e numero di telefono.';
                                            $lines = preg_split("/\r\n|\n|\r/", $plain);
                                        @endphp
                                        @if($isRemovedNotice)
                                            <div class="chat-removed-notice" role="alert">
                                                <i class="fas fa-ban"></i>
                                                Messaggio rimosso: è vietato inviare Email e numero di telefono.
                                            </div>
                                        @else
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
                                                    <div class="chat-reply-prefix">@ Risponde a {{ $m[1] }}{{ $whenSuffix }}</div>
                                                    <div>{{ $rest }}</div>
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
                        @if(auth()->user()->isAdmin())
                        <div class="mb-3 p-2 p-md-3 border border-success border-2 rounded bg-white small chat-addressed-user-box">
                            <label for="chatAddressedUserSearch" class="form-label fw-semibold mb-1 d-flex align-items-center gap-2">
                                <i class="fas fa-user-tag text-success" aria-hidden="true"></i>
                                Indirizza il messaggio a un utente (facoltativo, solo amministratore)
                            </label>
                            <div class="position-relative">
                                <input type="text"
                                       id="chatAddressedUserSearch"
                                       class="form-control form-control-sm"
                                       autocomplete="off"
                                       placeholder="Scrivi almeno 2 lettere del nickname (username)…"
                                       aria-describedby="chatAddressedUserHelp"
                                       maxlength="64">
                                <div id="chatAddressedUserSuggestions"
                                     class="list-group position-absolute w-100 shadow-sm d-none mt-1 rounded overflow-hidden border"
                                     style="z-index: 25; max-height: 11rem; overflow-y: auto;"
                                     role="listbox"
                                     aria-label="Utenti trovati"></div>
                            </div>
                            <div id="chatAddressedUserHelp" class="form-text mt-1">
                                Cerca tra gli utenti abilitati: dopo la scelta il nickname resta visibile nel campo. Il messaggio sarà intestato in chat; di default non parte l’email (puoi attivarla sotto). Per «Rispondi» a un messaggio vale sempre la notifica email come prima.
                            </div>
                            <div class="mt-2 pt-2 border-top border-success-subtle">
                                <div class="fw-semibold small mb-1">Notifica email al destinatario</div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="addressed_notify_email" id="chat_addr_email_0" value="0" checked>
                                    <label class="form-check-label" for="chat_addr_email_0">No, solo messaggio in chat</label>
                                </div>
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="radio" name="addressed_notify_email" id="chat_addr_email_1" value="1">
                                    <label class="form-check-label" for="chat_addr_email_1">Sì, invia anche l’email</label>
                                </div>
                            </div>
                        </div>
                        @endif
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
                        @php
                            $emojis = ['😀','😁','😂','🤣','😊','😍','😘','😉','🤗','😎','🤔','😢','😭','😡','🙏','👏','👍','👎','❤️','🔥'];
                        @endphp
                        <div class="d-flex align-items-start gap-2 flex-wrap flex-sm-nowrap">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Invia
                            </button>
                            <div class="chat-emoji-box border rounded p-2 bg-light">
                                <div class="d-flex flex-wrap gap-1 justify-content-start">
                                    @foreach($emojis as $emoji)
                                        <button type="button" class="btn btn-light btn-sm chat-emoji-btn" data-emoji="{{ $emoji }}">{{ $emoji }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
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
        .chat-message-item {
            background: #f8f9fa;
            border: 2px solid var(--user-color, #5dade2);
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.85);
        }

        /* Messaggio che contiene una risposta: bordo = colore del destinatario risposta */
        .chat-message-item.chat-message--is-reply {
            border-color: var(--reply-color, var(--user-color, #5dade2));
        }

        /* Messaggio a cui si sta rispondendo: evidenziato con lo stesso colore */
        .chat-message-item.chat-message--replied-target {
            outline: 3px solid var(--reply-color, #198754);
            outline-offset: 2px;
            box-shadow:
                0 0 0 2px rgba(255, 255, 255, 0.85),
                0 0 18px color-mix(in srgb, var(--reply-color, #198754) 55%, transparent 45%);
        }

        .chat-message-item .chat-reply-prefix {
            display: inline-block;
            padding: 0.12rem 0.35rem;
            border-radius: 0.4rem;
            border: 2px solid var(--reply-color, rgba(25, 135, 84, 0.5));
            background: color-mix(in srgb, var(--reply-color, #198754) 18%, #fff 82%);
        }

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
            border: 3px solid #5c2d04;
            border-radius: 0.5rem;
            padding: 0.75rem;
            background: #fff;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.85);
            /* Quasi tutta l’altezza utile della finestra, con tetto su monitor molto alti */
            max-height: min(1600px, calc(100vh - 100px));
            overflow-y: auto;
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
        .chat-emoji-box {
            max-width: 22rem;
        }
        .chat-emoji-box .chat-emoji-btn {
            min-width: 2.1rem;
            text-align: center;
        }
        .chat-reply-prefix {
            color: #198754;
        }

        .chat-removed-notice {
            border: 2px solid rgba(220, 53, 69, 0.55);
            background: rgba(220, 53, 69, 0.08);
            color: #842029;
            padding: 0.35rem 0.5rem;
            border-radius: 0.5rem;
            font-weight: 700;
        }

        .chat-addressed-user-box .list-group-item {
            cursor: pointer;
            font-size: 0.9rem;
        }
        .chat-addressed-user-box .list-group-item:hover,
        .chat-addressed-user-box .list-group-item:focus {
            background: rgba(25, 135, 84, 0.12);
        }
    </style>
@endsection
@section('scripts')
    @parent
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const textarea = document.getElementById('content');
            const adminTextarea = document.getElementById('chat_admin_content');
            const chatUsersAutocompleteUrl = @json(route('users.autocomplete'));

            const replyNickInput = document.getElementById('reply_to_nickname');
            const replyWhenInput = document.getElementById('reply_to_when');
            const replyBadge = document.getElementById('replyBadge');
            const replyBadgeText = document.getElementById('replyBadgeText');

            function resetAddressedEmailRadios() {
                var r1 = document.getElementById('chat_addr_email_1');
                var r0 = document.getElementById('chat_addr_email_0');
                if (r0) r0.checked = true;
                if (r1) r1.checked = false;
            }

            function applyChatReplyTarget(nick, when) {
                var n = (nick || '').trim();
                var w = (when || '').trim();
                if (replyNickInput) replyNickInput.value = n;
                if (replyWhenInput) replyWhenInput.value = w;
                if (replyBadgeText) {
                    if (!n) {
                        replyBadgeText.textContent = '';
                    } else {
                        replyBadgeText.textContent = '@ Risponde a ' + n + (w ? (' (' + w + ')') : '');
                    }
                }
                if (replyBadge) {
                    if (n) replyBadge.classList.remove('d-none');
                    else replyBadge.classList.add('d-none');
                }
                if (!n) {
                    resetAddressedEmailRadios();
                    var addrClear = document.getElementById('chatAddressedUserSearch');
                    if (addrClear) addrClear.value = '';
                }
            }

            (function setupChatAddressedUserSearch() {
                var input = document.getElementById('chatAddressedUserSearch');
                var box = document.getElementById('chatAddressedUserSuggestions');
                if (!input || !box) return;

                var timer = null;

                function hideSuggestions() {
                    box.classList.add('d-none');
                    box.innerHTML = '';
                }

                function renderResults(rows) {
                    box.innerHTML = '';
                    if (!rows || !rows.length) {
                        hideSuggestions();
                        return;
                    }
                    rows.forEach(function (row) {
                        var un = (row.username || '').toString();
                        if (!un) return;
                        var a = document.createElement('button');
                        a.type = 'button';
                        a.className = 'list-group-item list-group-item-action border-0 rounded-0 text-start';
                        a.setAttribute('role', 'option');
                        a.dataset.username = un;
                        var lab = (row.label || un).toString();
                        a.textContent = lab;
                        a.addEventListener('mousedown', function (e) {
                            e.preventDefault();
                            applyChatReplyTarget(un, '');
                            input.value = un;
                            hideSuggestions();
                            if (adminTextarea && typeof CKEDITOR !== 'undefined' && CKEDITOR.instances && CKEDITOR.instances['chat_admin_content']) {
                                CKEDITOR.instances['chat_admin_content'].focus();
                            } else if (adminTextarea) {
                                adminTextarea.focus();
                            } else if (textarea) {
                                textarea.focus();
                                textarea.scrollIntoView({ behavior: 'smooth', block: 'end' });
                            }
                        });
                        box.appendChild(a);
                    });
                    box.classList.remove('d-none');
                }

                input.addEventListener('input', function () {
                    if (timer) clearTimeout(timer);
                    var q = (input.value || '').trim();
                    if (q.length < 2) {
                        hideSuggestions();
                        return;
                    }
                    timer = setTimeout(function () {
                        var url = chatUsersAutocompleteUrl + '?q=' + encodeURIComponent(q);
                        fetch(url, {
                            headers: { Accept: 'application/json' },
                            credentials: 'same-origin',
                        })
                            .then(function (r) { return r.json(); })
                            .then(function (data) {
                                renderResults(data.results || []);
                            })
                            .catch(function () {
                                hideSuggestions();
                            });
                    }, 250);
                });

                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') hideSuggestions();
                });

                document.addEventListener('click', function (e) {
                    if (!box.contains(e.target) && e.target !== input) hideSuggestions();
                });
            })();

            document.querySelectorAll('.chat-emoji-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const emoji = btn.getAttribute('data-emoji') || '';
                    if (!emoji) return;

                    // Admin: inserisci nell'editor se presente.
                    if (adminTextarea && typeof CKEDITOR !== 'undefined' && CKEDITOR.instances && CKEDITOR.instances['chat_admin_content']) {
                        CKEDITOR.instances['chat_admin_content'].focus();
                        CKEDITOR.instances['chat_admin_content'].insertText(emoji);
                        return;
                    }

                    // Fallback: textarea utenti
                    if (!textarea) return;
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
                    var addr = document.getElementById('chatAddressedUserSearch');
                    if (addr) addr.value = '';
                    resetAddressedEmailRadios();
                    applyChatReplyTarget(nick, when);

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
                    applyChatReplyTarget('', '');
                    var addr = document.getElementById('chatAddressedUserSearch');
                    if (addr) addr.value = '';
                    var sug = document.getElementById('chatAddressedUserSuggestions');
                    if (sug) {
                        sug.innerHTML = '';
                        sug.classList.add('d-none');
                    }
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

            // Colori per utente (stabili): applica bordo box e colore risposte.
            (function () {
                var palette = [
                    '#0d6efd', // blu
                    '#198754', // verde
                    '#dc3545', // rosso
                    '#fd7e14', // arancio
                    '#6f42c1', // viola
                    '#20c997', // teal
                    '#d63384', // magenta
                    '#0dcaf0', // cyan
                    '#6610f2', // indigo
                    '#795548', // brown
                ];

                function hashStr(s) {
                    s = (s || '').toString();
                    var h = 0;
                    for (var i = 0; i < s.length; i++) {
                        h = ((h << 5) - h) + s.charCodeAt(i);
                        h |= 0;
                    }
                    return Math.abs(h);
                }

                function colorForNick(nick) {
                    var key = (nick || 'Utente').toString().trim().toLowerCase();
                    var idx = hashStr(key) % palette.length;
                    return palette[idx];
                }

                document.querySelectorAll('.chat-message-item[data-nickname]').forEach(function (box) {
                    var nick = box.getAttribute('data-nickname') || 'Utente';
                    box.style.setProperty('--user-color', colorForNick(nick));
                });

                // Per ogni prefisso "Risponde a X", colora come X.
                document.querySelectorAll('.chat-reply-prefix').forEach(function (el) {
                    var t = (el.textContent || '').trim();
                    var m = t.match(/@?\s*Risponde\s+a\s+(.+?)(\s*\(|$)/i);
                    if (!m || !m[1]) return;
                    var nick = (m[1] || '').trim();
                    var replyColor = colorForNick(nick);
                    el.style.setProperty('--reply-color', replyColor);

                    // Applica al messaggio di risposta: bordo = colore destinatario risposta
                    var replyBox = el.closest('.chat-message-item');
                    if (replyBox) {
                        replyBox.style.setProperty('--reply-color', replyColor);
                        replyBox.classList.add('chat-message--is-reply');
                    }

                    // Se c'è anche la data/orario tra parentesi, prova a trovare il messaggio originale e evidenzialo.
                    // Formato: "@ Risponde a NICK (dd/mm/YYYY HH:ii)"
                    var m2 = t.match(/@?\s*Risponde\s+a\s+(.+?)\s*\(([^)]+)\)\s*$/i);
                    if (!m2 || !m2[1] || !m2[2]) return;
                    var targetNick = String(m2[1]).trim();
                    var targetWhen = String(m2[2]).trim();

                    var target = document.querySelector(
                        '.chat-message-item[data-nickname="' + CSS.escape(targetNick) + '"][data-message-when="' + CSS.escape(targetWhen) + '"]'
                    );
                    if (target) {
                        target.style.setProperty('--reply-color', replyColor);
                        target.classList.add('chat-message--replied-target');
                    }
                });
            })();

            // Dismiss avvisi menzioni (client-side, persistente via localStorage)
            (function () {
                var root = document.querySelector('.chat-mention-alerts');
                if (!root) return;

                var storageKey = 'excursio_chat_dismissed_mention_alert_ids';
                function loadSet() {
                    try {
                        var raw = localStorage.getItem(storageKey);
                        var arr = raw ? JSON.parse(raw) : [];
                        if (!Array.isArray(arr)) arr = [];
                        var set = {};
                        arr.forEach(function (id) { set[String(id)] = true; });
                        return set;
                    } catch (e) {
                        return {};
                    }
                }
                function saveSet(set) {
                    try {
                        var ids = Object.keys(set).slice(-500);
                        localStorage.setItem(storageKey, JSON.stringify(ids));
                    } catch (e) {}
                }

                var dismissed = loadSet();
                var items = Array.prototype.slice.call(root.querySelectorAll('.chat-mention-item[data-alert-id]'));

                function hideIfDismissed(li) {
                    var id = li.getAttribute('data-alert-id');
                    if (!id) return false;
                    if (dismissed[String(id)]) {
                        li.remove();
                        return true;
                    }
                    return false;
                }

                items.forEach(hideIfDismissed);

                // Se non resta nulla, nascondi l'intero box
                function cleanupBox() {
                    var any = root.querySelector('.chat-mention-item[data-alert-id]');
                    if (!any) {
                        root.remove();
                    }
                }

                root.querySelectorAll('.chat-mention-dismiss').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var li = btn.closest('.chat-mention-item');
                        if (!li) return;
                        var id = li.getAttribute('data-alert-id');
                        if (id) {
                            dismissed[String(id)] = true;
                            saveSet(dismissed);
                        }
                        li.remove();
                        cleanupBox();
                    });
                });

                var clearAll = root.querySelector('.chat-mention-clear-all');
                if (clearAll) {
                    clearAll.addEventListener('click', function () {
                        root.querySelectorAll('.chat-mention-item[data-alert-id]').forEach(function (li) {
                            var id = li.getAttribute('data-alert-id');
                            if (id) {
                                dismissed[String(id)] = true;
                            }
                            li.remove();
                        });
                        saveSet(dismissed);
                        cleanupBox();
                    });
                }

                cleanupBox();
            })();
        });
    </script>
@endsection

@section('sidebar_after_my_events')
    @auth
    @endauth
@endsection

