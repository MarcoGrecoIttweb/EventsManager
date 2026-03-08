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
            border-bottom: 1px solid #dee2e6;
        }
        .site-header-logo {
            max-height: 100px;
            width: auto;
        }
        .sidebar-left {
            position: sticky;
            top: 1rem;
            align-self: flex-start;
            max-height: calc(100vh - 80px);
            overflow-y: auto;
        }
        .card-sidebar {
            font-size: 0.85rem;
            border-radius: 8px;
        }
        .card-sidebar .card-header {
            background: #f8f9fa;
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
    </style>
</head>
<body>
<div class="site-header text-center">
    <a href="{{ route('home') }}">
        <img src="{{ asset('upload_immagini/excursio.png') }}" alt="Excursio" class="site-header-logo">
    </a>
</div>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}">Home</a>
                </li>
                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('profile.show', auth()->user()) }}">Profilo</a>
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
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.events.index') }}">Gestione Eventi</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">Gestione Utenti</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.groups.index') }}">Gestione Gruppi</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('admin.newsletter.create') }}">Newsletter</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.newsletter.stats') }}">Statistiche Newsletter</a></li>
                            </ul>
                        </li>
                    @endif
                @endauth
            </ul>
            <ul class="navbar-nav">
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">Registrati</a>
                    </li>
                @else
                    @auth
                        <li class="nav-item d-flex align-items-center me-2">
                            <span class="navbar-text text-white-50 small">
                                Benvenuto <strong class="text-white">{{ auth()->user()->username }}</strong>
                            </span>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-link btn btn-link">Logout</button>
                            </form>
                        </li>
                    @endauth
                @endguest
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid mt-4">
    @php $hideSidebar = View::hasSection('no_sidebar'); @endphp
    <div class="row">

        {{-- Sidebar sinistra (nascosta nelle pagine auth) --}}
        @if(!$hideSidebar)
        <div class="col-md-2 sidebar-left">
            @auth
                {{-- Utenti online --}}
                @php
                    $onlineUsers = \Illuminate\Support\Facades\DB::table('utentionline as o')
                        ->join('utente as u', 'u.userID', '=', 'o.id_utente')
                        ->whereNotNull('o.id_utente')
                        ->where('o.time', '>', time() - 900)
                        ->select('u.userID', 'u.username')
                        ->orderBy('u.username')
                        ->get();
                @endphp
                <div class="card card-sidebar mb-3">
                    <div class="card-header py-2">
                        <small class="fw-bold">
                            <i class="fas fa-circle text-success" style="font-size:0.6em"></i>
                            Online ({{ $onlineUsers->count() }})
                        </small>
                    </div>
                    <div class="card-body p-2">
                        @forelse($onlineUsers as $ou)
                            <a href="{{ route('profile.show', $ou->userID) }}"
                               class="d-flex align-items-center text-decoration-none text-dark mb-1 online-user-row"
                               title="{{ $ou->username }}">
                                <span class="online-dot"></span>
                                <span class="small text-truncate">{{ $ou->username }}</span>
                            </a>
                        @empty
                            <span class="text-muted small">Nessuno online</span>
                        @endforelse
                    </div>
                </div>
            @else
                {{-- Form login per ospiti --}}
                <div class="card card-sidebar mb-3">
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
            @endauth
        </div>
        @endif

        {{-- Contenuto principale --}}
        <div class="{{ $hideSidebar ? 'col-12' : 'col-md-10' }}">

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

            @yield('content')

        </div>
    </div>
</div>

<footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container">
        <p>&copy; 2024 Excursio. Tutti i diritti riservati.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')
@yield('scripts')
</body>
</html>
