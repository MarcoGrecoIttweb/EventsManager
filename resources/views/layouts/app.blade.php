<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Excursio - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS Personalizzato -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        .site-header {
            background: #fff;
            padding: 6px 0;
        }
        .site-header-logo {
            max-height: 100px;
            width: auto;
        }

        @media (max-width: 767.98px) {
            .site-header {
                padding: 4px 0.5rem;
            }
            .site-header-logo {
                max-height: 52px;
            }
        }

        @media (max-width: 374.98px) {
            .site-header-logo {
                max-height: 44px;
            }
        }

        /* Sidebar boxes: evita tagli su smartphone */
        @media (max-width: 767.98px) {
            .card-sidebar.sidebar-box--green .card-body {
                max-height: none !important;
                overflow-y: visible !important;
            }
            .card-sidebar.sidebar-box--green .card-header small {
                font-size: 0.92rem;
            }
        }
        .sidebar-left {
            position: sticky;
            top: 1rem;
            align-self: flex-start;
            /* Niente scroll interno: i box non devono "sparire" */
            max-height: none;
            overflow: visible;
        }
        .card-sidebar {
            font-size: 0.85rem;
            border-radius: 8px;
        }
        .card-sidebar .card-header {
            background: #f8f9fa;
        }
        /* Sidebar: box "Utenti online" e "Eventi attivi" in verde */
        .card-sidebar.sidebar-box--green {
            border: 2px solid #198754 !important;
        }
        .card-sidebar.sidebar-box--green .card-header {
            background: rgba(25, 135, 84, 0.12) !important;
            border-bottom: 1px solid rgba(25, 135, 84, 0.35) !important;
        }
        .card-sidebar.sidebar-box--green .card-header small {
            color: #145c36;
        }
        .card-sidebar.sidebar-box--green .card-header i {
            color: #198754 !important;
        }
        /* Box "Eventi attivi": riga tra i titoli + righe alternate leggibili */
        .sidebar-active-events-list__item {
            border-bottom: 1px solid rgba(25, 135, 84, 0.28);
            padding: 0.4rem 0.35rem;
            margin-left: -0.25rem;
            margin-right: -0.25rem;
            border-radius: 4px;
        }
        .sidebar-active-events-list__item:last-child {
            border-bottom: none;
        }
        .sidebar-active-events-list__item:nth-child(odd) {
            background: rgba(255, 255, 255, 0.95);
        }
        .sidebar-active-events-list__item:nth-child(even) {
            background: rgba(25, 135, 84, 0.12);
        }
        .sidebar-active-events-list__item:hover {
            background: rgba(25, 135, 84, 0.22) !important;
        }
        .sidebar-active-events-list__link {
            display: block;
            color: #0f5132;
            font-weight: 500;
        }
        .sidebar-active-events-list__item:nth-child(3n+1) .sidebar-active-events-list__link {
            color: #0b5a32;
        }
        .sidebar-active-events-list__item:nth-child(3n+2) .sidebar-active-events-list__link {
            color: #0d4a7a;
        }
        .sidebar-active-events-list__item:nth-child(3n) .sidebar-active-events-list__link {
            color: #5c2d04;
        }
        .online-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #28a745;
            flex-shrink: 0;
            margin-right: 6px;
        }
        .online-user-row:hover {
            background: #f8f9fa;
            border-radius: 4px;
            padding-left: 2px;
        }
        @media (max-width: 767.98px) {
            /* Smartphone: sidebar non sticky, niente colonna “fissa” sullo sfondo */
            .sidebar-left {
                position: static;
                max-height: none;
                overflow: visible;
            }
        }

        /* Elenco eventi (card griglia): bordo cyan/azzurro + anello bianco — mobile e desktop */
        .card.event-box {
            border: 3px solid #0dcaf0 !important;
            box-shadow: 0 0 0 2px #fff, 0 0.125rem 0.35rem rgba(13, 202, 240, 0.2) !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card.event-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 0 2px #fff, 0 4px 18px rgba(13, 202, 240, 0.25) !important;
        }
        /* Evento al completo: bordo rosso (override del bordo cyan) */
        .card.event-box.event-box--full {
            border-color: #dc3545 !important;
        }
        /* Titoli delle card eventi centrati */
        .card.event-box .card-title {
            text-align: center;
        }
        /* Pulsante guest: verde chiaro */
        .btn-guest-details {
            background: #b7f3c2;
            border-color: #7adf92;
            color: #0f5132;
        }
        .btn-guest-details:hover {
            background: #a6eeb4;
            border-color: #63d87f;
            color: #0f5132;
        }
        /* Desktop (md+): navbar orizzontale */
        @media (min-width: 768px) {
            .excursio-navbar__main-nav,
            ul.excursio-navbar__main-nav {
                flex-wrap: wrap !important;
                align-items: center !important;
            }
            .excursio-navbar__main-nav .nav-item {
                white-space: nowrap;
            }
            .excursio-navbar__main-nav .nav-link,
            .excursio-navbar__main-nav .navbar-text {
                padding-right: 0.5rem !important;
                padding-left: 0.5rem !important;
                font-size: 0.85rem;
            }
            .excursio-navbar__user-nav,
            ul.excursio-navbar__user-nav {
                flex-direction: row !important;
                margin-left: auto !important;
                align-items: center !important;
            }
        }
        /* Mobile: separatore visivo tra nav principale e logout */
        @media (max-width: 767.98px) {
            .excursio-navbar__user-nav {
                margin-top: 0.35rem;
                padding-top: 0.5rem;
                border-top: 1px solid rgba(255, 255, 255, 0.25);
            }
        }
        .excursio-navbar__main-nav .navbar-text,
        .excursio-navbar__user-nav .navbar-text {
            font-size: 0.9rem;
            max-width: min(18rem, 85vw);
            overflow: hidden;
            text-overflow: ellipsis;
        }
        /* Niente striscia/bordo chiaro sotto i link della barra nera */
        .excursio-navbar,
        .excursio-navbar .nav-link,
        .excursio-navbar .navbar-brand,
        .excursio-navbar .btn-link {
            border-bottom: 0 !important;
            box-shadow: none !important;
        }
        /* Sfondo navbar: grigio scuro */
        .excursio-navbar {
            background-color: #3d3d3d !important;
        }
        /* Testi e link della navbar: giallo ocra */
        .excursio-navbar,
        .excursio-navbar .nav-link,
        .excursio-navbar .navbar-brand,
        .excursio-navbar .navbar-text,
        .excursio-navbar .btn-link {
            color: #FFB300 !important;
        }
        .excursio-navbar .nav-link:hover,
        .excursio-navbar .nav-link:focus,
        .excursio-navbar .navbar-brand:hover,
        .excursio-navbar .btn-link:hover {
            color: #FFCC33 !important;
        }
        /* "Stai impersonando NOME": resta bianco, non giallo ocra */
        .excursio-navbar .excursio-navbar-impersonating,
        .excursio-navbar .excursio-navbar-impersonating strong {
            color: #ffffff !important;
        }

        /* Header + navbar sempre visibili in alto durante lo scroll */
        .site-nav-sticky {
            position: sticky;
            top: 0;
            z-index: 1030;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        .site-nav-sticky .excursio-navbar {
            box-shadow: none;
        }
        :root {
            --site-sticky-header-h: 9rem;
        }
        /* Sidebar sotto l’header sticky (solo da md, dove la sidebar è sticky) */
        @media (min-width: 768px) {
            .sidebar-left {
                /* evita sovrapposizione con header sticky (logo+navbar) */
                top: calc(var(--site-sticky-header-h) + 0.75rem);
            }
        }

        /* (rimossa) Banner admin: nuove iscrizioni da approvare */
    </style>
</head>
<body>
<header class="site-nav-sticky">
<div class="site-header text-center">
    <a href="{{ route('home') }}">
        <img src="{{ asset('upload_immagini/excursio.png') }}" alt="Excursio" class="site-header-logo">
    </a>
</div>
<nav class="navbar navbar-expand-md navbar-dark bg-dark excursio-navbar py-1">
    <div class="container-fluid px-3 px-xl-4">
        <a href="{{ route('home') }}" class="navbar-brand mb-0 text-white fw-semibold py-1" title="Home">
            <i class="fas fa-home me-1"></i><span class="d-none d-sm-inline">Home</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Apri il menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse d-md-flex" id="navbarNav">
            @php
                $isAdmin = auth()->check() && auth()->user()->isAdmin();
                // Mostra sempre il link chat agli utenti loggati: se la feature è OFF, verrà mostrata la pagina "in arrivo".
                $showChatLink = auth()->check() ? true : (($featureChatSalottinoEnabled ?? true) || $isAdmin);
                // Link Mercatino (vetrina) anche per i visitatori: se la feature è OFF, badge / redirect "in arrivo".
                $showMercatinoLink = auth()->check() ? true : (($featureMercatinoEnabled ?? true) || $isAdmin);
                // Stessa logica degli album foto: in passato il link c'era solo per @guest e nel menu Admin — così spariva dopo il login.
                $showAlbumsFotoLink = auth()->check() ? true : (($featureAlbumsFotoEnabled ?? true) || $isAdmin);
            @endphp
            <ul class="navbar-nav excursio-navbar__main-nav me-auto mb-2 mb-lg-0">
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt"></i> Accedi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}" data-hint="Non sei ancora registrato? Crea un account in pochi secondi.">
                            <i class="fas fa-user-plus"></i> Registrati
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}" id="btn-chi-siamo" data-hint="Scopri chi siamo e cosa facciamo">
                            <i class="fas fa-info-circle"></i> Conoscici meglio
                        </a>
                    </li>
                    @if($showAlbumsFotoLink)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('photo-albums.index') }}">
                                <i class="fas fa-images"></i> Album foto Eventi
                                @if(!($featureAlbumsFotoEnabled ?? true))
                                    <span class="ms-1 badge bg-warning text-dark">IN ARRIVO</span>
                                @endif
                            </a>
                        </li>
                    @endif
                @else
                <li class="nav-item d-flex align-items-center me-1 me-md-2">
                    <span class="navbar-text text-white-50 small">
                        <i class="fas fa-smile-beam me-1"></i>
                        {{ (auth()->user()->sesso ?? '') === 'f' ? 'Benvenuta' : 'Benvenuto' }}
                        <strong class="text-white">{{ auth()->user()->username }}</strong>
                    </span>
                </li>
                @if(session()->has('impersonator_id'))
                    <li class="nav-item d-flex align-items-center">
                        <span class="navbar-text small excursio-navbar-impersonating">
                            <i class="fas fa-user-secret me-1"></i>
                            Stai impersonando <strong>{{ session('impersonated_username') }}</strong>
                        </span>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('impersonation.stop') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link text-warning" title="Torna admin">
                                <i class="fas fa-undo"></i> Torna admin
                            </button>
                        </form>
                    </li>
                @endif
                {{-- Nav autenticato: ordine separato admin / utente (`resources/views/layouts/app.blade.php`) --}}
                @if($isAdmin)
                    {{-- Amministratore: ordine richiesto (benvenuto + impersonazione sopra) --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-crown"></i> Admin
                            @if(($adminPendingUsersCount ?? 0) > 0)
                                <span class="badge bg-danger rounded-pill ms-1" title="Iscrizioni in attesa di approvazione">{{ $adminPendingUsersCount }}</span>
                            @endif
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li><a class="dropdown-item" href="{{ route('users.search') }}"><i class="fas fa-search me-1"></i>Cerca utenti</a></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center justify-content-between gap-2 {{ ($adminPendingUsersCount ?? 0) > 0 ? 'fw-semibold text-dark' : '' }}"
                                   href="{{ route('admin.users.index') }}">
                                    <span>Gestione utenti</span>
                                    @if(($adminPendingUsersCount ?? 0) > 0)
                                        <span class="badge bg-warning text-dark">{{ $adminPendingUsersCount }} in attesa</span>
                                    @endif
                                </a>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('admin.events.index') }}">Gestione Eventi</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.users.gallery') }}">Admin immagini utenti</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.common-event.form') }}">Eventi in comune</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.groups.index') }}">Gestione gruppi</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.newsletter.create') }}">Newsletter</a></li>
                        </ul>
                    </li>
                    @if(auth()->user()->canManageEvents())
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="manageDropdownAdmin" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-calendar-plus"></i> Gestione Eventi
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="manageDropdownAdmin">
                                <li><a class="dropdown-item" href="{{ route('manage.events.index') }}">Gestisci eventi</a></li>
                                <li><a class="dropdown-item" href="{{ route('manage.events.create') }}">Crea Evento</a></li>
                            </ul>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('my-events.active') }}" data-hint="Visualizza e gestisci gli eventi a cui sei iscritto">
                            <i class="fas fa-calendar-check"></i> Eventi in programma
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('events.past') }}" data-hint="Eventi passati &amp; Votazioni degli eventi">
                            <i class="fas fa-history"></i> Eventi passati &amp; Voti
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('profile.show', auth()->user()) }}" data-hint="Visualizza il tuo profilo">
                            <i class="fas fa-user-circle"></i> Profilo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('friends.index') }}">
                            <i class="fas fa-user-friends"></i> Amici
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('users.index') }}">
                            <i class="fas fa-users"></i> Profili
                        </a>
                    </li>
                    @if($showChatLink)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('chat.index') }}">
                                <i class="fas fa-comments"></i> Salottino delle chat
                                @if(!($featureChatSalottinoEnabled ?? true))
                                    <span class="ms-1 badge bg-secondary">OFF</span>
                                @endif
                            </a>
                        </li>
                    @endif
                    @if($showAlbumsFotoLink)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('photo-albums.index') }}">
                                <i class="fas fa-images"></i> Album foto Eventi
                                @if(!($featureAlbumsFotoEnabled ?? true))
                                    <span class="ms-1 badge bg-secondary">OFF</span>
                                @endif
                            </a>
                        </li>
                    @endif
                    @if($showMercatinoLink)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('mercatino.vetrina') }}">
                                <i class="fas fa-store"></i> Mercatino
                                @if(!($featureMercatinoEnabled ?? true))
                                    <span class="ms-1 badge bg-secondary">OFF</span>
                                @endif
                            </a>
                        </li>
                    @endif
                @else
                    {{-- Utente non admin: ordine classico community --}}
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('my-events.active') }}" data-hint="Visualizza e gestisci gli eventi a cui sei iscritto">
                            <i class="fas fa-calendar-check"></i> Eventi in programma
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('profile.show', auth()->user()) }}" data-hint="Visualizza il tuo profilo">
                            <i class="fas fa-user-circle"></i> Profilo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('users.search') }}" data-hint="Trova i tuoi amici con un click">
                            <i class="fas fa-search"></i> Cerca amici
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.common-event.form') }}">
                            <i class="fas fa-random"></i> Eventi in comune
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('friends.index') }}">
                            <i class="fas fa-user-friends"></i> Amici
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('users.index') }}">
                            <i class="fas fa-users"></i> Profili
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('events.past') }}" data-hint="Eventi passati &amp; Votazioni degli eventi">
                            <i class="fas fa-history"></i> Eventi passati &amp; Voti
                        </a>
                    </li>
                    @if($showAlbumsFotoLink)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('photo-albums.index') }}">
                                <i class="fas fa-images"></i> Album foto Eventi
                                @if(!($featureAlbumsFotoEnabled ?? true))
                                    <span class="ms-1 badge bg-warning text-dark">IN ARRIVO</span>
                                @endif
                            </a>
                        </li>
                    @endif
                    @if($showChatLink)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('chat.index') }}">
                                <i class="fas fa-comments"></i> Salottino delle chat
                                @if(!($featureChatSalottinoEnabled ?? true))
                                    <span class="ms-1 badge bg-warning text-dark">IN ARRIVO</span>
                                @endif
                            </a>
                        </li>
                    @endif
                    @if($showMercatinoLink)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('mercatino.vetrina') }}">
                                <i class="fas fa-store"></i> Mercatino
                                @if(!($featureMercatinoEnabled ?? true))
                                    <span class="ms-1 badge bg-warning text-dark">IN ARRIVO</span>
                                @endif
                            </a>
                        </li>
                    @endif
                    @if(auth()->user()->canManageEvents())
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="manageDropdown" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-calendar-plus"></i> Gestione eventi
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="manageDropdown">
                                <li><a class="dropdown-item" href="{{ route('manage.events.index') }}">Gestisci eventi</a></li>
                                <li><a class="dropdown-item" href="{{ route('manage.events.create') }}">Crea Evento</a></li>
                            </ul>
                        </li>
                    @endif
                @endif
                @endguest
            </ul>
            @auth
            <ul class="navbar-nav excursio-navbar__user-nav">
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nav-link btn btn-link">
                            <i class="fas fa-sign-out-alt"></i> Esci
                        </button>
                    </form>
                </li>
            </ul>
            @endauth
        </div>
    </div>
