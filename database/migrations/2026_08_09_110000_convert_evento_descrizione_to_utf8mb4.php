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

        // `descrizione` è rimasta in `latin1` (charset legacy della tabella), ma la
        // connessione dell'app usa `utf8mb4` (config/database.php). MySQL converte
        // ogni scrittura da utf8mb4 a latin1: i caratteri fuori dal repertorio
        // latin1 (emoji, virgolette tipografiche, trattini lunghi incollati da
        // Word/editor ricco...) fanno fallire il salvataggio con
        // "Incorrect string value" (1366). Portiamo la colonna a utf8mb4 per
        // allinearla alla connessione: MySQL riconverte i dati esistenti (validi
        // in latin1) senza perdita.
        DB::statement("SET SESSION sql_mode = ''");
        DB::statement("ALTER TABLE `evento` MODIFY `descrizione` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
        DB::statement("SET SESSION sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
    }

    public function down(): void
    {
        // Non riconvertiamo automaticamente a latin1: si perderebbero i caratteri
        // fuori repertorio eventualmente salvati nel frattempo.
    }
};
