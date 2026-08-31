<?php

namespace App\Support;

/**
 * Sanificazione HTML per editor (CKEditor): tag consentiti, link e immagini da URL sicuri.
 */
class SafeRichText
{
    public const ALLOWED_TAGS = '<p><br><strong><b><em><i><u><a><ul><ol><li><code><pre><span><div><h1><h2><h3><h4><h5><h6><blockquote><table><thead><tbody><tr><th><td><img><iframe><video><source>';

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
        $clean = self::sanitizeIframeTags($clean);
        $clean = self::sanitizeVideoTags($clean);
        $clean = self::sanitizeAnchorTags($clean);
        $clean = self::stripAttributesFromNonMediaTags($clean);

        return $clean;
    }

    /**
     * <iframe>: consente qualsiasi sorgente http/https (embed video da qualunque piattaforma),
     * ma blocca schemi javascript:/data:/vbscript: che non sono mai embed video legittimi.
     */
    private static function sanitizeIframeTags(string $html): string
    {
        return preg_replace_callback('/<iframe\b[^>]*>.*?<\/iframe>/is', function (array $m): string {
            $tag = $m[0];

            if (! preg_match('/src\s*=\s*(["\'])(.*?)\1/i', $tag, $sm)) {
                return '';
            }
            $url = trim($sm[2]);
            if (! self::isSafeMediaSrc($url)) {
                return '';
            }

            $out = ' src="' . e($url) . '"';
            if (preg_match('/\bwidth\s*=\s*(["\']?)(\d+)\1/i', $tag, $wm)) {
                $out .= ' width="' . (int) $wm[2] . '"';
            }
            if (preg_match('/\bheight\s*=\s*(["\']?)(\d+)\1/i', $tag, $hm)) {
                $out .= ' height="' . (int) $hm[2] . '"';
            }
            if (preg_match('/\ballowfullscreen\b/i', $tag)) {
                $out .= ' allowfullscreen';
            }
            if (preg_match('/\bframeborder\s*=\s*(["\']?)(\d+)\1/i', $tag, $fm)) {
                $out .= ' frameborder="' . (int) $fm[2] . '"';
            }
            if (preg_match('/\ballow\s*=\s*(["\'])(.*?)\1/i', $tag, $am)) {
                $out .= ' allow="' . e($am[2]) . '"';
            }
            if (preg_match('/\btitle\s*=\s*(["\'])(.*?)\1/i', $tag, $tm)) {
                $out .= ' title="' . e($tm[2]) . '"';
            }
            $out .= self::extractSafeStyleAttr($tag);

            return '<iframe' . $out . '></iframe>';
        }, $html) ?? '';
    }

    /**
     * <video>/<source>: consente file video con sorgente http/https o percorso locale del sito.
     */
    private static function sanitizeVideoTags(string $html): string
    {
        $html = preg_replace_callback('/<video\b([^>]*)>(.*?)<\/video>/is', function (array $m): string {
            $attrs = $m[1];
            $inner = $m[2];

            $out = '';
            if (preg_match('/\bwidth\s*=\s*(["\']?)(\d+)\1/i', $attrs, $wm)) {
                $out .= ' width="' . (int) $wm[2] . '"';
            }
            if (preg_match('/\bheight\s*=\s*(["\']?)(\d+)\1/i', $attrs, $hm)) {
                $out .= ' height="' . (int) $hm[2] . '"';
            }
            if (preg_match('/\bcontrols\b/i', $attrs)) {
                $out .= ' controls';
            }
            if (preg_match('/\bsrc\s*=\s*(["\'])(.*?)\1/i', $attrs, $sm) && self::isSafeMediaSrc(trim($sm[2]))) {
                $out .= ' src="' . e(trim($sm[2])) . '"';
            }
            $out .= self::extractSafeStyleAttr($attrs);

            $innerClean = self::sanitizeSourceTags($inner);

            return '<video' . $out . '>' . $innerClean . '</video>';
        }, $html) ?? '';

        // <source> fuori da un tag <video> non ha senso da solo: eliminali per sicurezza.
        return preg_replace('/<source\b[^>]*\/?>(?!.*<\/video>)/is', '', $html) ?? '';
    }

    private static function sanitizeSourceTags(string $html): string
    {
        return preg_replace_callback('/<source\b[^>]*\/?>/i', function (array $m): string {
            $tag = $m[0];
            if (! preg_match('/src\s*=\s*(["\'])(.*?)\1/i', $tag, $sm) || ! self::isSafeMediaSrc(trim($sm[2]))) {
                return '';
            }
            $out = ' src="' . e(trim($sm[2])) . '"';
            if (preg_match('/\btype\s*=\s*(["\'])(.*?)\1/i', $tag, $tm)) {
                $out .= ' type="' . e($tm[2]) . '"';
            }

            return '<source' . $out . '>';
        }, $html) ?? '';
    }

    /**
     * Src sicura per iframe/video: qualsiasi http(s) o percorso locale del sito, mai javascript:/data:/vbscript:.
     */
    private static function isSafeMediaSrc(string $url): bool
    {
        if ($url === '') {
            return false;
        }
        $lower = strtolower($url);
        if (str_starts_with($lower, 'javascript:') || str_starts_with($lower, 'data:') || str_starts_with($lower, 'vbscript:')) {
            return false;
        }

        return true;
    }

    private static function sanitizeImgTags(string $html): string
    {
        return preg_replace_callback('/<img\b[^>]*>/i', function (array $m): string {
            $tag = $m[0];
            $out = '';
            $widthFromStyle = null;
            $heightFromStyle = null;

            if (preg_match('/src\s*=\s*(["\'])(.*?)\1/i', $tag, $sm)) {
                $url = trim($sm[2]);
                if (! self::isSafeImgSrc($url)) {
                    return '';
                }
                // Normalizza percorsi relativi locali (es. "upload_immagini/x.jpg") in assoluti ("/upload_immagini/x.jpg")
                if (!preg_match('#^(https?:)?//#i', $url) && !str_starts_with($url, '/')) {
                    $url = '/' . ltrim($url, '/');
                }
                $out .= ' src="' . e($url) . '"';
            } else {
                return '';
            }

            if (preg_match('/alt\s*=\s*(["\'])(.*?)\1/i', $tag, $am)) {
                $out .= ' alt="' . e($am[2]) . '"';
            }

            // CKEditor spesso salva il ridimensionamento in style="width: ...; height: ...".
            // Convertiamo width/height px in attributi HTML così sopravvivono alla sanificazione.
            if (preg_match('/style\s*=\s*(["\'])(.*?)\1/i', $tag, $stm)) {
                $style = (string) ($stm[2] ?? '');
                if (preg_match('/\bwidth\s*:\s*(\d{1,4})(?:\.\d+)?\s*(?:px)?\b/i', $style, $sw)) {
                    $widthFromStyle = (int) $sw[1];
                }
                if (preg_match('/\bheight\s*:\s*(\d{1,4})(?:\.\d+)?\s*(?:px)?\b/i', $style, $sh)) {
                    $heightFromStyle = (int) $sh[1];
                }
            }

            if (preg_match('/\bwidth\s*=\s*(["\']?)(\d+)\1/i', $tag, $wm)) {
                $out .= ' width="' . (int) $wm[2] . '"';
            } elseif (is_int($widthFromStyle) && $widthFromStyle > 0) {
                $out .= ' width="' . $widthFromStyle . '"';
            }
            if (preg_match('/\bheight\s*=\s*(["\']?)(\d+)\1/i', $tag, $hm)) {
                $out .= ' height="' . (int) $hm[2] . '"';
            } elseif (is_int($heightFromStyle) && $heightFromStyle > 0) {
                $out .= ' height="' . $heightFromStyle . '"';
            }
            $out .= self::extractSafeStyleAttr($tag);

            return '<img' . $out . ' />';
        }, $html) ?? '';
    }

    /**
     * Estrae da un frammento di tag (con eventuale attributo style) la dichiarazione
     * style="..." già filtrata sulle proprietà sicure, pronta per essere concatenata
     * (con lo spazio iniziale incluso), oppure stringa vuota se non c'è nulla di sicuro.
     */
    private static function extractSafeStyleAttr(string $tagOrAttrs): string
    {
        if (! preg_match('/style\s*=\s*(["\'])(.*?)\1/is', $tagOrAttrs, $stm)) {
            return '';
        }
        $styleRaw = html_entity_decode($stm[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safeStyle = self::sanitizeCssDeclarationsForRichText($styleRaw);

        return $safeStyle !== '' ? ' style="' . e($safeStyle) . '"' : '';
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
        // Percorso relativo locale consentito (es. upload_immagini/foo.jpg)
        // Limitiamo a cartelle pubbliche note per evitare "path traversal" o riferimenti strani.
        if (preg_match('#^(upload_immagini|upload_avatar)/[a-z0-9][a-z0-9._-]*\.(?:jpg|jpeg|png|webp|gif)$#i', $url)) {
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
     * Rimuove onclick/class ecc. da p, span, … lasciando intatti img e a già ripuliti.
     * Conserva solo dichiarazioni CSS sicure su color / background-color (es. testo blu da CKEditor).
     */
    private static function stripAttributesFromNonMediaTags(string $html): string
    {
        $result = preg_replace_callback('/<([a-z][a-z0-9]*)\s+([^>]+)>/i', function (array $m): string {
            $tag = strtolower($m[1]);
            if ($tag === 'img' || $tag === 'a' || $tag === 'iframe' || $tag === 'video' || $tag === 'source') {
                return $m[0];
            }

            $attrs = $m[2];
            if (! preg_match('/\bstyle\s*=\s*(["\'])(.*?)\1/is', $attrs, $sm)) {
                return '<' . $tag . '>';
            }

            $styleRaw = html_entity_decode($sm[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $safeStyle = self::sanitizeCssDeclarationsForRichText($styleRaw);
            if ($safeStyle === '') {
                return '<' . $tag . '>';
            }

            return '<' . $tag . ' style="' . e($safeStyle) . '">';
        }, $html);

        return $result ?? '';
    }

    /**
     * Estrae solo le proprietà CSS ammesse (colore e allineamento) con valori sicuri
     * (niente url(), var(), expression(), ecc.).
     */
    private static function sanitizeCssDeclarationsForRichText(string $style): string
    {
        $out = [];
        foreach (preg_split('/;/', $style) as $chunk) {
            if (! preg_match('/^\s*([a-zA-Z-]+)\s*:\s*(.+?)\s*$/s', $chunk, $cm)) {
                continue;
            }
            $prop = strtolower($cm[1]);
            $val = trim($cm[2]);

            if ($prop === 'color' || $prop === 'background-color') {
                if (self::isSafeCssColorValue($val)) {
                    $out[] = $prop . ': ' . $val;
                }
                continue;
            }

            if ($prop === 'text-align' && preg_match('/^(left|right|center|justify|start|end)$/i', $val)) {
                $out[] = $prop . ': ' . strtolower($val);
                continue;
            }

            if ($prop === 'float' && preg_match('/^(left|right|none)$/i', $val)) {
                $out[] = $prop . ': ' . strtolower($val);
                continue;
            }

            if ($prop === 'display' && preg_match('/^(block|inline|inline-block)$/i', $val)) {
                $out[] = $prop . ': ' . strtolower($val);
                continue;
            }

            if (in_array($prop, ['margin', 'margin-left', 'margin-right', 'margin-top', 'margin-bottom'], true)
                && preg_match('/^(auto|0|[0-9]{1,4}(\.[0-9]+)?(px|em|rem|%))(\s+(auto|0|[0-9]{1,4}(\.[0-9]+)?(px|em|rem|%))){0,3}$/i', $val)) {
                $out[] = $prop . ': ' . strtolower($val);
                continue;
            }

            if ($prop === 'line-height' && preg_match('/^[0-9]{1}(\.[0-9]+)?(px|em|rem|%)?$/i', $val)) {
                $out[] = $prop . ': ' . strtolower($val);
                continue;
            }
        }

        return implode('; ', $out);
    }

    private static function isSafeCssColorValue(string $v): bool
    {
        $v = trim($v);
        if ($v === '' || strlen($v) > 200) {
            return false;
        }
        $lower = strtolower($v);
        foreach (['url(', 'expression', 'javascript:', '@import', 'behavior', 'binding', '<', '>', '/*', 'var('] as $bad) {
            if (str_contains($lower, $bad)) {
                return false;
            }
        }
        if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $v)) {
            return true;
        }
        if (preg_match('/^rgba?\(\s*[0-9.]+%?\s*,\s*[0-9.]+%?\s*,\s*[0-9.]+%?\s*(,\s*[0-9.]+%?\s*)?\)$/i', $v)) {
            return true;
        }
        if (preg_match('/^hsla?\(\s*[0-9.]+\s*,\s*[0-9.]+%\s*,\s*[0-9.]+%\s*(,\s*[0-9.]+%?\s*)?\)$/i', $v)) {
            return true;
        }
        if (preg_match('/^[a-zA-Z][a-zA-Z0-9-]{0,79}$/', $v)) {
            return true;
        }

        return false;
    }
}