</nav>
</header>

<div class="container-fluid mt-4">
    @php $hideSidebar = View::hasSection('no_sidebar'); @endphp
    <div class="row">

        {{-- Sidebar sinistra (nascosta nelle pagine auth) --}}
        @if(!$hideSidebar)
        <div class="col-md-2 sidebar-left">
            @guest
                {{-- Form login sidebar: nascosto su smartphone (login dalla navbar) --}}
                <div class="card card-sidebar mb-3 d-none d-md-block" style="border: 1px solid #198754;">
                    <div class="card-header py-2">
                        <small class="fw-bold"><i class="fas fa-sign-in-alt"></i> Accedi</small>
                    </div>
                    <div class="card-body p-2">
                        <form id="sidebarLoginForm" method="POST" action="{{ route('login.post') }}">
                            @csrf
                            <div class="mb-2">
                                <input type="text" name="username" class="form-control form-control-sm @error('username') is-invalid @enderror"
                                       placeholder="Nickname" value="{{ old('username') }}" required style="border: 1px solid #CC9900;">
                                @error('username')
                                    <div class="invalid-feedback" style="font-size:0.75em">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <input type="password" name="password" class="form-control form-control-sm @error('password') is-invalid @enderror"
                                       placeholder="Password" required style="border: 1px solid #CC9900;">
                                @error('password')
                                    <div class="invalid-feedback" style="font-size:0.75em">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="position-relative">
                                <div id="sidebarLoginVoteTooltip"
                                     style="display:none; position:absolute; top:100%; left:0; right:0; margin-top:8px; z-index:2000; background:#fff; color:#000; border:2px solid #000; border-radius:6px; padding:8px 10px; font-size:0.75rem; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.25);">
                                    <span style="color:#000;">😍</span>
                                    Novità! Ti ricordiamo che per gli eventi già trascorsi puoi valutare la tua esperienza scegliendo una faccina da "Pessimo" a "Ottimo".
                                </div>
                                <button type="submit" id="sidebarLoginBtn" class="btn btn-primary btn-sm w-100" style="border: 2px solid #CC9900;"><i class="fas fa-sign-in-alt"></i> Accedi</button>
                            </div>
                        </form>
                        <script>
                            (function () {
                                var form = document.getElementById('sidebarLoginForm');
                                var tooltip = document.getElementById('sidebarLoginVoteTooltip');
                                var btn = document.getElementById('sidebarLoginBtn');
                                if (!form || !tooltip || !btn) return;
                                var confirmed = false;
                                form.addEventListener('submit', function (e) {
                                    if (confirmed) return;
                                    e.preventDefault();
                                    e.stopPropagation();
                                    tooltip.style.display = 'block';
                                    btn.disabled = true;
                                    tooltip.scrollIntoView({behavior: 'smooth', block: 'center'});
                                    setTimeout(function () {
                                        confirmed = true;
                                        tooltip.style.display = 'none';
                                        form.submit();
                                    }, 7000);
                                    return false;
                                });
                            })();
                        </script>
                        <hr class="my-2">
                        <div class="d-grid gap-1">
                            <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-sm" style="border: 1px solid #CC9900;">
                                <i class="fas fa-user-plus"></i> Registrati
                            </a>
                            <a href="{{ route('password.request') }}" class="small text-center text-muted d-block mt-1">
                                Password dimenticata?
                            </a>
                        </div>
                    </div>
                </div>
            @endguest

            {{-- Utenti online: visibile per tutti, elenco cliccabile verso il profilo --}}
            <div class="card card-sidebar sidebar-box--green mb-3">
                <div class="card-header py-2">
                    <small class="fw-bold">
                        <i class="fas fa-circle text-success me-1"></i> Utenti online
                    </small>
                </div>
                <div class="card-body p-2" style="max-height: 220px; overflow-y: auto;">
                    @php
                        try {
                            $idleMinutes = (int) config('session.online_timeout', 3);
                            if ($idleMinutes < 1) { $idleMinutes = 3; }
                            $onlineCutoff = time() - ($idleMinutes * 60);

                            $onlineUsers = \Illuminate\Support\Facades\DB::table('utentionline')
                                ->join('utente', 'utentionline.id_utente', '=', 'utente.userID')
                                ->where('utente.abilitato', 1)
                                ->where('utentionline.time', '>=', $onlineCutoff)
                                ->groupBy('utentionline.id_utente', 'utente.username')
                                ->selectRaw('utentionline.id_utente as userID, utente.username as nickname, MAX(utentionline.time) as last_time')
                                ->orderByDesc('last_time')
                                ->limit(30)
                                ->get();
                        } catch (\Illuminate\Database\QueryException $e) {
                            $onlineUsers = collect();
                        }
                    @endphp
                    @if($onlineUsers->isEmpty())
                        <small class="text-muted">Nessun utente online in questo momento.</small>
                    @else
                        <ul class="list-unstyled mb-0">
                            @foreach($onlineUsers as $online)
                                <li class="d-flex align-items-center online-user-row py-1">
                                    <span class="online-dot"></span>
                                    <span class="small">
                                        <a href="{{ route('profile.show', $online->userID) }}" class="text-decoration-none">
                                            {{ $online->nickname }}
                                        </a>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- Contenuti opzionali sotto "Utenti online" (es. homepage stats) --}}
            @yield('sidebar_after_online')

            @guest
                {{-- Box per utenti non registrati: invito a partecipare scrivendo una email --}}
                <div class="card card-sidebar mb-3" style="border: 1px solid #198754;">
                    <div class="card-header py-2" role="button" data-bs-toggle="collapse" data-bs-target="#guestParticipateBox" aria-expanded="false" aria-controls="guestParticipateBox" style="cursor:pointer;">
                        <small class="fw-bold">
                            <i class="fas fa-envelope text-danger me-1"></i> Non sei registrato e vuoi partecipare?
                        </small>
                    </div>
                    <div class="collapse" id="guestParticipateBox">
                        <div class="card-body p-2">
                            <div class="small mb-2">
                                Se sei interessato a partecipare a un evento in programma per cominciare a conoscerci e per provare, scrivici una email e ti daremo tutte le informazioni.
                            </div>
                            <a href="mailto:excursio@libero.it?subject=Richiesta%20partecipazione%20evento&body=Ciao,%20vorrei%20partecipare%20all%27evento%3A%20%5BTITOLO%20EVENTO%5D%0AMio%20nome%3A%20%5BNOME%5D%0AMio%20numero%20di%20telefono%3A%20%5BTELEFONO%5D%0A%0AGrazie."
                               class="btn btn-danger btn-sm w-100">
                                <i class="fas fa-paper-plane me-1"></i> Scrivici per partecipare
                            </a>
                        </div>
                    </div>
                </div>
            @endguest

            @auth
                @php
                    try {
                        $mySubscribedEvents = auth()->user()->participatingEvents()->limit(30)->get();
                    } catch (\Throwable $e) {
                        $mySubscribedEvents = collect();
                    }
                @endphp
                <div class="card card-sidebar sidebar-box--green mb-3">
                    <div class="card-header py-2">
                        <small class="fw-bold">
                            <i class="fas fa-calendar-check text-info me-1"></i> Eventi attivi
                        </small>
                    </div>
                    <div class="card-body p-2" style="max-height: 220px; overflow-y: auto;">
                        @if($mySubscribedEvents->isEmpty())
                            <small class="text-muted">Non risulti iscritto a eventi futuri pubblicati.</small>
                        @else
                            <ul class="list-unstyled mb-0 sidebar-active-events-list">
                                @foreach($mySubscribedEvents as $subEvent)
                                    <li class="small sidebar-active-events-list__item">
                                        <a href="{{ route('events.show', $subEvent) }}" class="text-decoration-none sidebar-active-events-list__link">
                                            {{ $subEvent->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                {{-- Contenuti opzionali sotto "Eventi attivi" (per singole pagine) --}}
                @yield('sidebar_after_my_events')
            @endauth
        </div>
        @endif

        {{-- Contenuto principale --}}
        <div class="{{ $hideSidebar ? 'col-12' : 'col-md-10' }}">

            @if(!View::hasSection('suppress_global_flash'))
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            @endif

            @yield('content')

        </div>
    </div>
</div>

<footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container">
        <p>&copy; 2026 Excursio. Tutti i diritti riservati.</p>
        <div class="small mt-2">
            <a href="{{ url('/cookie-policy') }}" class="text-white-50 text-decoration-none">Cookie Policy</a>
            <span class="mx-2 text-white-50">|</span>
            <a href="{{ url('/privacy-policy') }}" class="text-white-50 text-decoration-none">Privacy Policy</a>
            @if(!View::hasSection('suppress_cookie_modal'))
                <span class="mx-2 text-white-50">|</span>
                <button type="button" class="btn btn-link p-0 align-baseline text-white-50 text-decoration-none"
                        onclick="(function(){var el=document.getElementById('cookieConsentModal'); if(el && typeof bootstrap!=='undefined'){bootstrap.Modal.getOrCreateInstance(el,{backdrop:'static',keyboard:false}).show();}})();">
                    Preferenze cookie
                </button>
            @endif
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Calcola l'altezza reale dell'header sticky e adegua il top della sidebar
    function updateStickyHeaderVar() {
        var header = document.querySelector('.site-nav-sticky');
        if (!header) return;
        var h = header.getBoundingClientRect().height;
        if (h && h > 0) {
            document.documentElement.style.setProperty('--site-sticky-header-h', Math.ceil(h) + 'px');
        }
    }
    updateStickyHeaderVar();
    window.addEventListener('resize', function () {
        updateStickyHeaderVar();
    });

    // "Chi siamo e cosa facciamo": mostra/nasconde la sezione al click
    var btnChiSiamo = document.getElementById('btn-chi-siamo');
    if (btnChiSiamo) {
        btnChiSiamo.addEventListener('click', function(e) {
            var box = document.getElementById('descrizione-eventi');
            if (box) {
                e.preventDefault();
                if (box.style.display === 'none' || box.style.display === '') {
                    box.style.display = 'block';
                    box.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    box.style.display = 'none';
                }
            }
        });
    }

    // Chiudi il menu hamburger dopo click su link (non sui toggle dei dropdown)
    var navCollapse = document.getElementById('navbarNav');
    if (navCollapse) {
        navCollapse.querySelectorAll('a.nav-link:not(.dropdown-toggle), a.dropdown-item, button.nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                var bsCollapse = bootstrap.Collapse.getInstance(navCollapse);
                if (bsCollapse) {
                    bsCollapse.hide();
                }
            });
        });
    }

    // Aggiungi pulsante mostra/nascondi per tutti i campi password (esclusi quelli già gestiti in pagina)
    document.querySelectorAll('input[type="password"]:not([data-password-managed])').forEach(function (input) {
        // Wrappa l'input in un input-group se non lo è già
        var parent = input.parentElement;
        if (!parent.classList.contains('input-group')) {
            var wrapper = document.createElement('div');
            wrapper.className = 'input-group';
            parent.insertBefore(wrapper, input);
            wrapper.appendChild(input);
            // Sposta il feedback di validazione fuori dal wrapper
            var feedback = parent.querySelector('.invalid-feedback');
            if (feedback) parent.appendChild(feedback);
        }

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-outline-secondary' + (input.classList.contains('form-control-sm') ? ' btn-sm' : '');
        btn.innerHTML = '<i class="fas fa-eye"></i>';
        btn.title = 'Mostra/nascondi password';
        function togglePasswordField() {
            var isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            btn.innerHTML = isHidden ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
        }
        btn.addEventListener('click', togglePasswordField);
        input.addEventListener('click', function () {
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
            }
        });

        input.parentElement.appendChild(btn);
    });

    // Tooltip "spiegazione breve" (solo PC/mouse).
    // Genera automaticamente una breve descrizione per tutti i pulsanti/link
    // (utente e admin) senza alterare il testo visibile.
    (function setupHoverHints() {
        try {
            var isDesktopHover = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;
            if (!isDesktopHover) return;

            function hasClassLike(el, needle) {
                if (!el || !el.classList) return false;
                for (var i = 0; i < el.classList.length; i++) {
                    if (String(el.classList[i]).indexOf(needle) !== -1) return true;
                }
                return false;
            }

            function firstIconClass(el) {
                try {
                    var icon = el.querySelector('i.fas,i.far,i.fab,i.fa');
                    if (!icon) return '';
                    for (var i = 0; i < icon.classList.length; i++) {
                        var c = icon.classList[i];
                        if (c && c.indexOf('fa-') === 0) return c;
                    }
                } catch (e) {}
                return '';
            }

            function getFormMethodHint(el) {
                var form = el.closest ? el.closest('form') : null;
                if (!form) return '';
                // Laravel spoofed method via _method
                var method = (form.getAttribute('method') || 'GET').toUpperCase();
                try {
                    var spoof = form.querySelector('input[name="_method"]');
                    if (spoof && spoof.value) method = String(spoof.value).toUpperCase();
                } catch (e) {}

                if (method === 'DELETE') return 'Elimina';
                if (method === 'PUT' || method === 'PATCH') return 'Aggiorna';
                if (method === 'POST') return '';
                return '';
            }

            function guessHint(el) {
                if (!el || !(el instanceof HTMLElement)) return '';

                // Se ha un title breve (tipico: "Vedi", "Modifica", "Elimina"), trasformalo in una frase più chiara.
                var t = (el.getAttribute('title') || '').trim();
                if (t) {
                    var tl = t.toLowerCase();
                    if (tl === 'vedi' || tl === 'visualizza') return 'Apri i dettagli';
                    if (tl === 'modifica') return 'Apri la modifica';
                    if (tl === 'elimina' || tl === 'cancella') return 'Elimina questo elemento';
                    if (tl === 'attiva') return 'Attiva questo elemento';
                    if (tl === 'disattiva') return 'Disattiva questo elemento';
                    if (tl === 'salva') return 'Salva le modifiche';
                    if (tl === 'invia') return 'Invia';
                    if (tl === 'cerca') return 'Esegui la ricerca';
                }

                // Priorità: aria-label esplicito (spesso già è la funzione)
                var aria = (el.getAttribute('aria-label') || '').trim();
                if (aria) return aria;

                // Se è un link
                var href = (el.getAttribute('href') || '').trim();
                if (href) {
                    if (/\/edit\b/i.test(href)) return 'Modifica';
                    if (/\/create\b/i.test(href)) return 'Crea nuovo';
                    if (/\/print\b/i.test(href)) return 'Stampa';
                    if (/\/login\b/i.test(href)) return 'Accedi';
                    if (/\/register\b/i.test(href)) return 'Registrati';
                    if (/\/logout\b/i.test(href)) return 'Esci';
                    if (/chat/i.test(href)) return 'Apri la chat';
                    if (/mercatino/i.test(href)) return 'Apri il mercatino';
                    if (/albums?-foto|photo-albums/i.test(href)) return 'Apri album foto eventi';
                    return 'Apri';
                }

                // Se è un bottone in una form, prova a capire il metodo
                var methodHint = getFormMethodHint(el);
                if (methodHint) return methodHint;

                // Heuristica per icone FontAwesome
                var icon = firstIconClass(el);
                if (icon) {
                    if (icon === 'fa-eye') return 'Visualizza';
                    if (icon === 'fa-edit' || icon === 'fa-pen') return 'Modifica';
                    if (icon === 'fa-trash') return 'Elimina';
                    if (icon === 'fa-save') return 'Salva';
                    if (icon === 'fa-paper-plane') return 'Invia';
                    if (icon === 'fa-plus') return 'Crea nuovo';
                    if (icon === 'fa-home') return 'Vai alla Home';
                    if (icon === 'fa-search') return 'Cerca';
                    if (icon === 'fa-times') return 'Chiudi / Annulla';
                    if (icon === 'fa-reply') return 'Rispondi';
                    if (icon === 'fa-upload') return 'Carica';
                    if (icon === 'fa-download') return 'Scarica';
                    if (icon === 'fa-play') return 'Attiva';
                    if (icon === 'fa-pause') return 'Disattiva';
                    if (icon === 'fa-toggle-on') return 'Attiva';
                    if (icon === 'fa-toggle-off') return 'Disattiva';
                    if (icon === 'fa-map') return 'Apri la mappa';
                    if (icon === 'fa-compress-alt') return 'Riduci';
                }

                // Classi Bootstrap comuni
                if (hasClassLike(el, 'btn-danger')) return 'Azione pericolosa';
                if (hasClassLike(el, 'btn-success')) return 'Conferma';

                return '';
            }

            // Bootstrap Tooltip (se disponibile).
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                var selectors = [
                    '.btn',
                    'a.nav-link',
                    'a.dropdown-item',
                    'button.nav-link',
                    '.badge',
                    '[role="status"]',
                    '[role="note"]',
                    '[data-hint]'
                ].join(',');

                document.querySelectorAll(selectors).forEach(function (el) {
                    if (!el || !(el instanceof HTMLElement)) return;
                    var hint = (el.getAttribute('data-hint') || '').trim();
                    if (!hint) {
                        hint = guessHint(el);
                        if (hint) el.setAttribute('data-hint', hint);
                    }
                    if (hint === '') return;

                    // Evita tooltip su elementi disabilitati o hidden
                    if (el.hasAttribute('disabled')) return;
                    if (el.getAttribute('aria-hidden') === 'true') return;

                    // Non sovrascrivere altri data-bs-toggle (dropdown/collapse/modal...).
                    // Bootstrap Tooltip può essere inizializzato anche senza data-bs-toggle="tooltip".
                    var existingBsToggle = (el.getAttribute('data-bs-toggle') || '').trim();
                    if (existingBsToggle !== '' && existingBsToggle !== 'tooltip') {
                        // Non tocchiamo l'attributo, ma possiamo comunque aggiungere il testo tooltip.
                        el.setAttribute('data-bs-title', hint);
                    } else {
                        el.setAttribute('data-bs-title', hint);
                    }
                    // Fallback: se non esiste title o è troppo generico, metti il title uguale all'hint,
                    // così almeno il tooltip nativo del browser mostra la spiegazione.
                    var currentTitle = (el.getAttribute('title') || '').trim();
                    if (currentTitle === '' || currentTitle.length <= 10) {
                        el.setAttribute('title', hint);
                    }

                    // Non duplicare tooltip se già creato
                    if (bootstrap.Tooltip.getInstance(el)) return;
                    bootstrap.Tooltip.getOrCreateInstance(el, {
                        trigger: 'hover focus',
                        container: 'body',
                        boundary: document.body
                    });
                });
            }
        } catch (e) {
            // no-op
        }
    })();
});
</script>

