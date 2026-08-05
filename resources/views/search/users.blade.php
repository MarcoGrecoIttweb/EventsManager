@extends('layouts.app')

@section('title', 'Cerca utenti - Excursio')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="mb-0"><i class="fas fa-search"></i> Cerca utenti</h2>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-home"></i> Home
            </a>
            @if(strlen(trim($query)) >= 1)
                <a href="{{ route('users.search') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Torna a Cerca utenti
                </a>
            @endif
        </div>
    </div>

    <form method="GET" action="{{ route('users.search') }}" class="mb-4">
        <div class="input-group">
            <select name="type" class="form-select" style="max-width: 180px; border: 2px solid #198754;">
                <option value="username" {{ $type === 'username' ? 'selected' : '' }}>Username</option>
                <option value="nome" {{ $type === 'nome' ? 'selected' : '' }}>Nome</option>
            </select>
            <input type="text" name="q" class="form-control" placeholder="Cerca per username o nome…"
                   value="{{ $query }}" autofocus autocomplete="off" list="users-search-datalist"
                   style="border: 2px solid #198754;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Cerca
            </button>
        </div>
        <small class="text-muted">Inserisci almeno 2 caratteri per cercare per username o nome.</small>
    </form>

    <div class="mb-4">
        <button type="button" id="btn-mostra-tutti" class="btn btn-success">
            <i class="fas fa-users"></i> Mostra utenti attivi
        </button>
        <span id="all-users-count" class="text-muted small ms-2"></span>
    </div>

    <div id="all-users-container" class="d-none">
        <hr>
        <h5 class="mb-3"><i class="fas fa-list"></i> Utenti attivi con newsletter</h5>
        <div id="all-users-grid" class="row"></div>
    </div>

    <datalist id="users-search-datalist"></datalist>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.querySelector('input[name="q"]');
            const typeSelect = document.querySelector('select[name="type"]');
            const datalist = document.getElementById('users-search-datalist');
            if (!input || !datalist) return;

            const url = @json(route('users.autocomplete'));
            let timer = null;

            function clearList() {
                while (datalist.firstChild) datalist.removeChild(datalist.firstChild);
            }

            function fillList(results) {
                clearList();
                for (const item of results) {
                    const opt = document.createElement('option');
                    opt.value = item.username;
                    opt.label = item.label || item.username;
                    datalist.appendChild(opt);
                }
            }

            async function fetchUsers(q, type) {
                const res = await fetch(url + '?q=' + encodeURIComponent(q) + '&type=' + encodeURIComponent(type), { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                fillList(data.results || []);
            }

            function handleInput() {
                const q = (input.value || '').trim();
                const type = typeSelect ? typeSelect.value : 'username';
                clearList();
                if (q.length < 2) return;

                if (timer) clearTimeout(timer);
                timer = setTimeout(() => {
                    fetchUsers(q, type).catch(() => clearList());
                }, 250);
            }

            input.addEventListener('input', handleInput);
            if (typeSelect) {
                typeSelect.addEventListener('change', handleInput);
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('btn-mostra-tutti');
            const container = document.getElementById('all-users-container');
            const grid = document.getElementById('all-users-grid');
            const countEl = document.getElementById('all-users-count');
            if (!btn || !container || !grid) return;

            const url = @json(route('users.all'));
            let loaded = false;

            function buildCard(user) {
                const col = document.createElement('div');
                col.className = 'col-md-4 col-lg-3 mb-3';

                const card = document.createElement('div');
                card.className = 'card h-100 text-center p-3';

                let photoHtml;
                if (user.photo_url) {
                    photoHtml = '<img src="' + user.photo_url + '" class="rounded-circle mx-auto mb-2" style="width:70px;height:70px;object-fit:cover;" alt="' + user.username + '">';
                } else {
                    photoHtml = '<div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto mb-2" style="width:70px;height:70px;"><i class="fas fa-user fa-2x text-white"></i></div>';
                }

                card.innerHTML =
                    '<a href="' + user.profile_url + '" class="text-decoration-none text-dark">' +
                        photoHtml +
                        '<h6 class="mb-0 mt-1">' + user.username + '</h6>' +
                    '</a>';

                col.appendChild(card);
                return col;
            }

            btn.addEventListener('click', function () {
                if (loaded) {
                    container.classList.toggle('d-none');
                    return;
                }

                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Caricamento…';

                fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        const users = data.users || [];
                        grid.innerHTML = '';
                        users.forEach(function (user) {
                            grid.appendChild(buildCard(user));
                        });

                        countEl.textContent = users.length === 1
                            ? '1 utente'
                            : users.length + ' utenti';

                        container.classList.remove('d-none');
                        loaded = true;
                        btn.innerHTML = '<i class="fas fa-users"></i> Nascondi elenco';
                        btn.classList.remove('btn-success');
                        btn.classList.add('btn-outline-success');
                    })
                    .catch(function () {
                        alert('Errore durante il caricamento degli utenti. Riprova.');
                    })
                    .finally(function () {
                        btn.disabled = false;
                        if (loaded) {
                            btn.innerHTML = '<i class="fas fa-users"></i> Nascondi elenco';
                        } else {
                            btn.innerHTML = '<i class="fas fa-users"></i> Mostra utenti attivi';
                        }
                    });
            });
        });
    </script>

    @if(strlen(trim($query)) >= 2)
        @if($users->count() > 0)
            <p class="text-muted">{{ $users->count() }} risultat{{ $users->count() === 1 ? 'o' : 'i' }} per "<strong>{{ $query }}</strong>"</p>
            <div class="row">
                @foreach($users as $user)
                    <div class="col-md-4 col-lg-3 mb-3">
                        <div class="card h-100 text-center p-3">
                            @if($user->photo_url)
                                <img src="{{ $user->photo_url }}" class="rounded-circle mx-auto mb-2"
                                     style="width:70px;height:70px;object-fit:cover;" alt="{{ $user->username }}">
                            @else
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto mb-2"
                                     style="width:70px;height:70px;">
                                    <i class="fas fa-user fa-2x text-white"></i>
                                </div>
                            @endif
                            <h6 class="mb-1">{{ $user->nome }}</h6>
                            <small class="text-muted">{{ $user->username }}</small>
                            <div class="mt-2 d-flex gap-1 justify-content-center flex-wrap">
                                <a href="{{ route('profile.show', $user) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-user"></i> Profilo
                                </a>
                                @if($user->getKey() !== auth()->id())
                                    @if(auth()->user()->isFriendOf($user))
                                        <form action="{{ route('friends.remove', $user) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-user-minus"></i> Rimuovi
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('friends.add', $user) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-user-plus"></i> Aggiungi
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4 text-muted">
                <i class="fas fa-user-slash fa-2x mb-2"></i>
                <p>Nessun utente trovato per "<strong>{{ $query }}</strong>"</p>
            </div>
        @endif
    @endif
</div>
@endsection