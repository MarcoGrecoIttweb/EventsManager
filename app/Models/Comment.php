<?php

namespace App\Models;

use App\Support\SafeRichText;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'post';

    // La tabella legacy usa 'data' invece di created_at/updated_at
    public $timestamps = false;
    const CREATED_AT = 'data';

    protected $fillable = ['testo', 'id_evento', 'id_utente', 'edited_at'];

    protected $casts = [
        'data' => 'datetime',
        'edited_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_utente', 'userID');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_evento', 'IDevento');
    }

    // Alias per compatibilità con le view (la colonna reale è 'testo')
    public function getContentAttribute(): string
    {
        return $this->testo ?? '';
    }

    // Alias per compatibilità con le view (la colonna reale è 'data')
    public function getCreatedAtAttribute()
    {
        return $this->data;
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
     * HTML sicuro per la visualizzazione (stesso schema della descrizione evento).
     * Decodifica entità così non si vede il sorgente tipo &lt;p&gt;…&lt;/p&gt;.
     */
    public function getSafeContentAttribute(): string
    {
        $raw = (string) ($this->attributes['testo'] ?? '');
        if ($raw === '') {
            return '';
        }

        $decoded = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $cleanContent = SafeRichText::sanitize($decoded, false);

        // Commenti senza markup (nessun "<"): escapa e rispetta gli a capo
        if ($cleanContent !== '' && strpos($cleanContent, '<') === false) {
            return nl2br(e($cleanContent), false);
        }

        return $cleanContent;
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