@stack('scripts')
@yield('scripts')

{{-- Banner cookie custom (solo se non hai ancora scelto) --}}
@php
    $cookieConsent = \App\Support\CookieConsent::read(request());
@endphp
@if(!View::hasSection('suppress_cookie_modal'))
    {{-- Il modale deve esistere SEMPRE per aprirlo da "Preferenze cookie". --}}
    {{-- Si mostra automaticamente solo quando serve (primo accesso / banner attivo). --}}
    <x-cookie-consent-modal :show="true" :autoShow="$cookieBannerShouldShow ?? true" :consent="$cookieConsent" />
@endif

{{-- Chatbot assistente Excursio --}}
<div id="excursio-chatbot">
    <button id="chatbot-toggle" title="Hai bisogno di aiuto?">
        <i class="fas fa-comment-dots"></i>
    </button>
    <div id="chatbot-window" style="display:none;">
        <div id="chatbot-header">
            <span><i class="fas fa-robot me-1"></i> Assistente Excursio</span>
            <button id="chatbot-close" title="Chiudi"><i class="fas fa-times"></i></button>
        </div>
        <div id="chatbot-messages">
            <div class="chatbot-msg chatbot-msg--bot">
                Ciao! Sono l'assistente di Excursio. Chiedimi su eventi, mercatino, registrazione, profilo, chat e altro — oppure scegli una domanda qui sotto.
            </div>
            <div id="chatbot-suggestions"></div>
        </div>
        <div id="chatbot-input-area">
            <input type="text" id="chatbot-input" placeholder="Scrivi la tua domanda..." autocomplete="off">
            <button id="chatbot-send" title="Invia"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>
