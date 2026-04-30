<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('evento')) {
            return;
        }

        // La tabella legacy può avere default "0000-00-00" su colonne datetime (es. dataevento):
        // con modalità strict, MySQL può bloccare ALTER TABLE anche se non tocchiamo quella colonna.
        // Disattiviamo temporaneamente sql_mode per questa sessione.
        DB::statement("SET SESSION sql_mode = ''");

        // Allinea la dimensione delle colonne legacy ai nuovi limiti UI/validazione.
        // Usiamo SQL diretto per evitare dipendenza da doctrine/dbal.
        DB::statement("ALTER TABLE `evento` MODIFY `citta` VARCHAR(35) NOT NULL");
        DB::statement("ALTER TABLE `evento` MODIFY `via` VARCHAR(35) NOT NULL");
        DB::statement("ALTER TABLE `evento` MODIFY `dove` VARCHAR(35) NOT NULL");
        DB::statement("ALTER TABLE `evento` MODIFY `civico` VARCHAR(10) NOT NULL DEFAULT ''");

        // Ripristina strict mode standard
        DB::statement("SET SESSION sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
    }

    public function down(): void
    {
        // Non facciamo shrink automatico: potrebbe troncare dati esistenti.
    }
};

