<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Decklist extends Model
{
    protected $fillable = [
        'user_id', 'event_id', 'filename', 'original_name',
        'path', 'mime_type', 'size'
    ];

    protected $appends = ['url', 'formatted_size', 'upload_date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->size;
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.2f", $bytes / pow(1024, $factor)) . ' ' . $units[$factor];
    }

    public function getUploadDateAttribute(): string
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($decklist) {
            Storage::disk('public')->delete($decklist->path);
        });
    }
}