<style>
    #excursio-chatbot {
        position: fixed;
        bottom: 1.25rem;
        right: 1.25rem;
        z-index: 9999;
        font-family: inherit;
    }
    #chatbot-toggle {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #198754;
        color: #fff;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        box-shadow: 0 4px 16px rgba(0,0,0,0.25);
        transition: transform 0.2s, background 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    #chatbot-toggle:hover {
        transform: scale(1.1);
        background: #146c43;
    }
    #chatbot-window {
        position: absolute;
        bottom: 70px;
        right: 0;
        width: 360px;
        max-width: calc(100vw - 2rem);
        height: 480px;
        max-height: calc(100vh - 6rem);
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 2px solid #198754;
    }
    #chatbot-header {
        background: #198754;
        color: #fff;
        padding: 0.7rem 1rem;
        font-weight: 600;
        font-size: 0.95rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }
    #chatbot-close {
        background: none;
        border: none;
        color: #fff;
        font-size: 1.1rem;
        cursor: pointer;
        padding: 0 0.25rem;
        opacity: 0.8;
    }
    #chatbot-close:hover { opacity: 1; }
    #chatbot-messages {
        flex: 1;
        overflow-y: auto;
        padding: 0.75rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .chatbot-msg {
        padding: 0.55rem 0.8rem;
        border-radius: 10px;
        font-size: 0.88rem;
        line-height: 1.45;
        max-width: 88%;
        word-wrap: break-word;
    }
    .chatbot-msg--bot {
        background: #e8f5e9;
        color: #1b5e20;
        align-self: flex-start;
        border-bottom-left-radius: 3px;
    }
    .chatbot-msg--user {
        background: #e3f2fd;
        color: #0d47a1;
        align-self: flex-end;
        border-bottom-right-radius: 3px;
    }
    #chatbot-suggestions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-top: 0.25rem;
    }
    .chatbot-suggestion-btn {
        background: #f0f0f0;
        border: 1px solid #ccc;
        border-radius: 16px;
        padding: 0.3rem 0.7rem;
        font-size: 0.78rem;
        cursor: pointer;
        color: #333;
        transition: background 0.15s;
    }
    .chatbot-suggestion-btn:hover {
        background: #198754;
        color: #fff;
        border-color: #198754;
    }
    .chatbot-suggestion-btn--more {
        background: #e8f5e9;
        border-color: #198754;
        color: #198754;
        font-weight: 600;
    }
    .chatbot-related-label {
        font-size: 0.75rem;
        color: #666;
        width: 100%;
        margin-bottom: 0.15rem;
        font-style: italic;
    }
    #chatbot-input-area {
        display: flex;
        border-top: 1px solid #e0e0e0;
        flex-shrink: 0;
        padding: 0.5rem;
        gap: 0.4rem;
        background: #fafafa;
    }
    #chatbot-input {
        flex: 1;
        border: 1px solid #ccc;
        border-radius: 20px;
        padding: 0.45rem 0.85rem;
        font-size: 0.88rem;
        outline: none;
    }
    #chatbot-input:focus { border-color: #198754; }
    #chatbot-send {
        background: #198754;
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        flex-shrink: 0;
    }
    #chatbot-send:hover { background: #146c43; }
    @media (max-width: 480px) {
        #chatbot-window {
            width: calc(100vw - 1.5rem);
            height: calc(100vh - 5rem);
            bottom: 65px;
            right: -0.5rem;
            border-radius: 10px;
        }
    }
