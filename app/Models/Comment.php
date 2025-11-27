<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'event_id', 'user_id', 'edited_at'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'edited_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Check if the comment has been edited.
     */
    public function getIsEditedAttribute(): bool
    {
        return !is_null($this->edited_at);
    }

    /**
     * Get the display text for edit information.
     */
    public function getEditInfoAttribute(): string
    {
        if (!$this->is_edited) {
            return '';
        }

        return 'Modificato il ' . $this->edited_at->format('d/m/Y H:i');
    }

    /**
     * Get safe HTML content with allowed tags only.
     */
    public function getSafeContentAttribute(): string
    {
        // Lista dei tag HTML permessi
        $allowedTags = '<p><br><strong><b><em><i><u><a><ul><ol><li><code><pre><span><div>';

        // Rimuovi tutti i tag non permessi
        $cleanContent = strip_tags($this->content, $allowedTags);

        // Rimuovi attributi pericolosi dai tag permessi
        $cleanContent = $this->removeDangerousAttributes($cleanContent);

        // Converti nuove linee in <br> per chi non usa l'editor
        $cleanContent = nl2br($cleanContent);

        return $cleanContent;
    }

    /**
     * Remove dangerous attributes from HTML tags.
     */
    private function removeDangerousAttributes(string $html): string
    {
        // Attributi permessi per i tag
        $allowedAttributes = [
            'a' => ['href', 'title', 'target'],
            'img' => ['src', 'alt', 'title', 'width', 'height'],
        ];

        // Pattern per trovare e rimuovere attributi pericolosi
        $html = preg_replace_callback('/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i', function($matches) use ($allowedAttributes) {
            $tagName = strtolower($matches[1]);
            $selfClosing = $matches[2];

            // Se il tag non è nella lista permessa, rimuovilo completamente
            if (!in_array($tagName, ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'a', 'ul', 'ol', 'li', 'code', 'pre', 'span', 'div'])) {
                return '';
            }

            // Se è un tag <a>, assicurati che gli href siano sicuri
            if ($tagName === 'a') {
                return preg_replace_callback('/<a\s+(.*?)>/i', function($aMatches) {
                    if (preg_match('/href=["\'](javascript:|data:|vbscript:)/i', $aMatches[1])) {
                        return '<a>'; // Rimuovi link pericolosi
                    }
                    // Mantieni solo attributi sicuri per i link
                    $safeAttributes = '';
                    if (preg_match('/href=["\']([^"\'<>]*)["\']/i', $aMatches[1], $hrefMatch)) {
                        $safeAttributes .= ' href="' . e($hrefMatch[1]) . '"';
                    }
                    if (preg_match('/title=["\']([^"\'<>]*)["\']/i', $aMatches[1], $titleMatch)) {
                        $safeAttributes .= ' title="' . e($titleMatch[1]) . '"';
                    }
                    if (preg_match('/target=["\'](_blank)["\']/i', $aMatches[1], $targetMatch)) {
                        $safeAttributes .= ' target="' . e($targetMatch[1]) . '"';
                    }
                    return '<a' . $safeAttributes . '>';
                }, $matches[0]);
            }

            return '<' . $tagName . '>';
        }, $html);

        return $html ?: '';
    }

    /**
     * Get plain text content (for previews)
     */
    public function getPlainContentAttribute(): string
    {
        return strip_tags($this->content);
    }

    /**
     * Get short preview of content
     */
    public function getPreviewAttribute(): string
    {
        $plainContent = $this->plain_content;
        return strlen($plainContent) > 100 ? substr($plainContent, 0, 100) . '...' : $plainContent;
    }
}
