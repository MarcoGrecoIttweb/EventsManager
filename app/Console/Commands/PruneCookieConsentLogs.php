<?php

namespace App\Console\Commands;

use App\Models\CookieConsentLog;
use Illuminate\Console\Command;

class PruneCookieConsentLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cookie-consent-logs:prune {--days=365 : Conserva solo i log più recenti di questo numero di giorni}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina i log di consenso cookie più vecchi del periodo indicato (minimizzazione dati)';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $deleted = CookieConsentLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Eliminati {$deleted} log di consenso cookie precedenti al " . $cutoff->format('d/m/Y') . '.');

        return self::SUCCESS;
    }
}
