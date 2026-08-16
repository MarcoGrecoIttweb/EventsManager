<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('utente', 'nascondi_eventi_partecipati')) {
            DB::statement("ALTER TABLE `utente` ADD `nascondi_eventi_partecipati` TINYINT(1) NOT NULL DEFAULT 0");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('utente', 'nascondi_eventi_partecipati')) {
            DB::statement("ALTER TABLE `utente` DROP COLUMN `nascondi_eventi_partecipati`");
        }
    }
};
