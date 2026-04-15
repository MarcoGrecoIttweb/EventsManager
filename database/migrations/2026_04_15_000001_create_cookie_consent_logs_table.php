<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cookie_consent_logs', function (Blueprint $table) {
            $table->id();
            // NB: la tabella utenti legacy (`utente.userID`) potrebbe non essere bigint unsigned.
            // Per evitare problemi FK in ambienti diversi, salviamo solo l'ID e un indice.
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->json('consent');
            $table->string('ip', 64)->nullable();
            $table->string('user_agent', 1000)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cookie_consent_logs');
    }
};

