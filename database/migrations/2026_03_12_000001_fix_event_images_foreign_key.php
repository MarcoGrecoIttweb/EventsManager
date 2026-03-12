<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Rimuove il FK errato che punta a events.id (tabella Laravel standard
        // che non esiste nel DB legacy — la tabella si chiama 'evento' con PK IDevento)
        try {
            DB::statement('ALTER TABLE `event_images` DROP FOREIGN KEY `event_images_event_id_foreign`');
        } catch (\Exception $e) {
            // Il vincolo potrebbe non esistere in locale, ignora
        }
    }

    public function down(): void
    {
        // Non ripristiniamo il FK errato
    }
};
