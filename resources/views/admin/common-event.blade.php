@extends('layouts.app')

@section('title', 'Evento in comune')

@section('content')
    <style>
        /* Box Username 1/2: sfondo verde chiaro + bordo verde */
        .common-event-username-input {
            background-color: #e7f5ea;
        }
        .common-event-username-input:not(.is-invalid) {
            border-color: #198754 !important;
            border-width: 2px !important;
        }
        .common-event-username-input:not(.is-invalid):focus {
            border-color: #198754 !important;
            box-shadow: 0 0 0 .2rem rgba(25, 135, 84, 0.25);
        }
    </style>
    <div class="container-fluid py-3" style="max-width: 58rem;">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <h1 class="h3 mb-0">
                <i class="fas fa-random text-primary"></i> Trova evento in comune
            </h1>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>

        <div class="card shadow-sm" style="border: 2px solid #198754;">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.common-event.search') }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-12 col-md-5">
                        <label class="form-label fw-semibold">Username 1</label>
                        <input type="text"
                               name="username1"
                               class="form-control common-event-username-input @error('username1') is-invalid @enderror"
                               value="{{ old('username1', $username1 ?? '') }}"
                               required
                               autocomplete="off"
                               list="common-event-users-datalist">
                        @error('username1')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-5">
                        <label class="form-label fw-semibold">Username 2</label>
                        <input type="text"
                               name="username2"
                               class="form-control common-event-username-input @error('username2') is-invalid @enderror"
                               value="{{ old('username2', $username2 ?? '') }}"
                               required
                               autocomplete="off"
                               list="common-event-users-datalist">
                        @error('username2')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-2 d-grid">
                        <button class="btn btn-primary">
                            <i class="fas fa-search"></i> Cerca
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-3">
            @if(($username1 ?? '') !== '' || ($username2 ?? '') !== '')
                @if(!$user1 || !$user2)
                    <div class="alert alert-warning border shadow-sm">
                        <div class="fw-semibold mb-1">Utenti non trovati</div>
                        <div class="small">
                            @if(!$user1)
                                - Username 1: <span class="fw-semibold">{{ $username1 }}</span> non esiste.<br>
                            @endif
                            @if(!$user2)
                                - Username 2: <span class="fw-semibold">{{ $username2 }}</span> non esiste.
                            @endif
                        </div>
                    </div>
                @else
                    @if($commonEvents->count() === 0)
                        <div class="alert alert-light border shadow-sm" style="border: 2px solid #198754;">
                            Nessun evento in comune trovato per <span class="fw-semibold">{{ $username1 }}</span> e <span class="fw-semibold">{{ $username2 }}</span>.
                        </div>
                    @else
                        <div class="card shadow-sm" style="border: 2px solid #198754;">
                            <div class="card-header bg-light text-dark fw-semibold">
                                Eventi in comune (max 20)
                            </div>
                            <div class="list-group list-group-flush">
                                @foreach($commonEvents as $event)
                                    <div class="list-group-item" style="border-color: #198754; background-color: #f1f3f5;">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                            <div class="min-w-0">
                                                <div class="fw-semibold text-truncate">
                                                    <a href="{{ route('events.show', $event) }}" class="text-decoration-none" style="color: #198754;">
                                                        {{ $event->title }}
                                                    </a>
                                                </div>
                                                <div class="small" style="color: #6c757d;">
                                                    <i class="fas fa-calendar"></i>
                                                    {{ $event->italian_event_date ?? ($event->date ? $event->date->format('d/m/Y H:i') : '') }}
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a class="btn btn-success btn-sm" href="{{ route('events.show', $event) }}" target="_blank" rel="noopener">
                                                    Apri evento
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            @endif
        </div>

        <datalist id="common-event-users-datalist"></datalist>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const datalist = document.getElementById('common-event-users-datalist');
            const u1 = document.querySelector('input[name="username1"]');
            const u2 = document.querySelector('input[name="username2"]');
            const inputs = [u1, u2].filter(Boolean);

            const url = @json(route('admin.common-event.users-search'));
            let timer = null;

            function clearDatalist() {
                while (datalist.firstChild) datalist.removeChild(datalist.firstChild);
            }

            function fillDatalist(results) {
                clearDatalist();
                for (const item of results) {
                    const opt = document.createElement('option');
                    // Il valore immesso nel campo deve rimanere solo l'username.
                    opt.value = item.username;
                    opt.label = item.label || item.username;
                    datalist.appendChild(opt);
                }
            }

            async function searchUsers(q) {
                const res = await fetch(url + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                fillDatalist(data.results || []);
            }

            function onInput(e) {
                const q = e.target.value ? e.target.value.trim() : '';
                clearDatalist();

                if (!q || q.length < 2) return;

                if (timer) clearTimeout(timer);
                timer = setTimeout(() => {
                    searchUsers(q).catch(() => clearDatalist());
                }, 250);
            }

            inputs.forEach(el => el.addEventListener('input', onInput));
        });
    </script>
@endsection

