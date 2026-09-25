{{-- Box "Ultimi accessi": stesso stile di "Utenti online", ma con gli ultimi utenti
     che hanno effettuato il login (non necessariamente ancora online ora). --}}
<div class="card card-sidebar sidebar-box--green mb-3">
    <div class="card-header py-2">
        <small class="fw-bold">
            Ultimi accessi
        </small>
    </div>
    <div class="card-body p-2" style="height: 88px; min-height: 0; overflow-y: auto;">
        @php
            try {
                $recentAccessUsers = \Illuminate\Support\Facades\DB::table('user_login_events')
                    ->join('utente', 'user_login_events.user_id', '=', 'utente.userID')
                    ->where('utente.abilitato', 1)
                    ->groupBy('user_login_events.user_id', 'utente.username')
                    ->selectRaw('user_login_events.user_id as userID, utente.username as nickname, MAX(user_login_events.logged_in_at) as last_login')
                    ->orderByDesc('last_login')
                    ->limit(30)
                    ->get();
            } catch (\Illuminate\Database\QueryException $e) {
                $recentAccessUsers = collect();
            }
        @endphp
        @if($recentAccessUsers->isEmpty())
            <small class="text-muted">Nessun accesso registrato.</small>
        @else
            <ul class="list-unstyled mb-0">
                @foreach($recentAccessUsers as $recent)
                    <li class="d-flex align-items-center online-user-row py-1">
                        <span class="online-dot"></span>
                        <span class="small text-truncate d-inline-block" style="max-width: 100%;">
                            <a href="{{ route('profile.show', $recent->userID) }}" class="text-decoration-none">
                                {{ $recent->nickname }}
                            </a>
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