</style>
<script>
(function() {
    var faqData = @json(json_decode(file_get_contents(public_path('js/chatbot-faq.json'))));
    var chatWindow = document.getElementById('chatbot-window');
    var chatToggle = document.getElementById('chatbot-toggle');
    var chatClose = document.getElementById('chatbot-close');
    var chatMessages = document.getElementById('chatbot-messages');
    var chatInput = document.getElementById('chatbot-input');
    var chatSend = document.getElementById('chatbot-send');
    var suggestions = document.getElementById('chatbot-suggestions');

    var suggestionsPage = 0;
    var suggestionsPerPage = 5;

    function showSuggestions(excludeQuestion) {
        if (!suggestions) return;
        suggestions.innerHTML = '';
        var filtered = faqData.filter(function(item) {
            return item.question !== excludeQuestion;
        });
        var start = suggestionsPage * suggestionsPerPage;
        var page = filtered.slice(start, start + suggestionsPerPage);
        if (page.length === 0) {
            suggestionsPage = 0;
            page = filtered.slice(0, suggestionsPerPage);
        }
        page.forEach(function(item) {
            var btn = document.createElement('button');
            btn.className = 'chatbot-suggestion-btn';
            btn.textContent = item.question;
            btn.addEventListener('click', function() {
                addMessage(item.question, 'user');
                addMessage(item.answer, 'bot');
                showRelatedSuggestions(item);
                scrollDown();
            });
            suggestions.appendChild(btn);
        });
        if (filtered.length > suggestionsPerPage) {
            var moreBtn = document.createElement('button');
            moreBtn.className = 'chatbot-suggestion-btn chatbot-suggestion-btn--more';
            moreBtn.textContent = 'Altre domande...';
            moreBtn.addEventListener('click', function() {
                suggestionsPage++;
                showSuggestions(excludeQuestion);
                scrollDown();
            });
            suggestions.appendChild(moreBtn);
        }
    }

    function showRelatedSuggestions(answeredItem) {
        if (!suggestions) return;
        suggestions.innerHTML = '';
        var related = findRelated(answeredItem, 3);
        if (related.length === 0) {
            showSuggestions(answeredItem.question);
            return;
        }
        var label = document.createElement('div');
        label.className = 'chatbot-related-label';
        label.textContent = 'Potrebbe interessarti anche:';
        suggestions.appendChild(label);
        related.forEach(function(item) {
            var btn = document.createElement('button');
            btn.className = 'chatbot-suggestion-btn';
            btn.textContent = item.question;
            btn.addEventListener('click', function() {
                addMessage(item.question, 'user');
                addMessage(item.answer, 'bot');
                showRelatedSuggestions(item);
                scrollDown();
            });
            suggestions.appendChild(btn);
        });
        var allBtn = document.createElement('button');
        allBtn.className = 'chatbot-suggestion-btn chatbot-suggestion-btn--more';
        allBtn.textContent = 'Tutte le domande...';
        allBtn.addEventListener('click', function() {
            suggestionsPage = 0;
            showSuggestions();
            scrollDown();
        });
        suggestions.appendChild(allBtn);
    }

    function findRelated(item, count) {
        var itemKw = new Set(item.keywords.map(function(k) { return normalize(k); }));
        var scored = [];
        faqData.forEach(function(other) {
            if (other.question === item.question) return;
            var overlap = 0;
            other.keywords.forEach(function(k) {
                var nk = normalize(k);
                itemKw.forEach(function(ik) {
                    if (nk.indexOf(ik) !== -1 || ik.indexOf(nk) !== -1) overlap++;
                });
            });
            if (overlap > 0) scored.push({ item: other, score: overlap });
        });
        scored.sort(function(a, b) { return b.score - a.score; });
        return scored.slice(0, count).map(function(s) { return s.item; });
    }

    function normalize(str) {
        return (str || '').toLowerCase()
            .replace(/[àáâãäå]/g, 'a')
            .replace(/[èéêë]/g, 'e')
            .replace(/[ìíîï]/g, 'i')
            .replace(/[òóôõö]/g, 'o')
            .replace(/[ùúûü]/g, 'u')
            .replace(/[^a-z0-9\s]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function findBestAnswer(query) {
        var q = normalize(query);
        if (!q) return null;

        var words = q.split(' ').filter(function(w) { return w.length > 1; });
        var best = null;
        var bestScore = 0;

        faqData.forEach(function(item) {
            var score = 0;
            var nQuestion = normalize(item.question).replace(/\?/g, '');
            var kwJoined = normalize(item.keywords.join(' ') + ' ' + nQuestion + ' ' + (item.answer || ''));

            if (nQuestion.indexOf(q) !== -1 || q.indexOf(nQuestion) !== -1) {
                score += 20;
            }
            if (kwJoined.indexOf(q) !== -1) {
                score += 12;
            }

            words.forEach(function(w) {
                if (w.length < 3) return;
                if (nQuestion.indexOf(w) !== -1) score += 5;
                if (kwJoined.indexOf(w) !== -1) score += 2;
                item.keywords.forEach(function(kw) {
                    var nkw = normalize(kw);
                    if (nkw === w) score += 10;
                    else if (nkw.indexOf(w) !== -1 || w.indexOf(nkw) !== -1) score += 6;
                });
            });

            if (score > bestScore) {
                bestScore = score;
                best = item;
            }
        });

        return bestScore >= 2 ? best : null;
    }

    function findFaqByTopic(topic) {
        var t = normalize(topic);
        if (!t) return [];
        return faqData.filter(function(item) {
            var blob = normalize(item.keywords.join(' ') + ' ' + item.question);
            return blob.indexOf(t) !== -1;
        });
    }

    function addMessage(text, type) {
        var div = document.createElement('div');
        div.className = 'chatbot-msg chatbot-msg--' + type;
        div.textContent = text;
        chatMessages.appendChild(div);
    }

    function scrollDown() {
        setTimeout(function() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 50);
    }

    function handleUserInput() {
        var text = (chatInput.value || '').trim();
        if (!text) return;
        chatInput.value = '';

        addMessage(text, 'user');

        var match = findBestAnswer(text);
        if (match) {
            addMessage(match.answer, 'bot');
            showRelatedSuggestions(match);
        } else {
            var qn = normalize(text);
            var topicHints = [];
            if (qn.indexOf('mercatino') !== -1 || qn.indexOf('vetrina') !== -1 || qn.indexOf('annuncio') !== -1
                || qn.indexOf('vend') !== -1 || qn.indexOf('compr') !== -1 || qn.indexOf('bozza') !== -1
                || qn.indexOf('scambio') !== -1 || qn.indexOf('inserzion') !== -1) {
                topicHints = findFaqByTopic('mercatino');
            } else if (qn.indexOf('evento') !== -1 || qn.indexOf('iscriv') !== -1) {
                topicHints = findFaqByTopic('evento');
            } else if (qn.indexOf('chat') !== -1 || qn.indexOf('salottino') !== -1 || qn.indexOf('messagg') !== -1
                || qn.indexOf('rispond') !== -1 || qn.indexOf('emoji') !== -1 || qn.indexOf('menzion') !== -1) {
                topicHints = findFaqByTopic('chat');
            }
            if (topicHints.length > 0) {
                addMessage('Non ho una risposta esatta alla tua domanda, ma forse ti interessa una di queste sullo stesso argomento:', 'bot');
                if (!suggestions) return;
                suggestions.innerHTML = '';
                topicHints.slice(0, 6).forEach(function(item) {
                    var btn = document.createElement('button');
                    btn.className = 'chatbot-suggestion-btn';
                    btn.textContent = item.question;
                    btn.addEventListener('click', function() {
                        addMessage(item.question, 'user');
                        addMessage(item.answer, 'bot');
                        showRelatedSuggestions(item);
                        scrollDown();
                    });
                    suggestions.appendChild(btn);
                });
            } else {
                addMessage('Mi dispiace, non ho trovato una risposta precisa. Prova con parole diverse oppure sfoglia le domande qui sotto. Per questioni specifiche puoi sempre contattare gli organizzatori.', 'bot');
                suggestionsPage = 0;
                showSuggestions();
            }
        }
        scrollDown();
    }

    if (chatToggle) {
        chatToggle.addEventListener('click', function() {
            chatWindow.style.display = chatWindow.style.display === 'none' ? 'flex' : 'none';
            if (chatWindow.style.display === 'flex') {
                chatInput.focus();
                scrollDown();
            }
        });
    }
    if (chatClose) {
        chatClose.addEventListener('click', function() {
            chatWindow.style.display = 'none';
        });
    }
    if (chatSend) {
        chatSend.addEventListener('click', handleUserInput);
    }
    if (chatInput) {
        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleUserInput();
            }
        });
    }

    showSuggestions();
})();
</script>

@auth
    @include('partials.event-details-greeting')
@endauth

</body>
</html>
