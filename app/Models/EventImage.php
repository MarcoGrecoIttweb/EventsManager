<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class EventImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'filename', 'path', 'original_name',
        'mime_type', 'size', 'order', 'is_cover'
    ];

    protected $appends = ['url', 'thumbnail_url'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get full URL for the image
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrlAttribute(): string
    {
        return $this->url; // Per ora stessa immagine, puoi aggiungere thumbnails dopo
    }

    /**
     * Get human readable file size
     */
    public function getFormattedSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->size;
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.2f", $bytes / pow(1024, $factor)) . ' ' . $units[$factor];
    }

    /**
     * Delete physical file when model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($image) {
            Storage::disk('public')->delete($image->path);
        });
    }
}
