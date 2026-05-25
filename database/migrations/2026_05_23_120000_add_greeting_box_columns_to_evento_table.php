<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('evento')) {
            return;
        }

        if (Schema::hasColumn('evento', 'greeting_box_enabled')) {
            return;
        }

        DB::statement("SET SESSION sql_mode = ''");

        DB::statement("ALTER TABLE `evento`
            ADD `greeting_box_enabled` TINYINT(1) NOT NULL DEFAULT 0,
            ADD `greeting_box_duration` SMALLINT UNSIGNED NOT NULL DEFAULT 5,
            ADD `greeting_box_message` TEXT NULL,
            ADD `greeting_box_max_width` SMALLINT UNSIGNED NOT NULL DEFAULT 420,
            ADD `greeting_box_border_color` VARCHAR(7) NOT NULL DEFAULT '#198754',
            ADD `greeting_box_bg_color` VARCHAR(7) NOT NULL DEFAULT '#ffffff'");

        DB::statement("SET SESSION sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('evento') || ! Schema::hasColumn('evento', 'greeting_box_enabled')) {
            return;
        }

        DB::statement("SET SESSION sql_mode = ''");

        DB::statement('ALTER TABLE `evento`
            DROP COLUMN `greeting_box_enabled`,
            DROP COLUMN `greeting_box_duration`,
            DROP COLUMN `greeting_box_message`,
            DROP COLUMN `greeting_box_max_width`,
            DROP COLUMN `greeting_box_border_color`,
            DROP COLUMN `greeting_box_bg_color`');

        DB::statement("SET SESSION sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
    }
};
