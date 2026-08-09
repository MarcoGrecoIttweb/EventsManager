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

        // `descrizione` era TEXT (limite MySQL di 65.535 byte): con editor ricco/HTML
        // o immagini incollate come base64 è facile superarlo e ottenere
        // "Data too long for column 'descrizione'" in salvataggio.
        // MEDIUMTEXT porta il limite a 16 MB.
        DB::statement("SET SESSION sql_mode = ''");
        DB::statement("ALTER TABLE `evento` MODIFY `descrizione` MEDIUMTEXT NOT NULL");
        DB::statement("SET SESSION sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
    }

    public function down(): void
    {
        // Non facciamo shrink automatico: potrebbe troncare dati esistenti.
    }
};
