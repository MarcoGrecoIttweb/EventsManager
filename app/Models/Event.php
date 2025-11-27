<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'date', 'city', 'address',
        'max_participants', 'user_id', 'is_active', 'allow_guests',
        'max_guests_per_user', 'cover_image'
    ];

    protected $casts = [
        'date' => 'datetime',
        'is_active' => 'boolean',
        'allow_guests' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('guests_count')
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function getParticipantsCountAttribute(): int
    {
        return $this->participants()->sum('guests_count') + $this->participants()->count();
    }

    public function getRealParticipantsCountAttribute(): int
    {
        return $this->participants()->count();
    }

    public function isFull(): bool
    {
        if (!$this->max_participants) return false;

        return $this->participants_count >= $this->max_participants;
    }

    public function canAddMoreGuests(User $user): bool
    {
        if (!$this->allow_guests) {
            return false;
        }

        $participation = $this->participants()->where('user_id', $user->id)->first();

        if (!$participation) {
            return false;
        }

        $currentGuests = $participation->pivot->guests_count;
        $canAddMore = $currentGuests < $this->max_guests_per_user;
        $eventNotFull = !$this->isFull();

        return $canAddMore && $eventNotFull;
    }

    public function getUserGuestsCount(User $user): int
    {
        $participation = $this->participants()->where('user_id', $user->id)->first();
        return $participation ? $participation->pivot->guests_count : 0;
    }

    /**
     * Get the percentage of occupied seats.
     */
    public function getOccupancyPercentageAttribute(): float
    {
        if (!$this->max_participants) {
            return 0;
        }

        return min(($this->participants_count / $this->max_participants) * 100, 100);
    }

    /**
     * Check if event is almost full (80% or more).
     */
    public function isAlmostFull(): bool
    {
        if (!$this->max_participants) {
            return false;
        }

        return $this->occupancy_percentage >= 80 && !$this->isFull();
    }

    /**
     * Get occupancy status for display.
     */
    public function getOccupancyStatusAttribute(): string
    {
        if ($this->isFull()) {
            return 'full';
        } elseif ($this->isAlmostFull()) {
            return 'almost_full';
        } else {
            return 'available';
        }
    }

    /**
     * Get safe HTML content for event description.
     */
    public function getSafeDescriptionAttribute(): string
    {
        if (empty($this->description)) {
            return '';
        }

        // Tag HTML permessi per la descrizione
        $allowedTags = '<p><br><strong><b><em><i><u><a><ul><ol><li><code><pre><span><div><h1><h2><h3><h4><h5><h6><blockquote><table><thead><tbody><tr><th><td>';

        // Rimuovi tutti i tag non permessi
        $cleanContent = strip_tags($this->description, $allowedTags);

        // Per sicurezza, sanitizza gli attributi href
        $cleanContent = preg_replace_callback('/<a\s+([^>]*)>/i', function($matches) {
            $attributes = $matches[1];

            // Estrai l'href se presente
            $href = '';
            if (preg_match('/href=(["\'])(.*?)\1/i', $attributes, $hrefMatches)) {
                $url = $hrefMatches[2];
                // Permetti solo http, https e mailto
                if (preg_match('/^(https?:\/\/|mailto:)/i', $url)) {
                    $href = ' href="' . e($url) . '"';
                }
            }

            return '<a' . $href . '>';
        }, $cleanContent);

        return $cleanContent;
    }

    /**
     * Get formatted preview for homepage (simplified version).
     */
    public function getHomepagePreview($length = 2000): string
    {
        if (empty($this->description)) {
            return '';
        }

        // Tag HTML permessi
        $allowedTags = '<strong><b><em><i><u><br><span>';
        $cleanContent = strip_tags($this->description, $allowedTags);

        // Rimuovi tag complessi
        $cleanContent = preg_replace('/<h[1-6][^>]*>.*?<\/h[1-6]>/i', '', $cleanContent);
        $cleanContent = preg_replace('/<div[^>]*>.*?<\/div>/is', '', $cleanContent);

        // Converti paragrafi in line breaks
        $cleanContent = str_replace('</p>', '<br>', $cleanContent);
        $cleanContent = preg_replace('/<p[^>]*>/i', '', $cleanContent);

        // Usa la funzione Str::limit di Laravel che gestisce già l'HTML di base
        return Str::limit($cleanContent, $length);
    }

    public function images(): HasMany
    {
        return $this->hasMany(EventImage::class)->orderBy('order');
    }

    /**
     * Get cover image URL
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        if (!$this->cover_image) {
            return null;
        }

        // Se cover_image è già un percorso completo
        if (str_contains($this->cover_image, '/')) {
            return Storage::disk('public')->url($this->cover_image);
        }

        // Se cover_image è solo il nome file, costruisci il percorso
        return Storage::disk('public')->url("events/{$this->id}/{$this->cover_image}");
    }

    /**
     * Check if event has images
     */
    public function getHasImagesAttribute(): bool
    {
        return $this->images()->count() > 0;
    }

    /**
     * Get images count
     */
    public function getImagesCountAttribute(): int
    {
        return $this->images()->count();
    }
}
