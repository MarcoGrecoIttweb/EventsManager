<?php

namespace App\Console\Commands;

use App\Models\UserLoginEvent;
use Illuminate\Console\Command;

class CleanupHomeFromPageVisits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'page-visits:cleanup-home';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rimuove "Home" dalle pagine visitate gia\' salvate prima che venisse esclusa dal conteggio (una tantum)';

    public function handle(): int
    {
        // Nota: sistemiamo solo l'elenco (quello visibile in tabella). Il numero
        // page_views_count non viene ricalcolato, perché non sappiamo quante delle
        // visite già conteggiate in passato fossero effettivamente sulla Home
        // (prima della modifica ogni apertura, Home inclusa, contava come 1).
        $rows = UserLoginEvent::query()
            ->where('pages_visited', 'like', '%Home%')
            ->get(['id', 'pages_visited']);

        $updated = 0;

        foreach ($rows as $row) {
            $labels = array_filter(array_map('trim', explode(',', (string) $row->pages_visited)));
            if (! in_array('Home', $labels, true)) {
                continue;
            }

            $labels = array_values(array_diff($labels, ['Home']));

            $row->pages_visited = $labels === [] ? null : implode(', ', $labels);
            $row->save();
            $updated++;
        }

        $this->info("Righe aggiornate: {$updated}.");

        return self::SUCCESS;
    }
}
