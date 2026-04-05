<?php

namespace App\Support;

/**
 * Compatibilità senza estensione mbstring completa: Laravel Str::limit() usa
 * mb_strimwidth(), assente nel polyfill Symfony su PHP 8+ quando mbstring non c'è.
 */
final class StrLimit
{
    public static function limit($value, int $limit = 100, string $end = '...'): string
    {
        $value = (string) $value;
        if ($limit <= 0) {
            return $end;
        }

        if (\function_exists('mb_strwidth') && \function_exists('mb_strimwidth')) {
            if (\mb_strwidth($value, 'UTF-8') <= $limit) {
                return $value;
            }

            return \rtrim(\mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
        }

        if (! \preg_match('//u', $value)) {
            return \strlen($value) <= $limit ? $value : \substr($value, 0, $limit) . $end;
        }

        \preg_match_all('/./us', $value, $m);
        $chars = $m[0] ?? [];
        if (\count($chars) <= $limit) {
            return $value;
        }

        return \rtrim(\implode('', \array_slice($chars, 0, $limit))) . $end;
    }
}
