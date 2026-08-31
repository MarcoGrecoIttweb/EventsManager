<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_login_events', function (Blueprint $table) {
            // Aggiornato ad ogni richiesta autenticata (heartbeat): permette di calcolare
            // il tempo di permanenza anche per le sessioni ormai concluse.
            $table->dateTime('last_seen_at')->nullable()->after('logged_in_at');
            // Impostato esplicitamente al logout, se avvenuto tramite il pulsante "Esci".
            $table->dateTime('ended_at')->nullable()->after('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_login_events', function (Blueprint $table) {
            $table->dropColumn(['last_seen_at', 'ended_at']);
        });
    }
};
