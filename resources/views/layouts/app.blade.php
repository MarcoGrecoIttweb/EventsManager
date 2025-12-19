<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventSite - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS Personalizzato -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-absolute">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">EventSite</a>
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
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('admin.newsletter.create') }}">Newsletter</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.newsletter.stats') }}">Statistiche Newsletter</a></li>
                            </ul>
                        </li>
                    @endif
                @endauth
            </ul>
            <ul class="navbar-nav">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" href="{{ route('profile.show', Auth::user()) }}">
                                @if(Auth::user()->photo)
                                    <img src="{{ Storage::disk('public')->url(Auth::user()->photo) }}"
                                         alt="{{ Auth::user()->name }}"
                                         class="rounded-circle me-2"
                                         style="width: 30px; height: 30px; object-fit: cover;">
                                @endif
                                <span>{{ Auth::user()->nickname ?: Auth::user()->name }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="nav-link btn btn-link">Logout</button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Registrati</a>
                        </li>
                    @endauth
            </ul>
        </div>
    </div>
</nav>
@php
    $hideHeroRoutes = [
        'admin/newsletter/create'
    ];

    $showHero = true;
    foreach ($hideHeroRoutes as $route) {
        if (request()->is($route)) {
            $showHero = false;
            break;
        }
    }
@endphp

<div class="@if($showHero) hero-container @else container mt-4 @endif">
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
        {{-- Sezione Hero --}}
        @if($showHero ?? false)
            <div class="hero-container">
                <img src="{{ $heroImage ?? asset('storage/hero/homepage_hero.jpg') }}"
                     alt="{{ $heroTitle ?? 'Eventi' }}"
                     class="hero-image">

                <div class="hero-overlay">
                    <div class="hero-content">
                        <h1 class="hero-title">{{ $heroTitle ?? 'Eventi' }}</h1>
                        @if(!empty($heroSubtitle))
                            <p class="hero-subtitle">{{ $heroSubtitle }}</p>
                        @endif

                        {{-- Pulsanti contestuali --}}
                        @if(request()->is('events/past'))
                            <a href="{{ route('events.index') }}" class="btn btn-outline-light btn-lg mt-3">
                                <i class="fas fa-arrow-left me-2"></i>Torna agli Eventi Futuri
                            </a>
                        @endif

                        @if(request()->is('events') && auth()->check() && auth()->user()->isAdmin())
                            <a href="{{ route('admin.events.create') }}" class="btn btn-primary btn-lg mt-3 ms-2">
                                <i class="fas fa-plus me-2"></i>Crea Nuovo Evento
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Contenuto principale --}}
        <div class="@if($showHero ?? false) hero-container-content @else container mt-4 @endif">
            @yield('content')
        </div>
</div>

<footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container">
        <p>&copy; 2024 EventSite. Tutti i diritti riservati.</p>
    </div>
 </footer>

 {{-- Bootstrap JS (necessario per il menu hamburger su mobile) --}}
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

 {{-- Il tuo stile personalizzato --}}
 <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
@stack('scripts')
@yield('scripts')
</body>
</html>
