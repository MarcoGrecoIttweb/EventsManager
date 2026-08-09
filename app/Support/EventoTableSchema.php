<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

class EventoTableSchema
{
    private static ?array $columns = null;

    /**
     * @return list<string>
     */
    public static function columns(): array
    {
        if (self::$columns !== null) {
            return self::$columns;
        }

        try {
            self::$columns = Schema::hasTable('evento')
                ? Schema::getColumnListing('evento')
                : [];
        } catch (\Throwable $e) {
            self::$columns = [];
        }

        return self::$columns;
    }

    public static function hasColumn(string $column): bool
    {
        return in_array($column, self::columns(), true);
    }

    /**
     * Toglie dal payload colonne assenti (es. migrazioni non ancora eseguite online).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function filter(array $data): array
    {
        if ($data === []) {
            return [];
        }

        $allowed = array_flip(self::columns());

        return array_intersect_key($data, $allowed);
    }

    /**
     * @return list<string>
     */
    public static function missingOptionalColumns(): array
    {
        $optional = [
            'allow_guests',
            'max_guests_per_user',
            'greeting_box_enabled',
            'greeting_box_duration',
            'greeting_box_message',
            'greeting_box_max_width',
            'greeting_box_border_color',
            'greeting_box_bg_color',
        ];

        return array_values(array_filter(
            $optional,
            fn (string $column): bool => ! self::hasColumn($column)
        ));
    }

    public static function resetCache(): void
    {
        self::$columns = null;
    }

    /**
     * Messaggio utente per un fallimento di salvataggio dell'evento, distinguendo
     * la causa reale (es. testo troppo lungo per la colonna) da una colonna
     * mancante (unico caso in cui "excursio:sync-database" è la soluzione).
     */
    public static function saveErrorMessage(\Throwable $e): string
    {
        $message = $e->getMessage();

        if (preg_match("/Data too long for column '([^']+)'/i", $message, $matches)) {
            $labels = [
                'descrizione' => 'Descrizione',
                'incipit' => 'Anteprima (incipit)',
                'greeting_box_message' => 'Messaggio box di benvenuto',
            ];
            $field = $labels[$matches[1]] ?? $matches[1];

            return "Salvataggio non riuscito: il testo nel campo \"{$field}\" è troppo lungo. Accorcialo e riprova.";
        }

        if (stripos($message, 'Unknown column') !== false || stripos($message, "doesn't exist") !== false) {
            return 'Salvataggio non riuscito. Se il problema persiste, sul server esegui: php artisan excursio:sync-database';
        }

        return 'Salvataggio non riuscito. Riprova o contatta l\'assistenza tecnica.';
    }
}
