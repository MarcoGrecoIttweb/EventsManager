<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('evento', 'visite')) {
            return;
        }

        // `evento`.`dataevento` ha un default legacy "0000-00-00 00:00:00" che con
        // NO_ZERO_DATE in sql_mode blocca qualsiasi ALTER TABLE su questa tabella
        // (stesso problema gia' risolto per iscrizioni_chiuse).
        DB::statement("SET SESSION sql_mode = ''");

        DB::statement("ALTER TABLE `evento` ADD `visite` INT UNSIGNED NOT NULL DEFAULT 0");

        DB::statement("SET SESSION sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
    }

    public function down(): void
    {
        if (! Schema::hasColumn('evento', 'visite')) {
            return;
        }

        DB::statement("SET SESSION sql_mode = ''");

        DB::statement("ALTER TABLE `evento` DROP COLUMN `visite`");

        DB::statement("SET SESSION sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
    }
};
