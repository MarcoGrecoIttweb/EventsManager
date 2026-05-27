<?php

namespace App\Console\Commands;

use App\Support\EventoTableSchema;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncExcursioDatabase extends Command
{
    protected $signature = 'excursio:sync-database';

    protected $description = 'Aggiorna il database Excursio (migrazioni + colonne mancanti su tabella evento)';

    public function handle(): int
    {
        $this->info('Sincronizzazione database Excursio...');

        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output());
            if ($output !== '') {
                $this->line($output);
            }
        } catch (\Throwable $e) {
            $this->warn('Migrazione standard non completata: ' . $e->getMessage());
        }

        EventoTableSchema::resetCache();

        if (! Schema::hasTable('evento')) {
            $this->error('Tabella evento non trovata.');

            return self::FAILURE;
        }

        $this->ensureGuestColumns();
        $this->ensureGreetingBoxColumns();

        EventoTableSchema::resetCache();

        $missing = EventoTableSchema::missingOptionalColumns();
        if ($missing !== []) {
            $this->error('Colonne ancora mancanti: ' . implode(', ', $missing));
            $this->line('Prova manualmente: database/sql/add_greeting_box_columns_manual.sql');

            return self::FAILURE;
        }

        $this->info('Database aggiornato. Ora puoi salvare gli eventi online.');

        return self::SUCCESS;
    }

    private function ensureGuestColumns(): void
    {
        if (EventoTableSchema::hasColumn('allow_guests')) {
            return;
        }

        $this->info('Aggiunta colonne ospiti (allow_guests)...');

        DB::statement("SET SESSION sql_mode = ''");
        DB::statement("ALTER TABLE `evento` ADD `allow_guests` TINYINT(1) NOT NULL DEFAULT 1 AFTER `pubblicato`, ADD `max_guests_per_user` INT NOT NULL DEFAULT 10 AFTER `allow_guests`");
        DB::statement("SET SESSION sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
    }

    private function ensureGreetingBoxColumns(): void
    {
        if (EventoTableSchema::hasColumn('greeting_box_enabled')) {
            return;
        }

        $this->info('Aggiunta colonne box benvenuto...');

        DB::statement("SET SESSION sql_mode = ''");
        DB::statement("ALTER TABLE `evento`
            ADD `greeting_box_enabled` TINYINT(1) NOT NULL DEFAULT 0,
            ADD `greeting_box_duration` SMALLINT UNSIGNED NOT NULL DEFAULT 5,
            ADD `greeting_box_message` TEXT NULL,
            ADD `greeting_box_max_width` SMALLINT UNSIGNED NOT NULL DEFAULT 420,
            ADD `greeting_box_border_color` VARCHAR(7) NOT NULL DEFAULT '#198754',
            ADD `greeting_box_bg_color` VARCHAR(7) NOT NULL DEFAULT '#ffffff'");
        DB::statement("SET SESSION sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");

        $migrationName = '2026_05_23_120000_add_greeting_box_columns_to_evento_table';
        if (! DB::table('migrations')->where('migration', $migrationName)->exists()) {
            $batch = (int) DB::table('migrations')->max('batch') + 1;
            DB::table('migrations')->insert([
                'migration' => $migrationName,
                'batch' => $batch,
            ]);
        }
    }
}
