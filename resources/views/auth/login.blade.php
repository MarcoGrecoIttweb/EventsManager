@extends('layouts.app')

@section('no_sidebar', '1')
@section('title', 'Login - Excursio')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Login</div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <p class="mb-0">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning">{{ session('warning') }}</div>
                    @endif
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form id="loginForm" method="POST" action="{{ route('login.post') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="username" class="form-label">Nickname</label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror"
                                   id="username" name="username" value="{{ old('username') }}" required>
                            @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" required>
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="position-relative">
                            <div id="loginVoteTooltip"
                                 style="display:none; position:absolute; bottom:100%; left:0; right:0; margin-bottom:8px; z-index:2000; background:#fff; color:#000; border:2px solid #000; border-radius:6px; padding:8px 12px; font-size:0.85rem; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.25);">
                                <span style="color:#000;">😍</span>
                                Gentile utente, ti ricordiamo che per gli eventi già iniziati puoi lasciare la tua votazione sull'esperienza vissuta, scegliendo una faccina da Pessimo a Ottimo. Accesso in corso...
                            </div>
                            <button type="submit" id="loginBtn" class="btn btn-primary w-100">Login</button>
                        </div>
                    </form>
                    <script>
                        (function () {
                            var form = document.getElementById('loginForm');
                            var tooltip = document.getElementById('loginVoteTooltip');
                            var btn = document.getElementById('loginBtn');
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

                    <div class="text-center mt-3">
                        <a href="{{ route('register') }}">Non hai un account? Registrati</a>
                    </div>
                    <div class="text-center mt-2">
                        <a href="{{ route('password.request') }}">Password dimenticata?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
