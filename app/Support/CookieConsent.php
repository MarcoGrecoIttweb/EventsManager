<?php

namespace App\Support;

use Illuminate\Http\Request;

final class CookieConsent
{
    public const COOKIE_NAME = 'excursio_cookie_consent';
    public const COOKIE_MAX_MINUTES = 60 * 24 * 180; // 180 giorni

    public const CAT_NECESSARY = 'necessary';
    public const CAT_THIRD_PARTY = 'third_party'; // legacy / compat
    public const CAT_EXTERNAL_MEDIA = 'external_media';

    public static function allowedCategories(): array
    {
        return [
            self::CAT_NECESSARY,
            self::CAT_THIRD_PARTY,
            self::CAT_EXTERNAL_MEDIA,
        ];
    }

    /**
     * Struttura:
     * [
     *   "status" => "accepted"|"rejected",
     *   "categories" => ["necessary","external_media",...],
     *   "updated_at" => "2026-04-15T12:34:56+00:00"
     * ]
     */
    public static function read(Request $request): array
    {
        $raw = (string) $request->cookie(self::COOKIE_NAME, '');
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    public static function isAccepted(Request $request): bool
    {
        $c = self::read($request);
        return ($c['status'] ?? null) === 'accepted';
    }

    public static function hasCategory(Request $request, string $category): bool
    {
        $c = self::read($request);
        $cats = $c['categories'] ?? [];
        if (!is_array($cats)) {
            return false;
        }
        return in_array($category, $cats, true);
    }

    public static function normalizeCategories(array $cats): array
    {
        $allowed = self::allowedCategories();
        $out = [];
        foreach ($cats as $c) {
            if (!is_string($c)) {
                continue;
            }
            $c = trim($c);
            if ($c === '') {
                continue;
            }
            if (!in_array($c, $allowed, true)) {
                continue;
            }
            $out[] = $c;
        }

        // Necessary is always included.
        if (!in_array(self::CAT_NECESSARY, $out, true)) {
            $out[] = self::CAT_NECESSARY;
        }

        // If user accepted external_media, also keep legacy third_party for backward compatibility with old gates.
        if (in_array(self::CAT_EXTERNAL_MEDIA, $out, true) && !in_array(self::CAT_THIRD_PARTY, $out, true)) {
            $out[] = self::CAT_THIRD_PARTY;
        }

        return array_values(array_unique($out));
    }

    public static function shouldShowBanner(Request $request): bool
    {
        $c = self::read($request);
        return !in_array(($c['status'] ?? null), ['accepted', 'rejected'], true);
    }
}

