<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        /* Menu sempre hamburger: voci in colonna sotto la riga toggler+brand (anche su tablet/desktop). */
        .excursio-navbar > .container-fluid {
            flex-wrap: wrap;
            justify-content: flex-start;
        }
        .excursio-navbar .navbar-collapse {
            flex-basis: 100%;
            width: 100% !important;
        }
        .excursio-navbar__user-nav {
            margin-top: 0.35rem;
            padding-top: 0.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.25);
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
<nav class="navbar navbar-dark bg-dark excursio-navbar py-1">
    <div class="container-fluid px-3 px-xl-4 d-flex align-items-center flex-wrap">
        <div class="d-flex align-items-center flex-shrink-0">
            <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Apri il menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a href="{{ route('home') }}" class="navbar-brand mb-0 text-white fw-semibold ms-1 py-1" title="Home">
                <i class="fas fa-home me-1"></i><span class="d-none d-sm-inline">Home</span>
            </a>
        </div>
        <div class="collapse navbar-collapse flex-column align-items-stretch" id="navbarNav">
            <ul class="navbar-nav excursio-navbar__main-nav mb-2 w-100">
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt"></i> Accedi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">
                            <i class="fas fa-user-plus"></i> Registrati
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('chat.index') }}">
                            <i class="fas fa-comments"></i> Salottino delle chat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('mercatino.index') }}">
                            <i class="fas fa-store"></i> Mercatino
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}#descrizione-eventi" title="Chi siamo e cosa facciamo">
                            <i class="fas fa-info-circle"></i> Chi siamo e cosa facciamo
                        </a>
                    </li>
                @else
                <li class="nav-item d-flex align-items-center me-1 me-md-2">
                    <span class="navbar-text text-white-50 small">
                        <i class="fas fa-smile-beam me-1"></i>
                        Benvenuto <strong class="text-white">{{ auth()->user()->username }}</strong>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('chat.index') }}">
                        <i class="fas fa-comments"></i> Salottino delle chat
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('mercatino.index') }}">
                        <i class="fas fa-store"></i> Mercatino
                    </a>
                </li>
                @auth
                    @if(auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.users.index') }}">
                                <i class="fas fa-users-cog"></i> Gestione utenti
                            </a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('profile.show', auth()->user()) }}">
                            <i class="fas fa-user-circle"></i> Profilo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('events.past') }}">
                            <i class="fas fa-history"></i> Eventi Passati
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('friends.index') }}">
                            <i class="fas fa-user-friends"></i> Amici
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('users.search') }}">
                            <i class="fas fa-search"></i> Cerca Utenti
                        </a>
                    </li>
                    @if(auth()->user()->canManageEvents())
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="manageDropdown" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-calendar-plus"></i> I Miei Eventi
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="manageDropdown">
                                <li><a class="dropdown-item" href="{{ route('manage.events.index') }}">I Miei Eventi</a></li>
                                <li><a class="dropdown-item" href="{{ route('manage.events.create') }}">Crea Evento</a></li>
                            </ul>
                        </li>
                    @endif
                    @if(auth()->user()->isAdmin())
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
                                <li><a class="dropdown-item" href="{{ route('admin.events.index') }}">Gestione Eventi</a></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center justify-content-between gap-2 {{ ($adminPendingUsersCount ?? 0) > 0 ? 'fw-semibold text-dark' : '' }}"
                                       href="{{ route('admin.users.index') }}">
                                        <span>Gestione Utenti</span>
                                        @if(($adminPendingUsersCount ?? 0) > 0)
                                            <span class="badge bg-warning text-dark">{{ $adminPendingUsersCount }} in attesa</span>
                                        @endif
                                    </a>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('admin.users.gallery') }}">Admin. Immagini utenti</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.groups.index') }}">Gestione Gruppi</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('admin.newsletter.create') }}">Newsletter</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.newsletter.stats') }}">Statistiche Newsletter</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.mail-test') }}">Test invio email</a></li>
                            </ul>
                        </li>
                    @endif
                @endauth
                @endguest
            </ul>
            @auth
            <ul class="navbar-nav excursio-navbar__user-nav w-100">
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
                <div class="card card-sidebar mb-3 d-none d-md-block">
                    <div class="card-header py-2">
                        <small class="fw-bold"><i class="fas fa-sign-in-alt"></i> Accedi</small>
                    </div>
                    <div class="card-body p-2">
                        <form method="POST" action="{{ route('login.post') }}">
                            @csrf
                            <div class="mb-2">
                                <input type="text" name="username" class="form-control form-control-sm @error('username') is-invalid @enderror"
                                       placeholder="Nickname" value="{{ old('username') }}" required>
                                @error('username')
                                    <div class="invalid-feedback" style="font-size:0.75em">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <input type="password" name="password" class="form-control form-control-sm @error('password') is-invalid @enderror"
                                       placeholder="Password" required>
                                @error('password')
                                    <div class="invalid-feedback" style="font-size:0.75em">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100">Entra</button>
                        </form>
                        <hr class="my-2">
                        <div class="d-grid gap-1">
                            <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-user-plus"></i> Registrati
                            </a>
                            <a href="{{ route('password.request') }}" class="small text-center text-muted d-block mt-1">
                                Password dimenticata?
                            </a>
                        </div>
                    </div>
                </div>
            @endguest

            {{-- Utenti online: visibile per tutti, solo elenco non cliccabile --}}
            <div class="card card-sidebar sidebar-box--green mb-3">
                <div class="card-header py-2">
                    <small class="fw-bold">
                        <i class="fas fa-circle text-success me-1"></i> Utenti online
                    </small>
                </div>
                <div class="card-body p-2" style="max-height: 220px; overflow-y: auto;">
                    @php
                        try {
                            $onlineUsers = \Illuminate\Support\Facades\DB::table('utentionline')
                                ->join('utente', 'utentionline.id_utente', '=', 'utente.userID')
                                ->where('utente.abilitato', 1)
                                ->orderByDesc('utentionline.time')
                                ->limit(30)
                                ->get(['utente.username as nickname']);
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
                                        {{ $online->nickname }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

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
                            <ul class="list-unstyled mb-0">
                                @foreach($mySubscribedEvents as $subEvent)
                                    <li class="small py-1">
                                        <a href="{{ route('events.show', $subEvent) }}" class="text-decoration-none">
                                            {{ $subEvent->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
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

    // Aggiungi pulsante mostra/nascondi per tutti i campi password
    document.querySelectorAll('input[type="password"]').forEach(function (input) {
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
        btn.className = 'btn btn-outline-secondary';
        btn.innerHTML = '<i class="fas fa-eye"></i>';
        btn.title = 'Mostra/nascondi password';
        btn.addEventListener('click', function () {
            var isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            btn.innerHTML = isHidden ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
        });

        input.parentElement.appendChild(btn);
    });
});
</script>

@stack('scripts')
@yield('scripts')
</body>
</html>
