<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * JSON su partecipa.ospiti_inseriti_il: elenco di { "at": "Y-m-d H:i:s", "nome": "..." }.
 * Compatibile con array di sole stringhe (data) legacy.
 */
class OspitiGuestStore
{
    /**
     * @return list<array{at: string, nome: string}>
     */
    public static function decode($raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $out = [];
        foreach (array_values($decoded) as $item) {
            if (is_string($item)) {
                $out[] = ['at' => $item, 'nome' => ''];

                continue;
            }
            if (is_array($item)) {
                $at = '';
                if (!empty($item['at'])) {
                    $at = (string) $item['at'];
                } elseif (!empty($item['data'])) {
                    $at = (string) $item['data'];
                }
                $nome = isset($item['nome']) ? trim((string) $item['nome']) : '';
                $out[] = ['at' => $at, 'nome' => $nome];
            }
        }

        return $out;
    }

    /**
     * @param  list<array{at: string, nome: string}>  $entries
     */
    public static function encode(array $entries): ?string
    {
        if ($entries === []) {
            return null;
        }

        return json_encode(array_values($entries), JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param  list<array{at: string, nome: string}>  $entries
     * @return list<array{at: string, nome: string}>
     */
    public static function normalizeToCount(array $entries, int $guestCount, object $pivot): array
    {
        $fallback = now()->format('Y-m-d H:i:s');
        if (!empty($pivot->data_iscrizione)) {
            try {
                $fallback = Carbon::parse($pivot->data_iscrizione)->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
                //
            }
        }

        if (count($entries) > $guestCount) {
            $entries = array_slice($entries, 0, $guestCount);
        }
        while (count($entries) < $guestCount) {
            $entries[] = ['at' => $fallback, 'nome' => ''];
        }

        return array_values($entries);
    }
}
