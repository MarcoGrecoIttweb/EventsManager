<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('utente', 'password_laravel')) {
            DB::statement("ALTER TABLE `utente` ADD `password_laravel` VARCHAR(255) NULL DEFAULT NULL AFTER `password`");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('utente', 'password_laravel')) {
            DB::statement("ALTER TABLE `utente` DROP COLUMN `password_laravel`");
        }
    }
};
