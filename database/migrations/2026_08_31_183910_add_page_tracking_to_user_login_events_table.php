<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_login_events', function (Blueprint $table) {
            // Numero di pagine viste durante la sessione (aggiornato ad ogni richiesta).
            $table->unsignedInteger('page_views_count')->default(0)->after('ended_at');
            // Elenco abbreviato e deduplicato delle pagine visitate, separate da virgola
            // (es. "Home, Evento, Chat"), troncato per non crescere all'infinito.
            $table->string('pages_visited', 500)->nullable()->after('page_views_count');
        });
    }

    public function down(): void
    {
        Schema::table('user_login_events', function (Blueprint $table) {
            $table->dropColumn(['page_views_count', 'pages_visited']);
        });
    }
};
