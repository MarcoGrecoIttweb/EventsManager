<?php

namespace App\Support;

/**
 * Sanificazione HTML per editor (CKEditor): tag consentiti, link e immagini da URL sicuri.
 */
class SafeRichText
{
    public const ALLOWED_TAGS = '<p><br><strong><b><em><i><u><a><ul><ol><li><code><pre><span><div><h1><h2><h3><h4><h5><h6><blockquote><table><thead><tbody><tr><th><td><img>';

    public static function sanitize(?string $html, bool $decodeEntities = false): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        if ($decodeEntities) {
            $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $clean = strip_tags($html, self::ALLOWED_TAGS);
        $clean = self::sanitizeImgTags($clean);
        $clean = self::sanitizeAnchorTags($clean);
        $clean = self::stripAttributesFromNonMediaTags($clean);

        return $clean;
    }

    private static function sanitizeImgTags(string $html): string
    {
        return preg_replace_callback('/<img\b[^>]*>/i', function (array $m): string {
            $tag = $m[0];
            $out = '';

            if (preg_match('/src\s*=\s*(["\'])(.*?)\1/i', $tag, $sm)) {
                $url = trim($sm[2]);
                if (! self::isSafeImgSrc($url)) {
                    return '';
                }
                $out .= ' src="' . e($url) . '"';
            } else {
                return '';
            }

            if (preg_match('/alt\s*=\s*(["\'])(.*?)\1/i', $tag, $am)) {
                $out .= ' alt="' . e($am[2]) . '"';
            }
            if (preg_match('/\bwidth\s*=\s*(["\']?)(\d+)\1/i', $tag, $wm)) {
                $out .= ' width="' . (int) $wm[2] . '"';
            }
            if (preg_match('/\bheight\s*=\s*(["\']?)(\d+)\1/i', $tag, $hm)) {
                $out .= ' height="' . (int) $hm[2] . '"';
            }

            return '<img' . $out . ' />';
        }, $html) ?? '';
    }

    private static function isSafeImgSrc(string $url): bool
    {
        $url = trim($url);
        if ($url === '') {
            return false;
        }
        $lower = strtolower($url);
        if (str_starts_with($lower, 'javascript:') || str_starts_with($lower, 'data:') || str_starts_with($lower, 'vbscript:')) {
            return false;
        }
        if (preg_match('#^(https?:)?//#i', $url)) {
            return true;
        }
        // Percorso assoluto sul sito (es. /upload_immagini/foo.jpg)
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return true;
        }

        return false;
    }

    private static function sanitizeAnchorTags(string $html): string
    {
        $result = preg_replace_callback('/<a\s+([^>]*)>/i', function (array $matches): string {
            $attributes = $matches[1];
            $href = '';
            if (preg_match('/href=(["\'])(.*?)\1/i', $attributes, $hrefMatches)) {
                $url = $hrefMatches[2];
                if (preg_match('/^(https?:\/\/|mailto:)/i', $url)) {
                    $href = ' href="' . e($url) . '"';
                }
            }

            return '<a' . $href . '>';
        }, $html);

        return $result ?? '';
    }

    /**
     * Rimuove style/onclick ecc. da p, span, … lasciando intatti img e a già ripuliti.
     */
    private static function stripAttributesFromNonMediaTags(string $html): string
    {
        $result = preg_replace_callback('/<([a-z][a-z0-9]*)\s+([^>]+)>/i', function (array $m): string {
            $tag = strtolower($m[1]);
            if ($tag === 'img' || $tag === 'a') {
                return $m[0];
            }

            return '<' . $tag . '>';
        }, $html);

        return $result ?? '';
    }
}
