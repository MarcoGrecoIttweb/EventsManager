{{-- Box "Utenti online": riutilizzato sia in sidebar sia accanto alla galleria (guest, smartphone) --}}
<div class="card card-sidebar sidebar-box--green mb-3">
    <div class="card-header py-2">
        <small class="fw-bold">
            Utenti online
        </small>
    </div>
    <div class="card-body p-2" style="height: 88px; min-height: 0; overflow-y: auto;">
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
