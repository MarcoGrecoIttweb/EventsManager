<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

class SiteSettings
{
    private const CACHE_PREFIX = 'site_setting:';

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever(self::cacheKey($key), function () use ($key, $default) {
            try {
                $row = SiteSetting::query()->where('key', $key)->first();
                if (! $row) {
                    return $default;
                }
                return $row->value;
            } catch (QueryException $e) {
                // Es. tabella non ancora migrata in ambienti fresh.
                return $default;
            }
        });
    }

    public static function getBool(string $key, bool $default = true): bool
    {
        $value = self::get($key, $default ? '1' : '0');
        if (is_bool($value)) {
            return $value;
        }
        $str = strtolower(trim((string) $value));
        return in_array($str, ['1', 'true', 'yes', 'on'], true);
    }

    public static function set(string $key, mixed $value): void
    {
        SiteSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
        );
        Cache::forget(self::cacheKey($key));
    }

    public static function toggleBool(string $key, bool $default = true): bool
    {
        $new = ! self::getBool($key, $default);
        self::set($key, $new);
        return $new;
    }

    private static function cacheKey(string $key): string
    {
        return self::CACHE_PREFIX . $key;
    }
}

