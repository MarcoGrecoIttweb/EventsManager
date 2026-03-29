<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use App\Support\SafeRichText;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    // Legacy table mapping
    protected $table = 'evento';
    protected $primaryKey = 'IDevento';

    // No timestamps in legacy
    public $timestamps = false;

    protected $fillable = [
        'nome', 'thumbnail', 'immagine', 'descrizione', 'incipit',
        'costo', 'dove', 'via', 'civico', 'citta', 'dataevento',
        'datascadenza', 'numeromax', 'id_organizzatore', 'sondaggio',
        'url_galleria', 'elenco_visibile', 'pubblicato',
        'allow_guests', 'max_guests_per_user',
    ];

    protected $casts = [
        'dataevento' => 'datetime',
        'datascadenza' => 'datetime',
        'costo' => 'float',
        'pubblicato' => 'integer',
        'elenco_visibile' => 'boolean',
        'allow_guests' => 'boolean',
    ];

    // ─── Accessors (legacy → Laravel names used by views) ─────────

    public function getIdAttribute()
    {
        return $this->IDevento;
    }

    public function getTitleAttribute()
    {
        return $this->nome;
    }

    public function setTitleAttribute($value)
    {
        $this->attributes['nome'] = $value;
    }

    public function getDescriptionAttribute()
    {
        return $this->descrizione;
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['descrizione'] = $value;
    }

    public function getDateAttribute()
    {
        return $this->dataevento;
    }

    public function setDateAttribute($value)
    {
        $this->attributes['dataevento'] = $value;
    }

    /**
     * Data e ora in italiano: "lunedì 24 marzo 2026, h:14:30"
     * (giorno della settimana, giorno, mese, anno, ora come h:HH:MM).
     */
    public function getItalianEventDateAttribute(): ?string
    {
        if (!$this->dataevento) {
            return null;
        }

        try {
            $d = $this->date->copy()->locale('it');

            return $d->translatedFormat('l j F Y') . ', h:' . $d->format('H:i');
        } catch (\Throwable $e) {
            try {
                $d = \Carbon\Carbon::parse($this->dataevento)->locale('it');

                return $d->translatedFormat('l j F Y') . ', h:' . $d->format('H:i');
            } catch (\Throwable $e2) {
                return null;
            }
        }
    }

    public function getCityAttribute()
    {
        return $this->citta;
    }

    public function setCityAttribute($value)
    {
        $this->attributes['citta'] = $value;
    }

    public function getAddressAttribute()
    {
        return trim(($this->via ?? '') . ' ' . ($this->civico ?? ''));
    }

    public function setAddressAttribute($value)
    {
        $this->attributes['via'] = $value;
        $this->attributes['civico'] = '';
    }

    public function getMaxParticipantsAttribute()
    {
        return $this->numeromax;
    }

    public function setMaxParticipantsAttribute($value)
    {
        $this->attributes['numeromax'] = $value;
    }

    public function getUserIdAttribute()
    {
        return $this->id_organizzatore;
    }

    public function getIsActiveAttribute()
    {
        return (bool) $this->pubblicato;
    }

    public function setIsActiveAttribute($value)
    {
        $this->attributes['pubblicato'] = $value ? 1 : 0;
    }

    public function getCoverImageAttribute()
    {
        return $this->immagine;
    }

    public function setCoverImageAttribute($value)
    {
        $this->attributes['immagine'] = $value;
    }

    public function getVenueAttribute()
    {
        return $this->dove;
    }

    public function setVenueAttribute($value)
    {
        $this->attributes['dove'] = $value;
    }

    public function getCostAttribute()
    {
        return $this->costo;
    }

    public function setCostAttribute($value)
    {
        $this->attributes['costo'] = $value;
    }

    public function getFormattedCostAttribute(): ?string
    {
        if ($this->costo === null || $this->costo == 0) {
            return null;
        }
        return number_format($this->costo, 2, ',', '.') . ' €';
    }

    // ─── Relationships ────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_organizzatore', 'userID');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'partecipa', 'id_evento', 'id_utente', 'IDevento', 'userID')
            ->withPivot('amici', 'data_iscrizione', 'ospiti_inseriti_il');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'id_evento', 'IDevento');
    }

    public function images(): HasMany
    {
        return $this->hasMany(EventImage::class, 'event_id', 'IDevento')->orderBy('order');
    }

    // ─── Computed attributes ──────────────────────────────────────

    public function getParticipantsCountAttribute(): int
    {
        return $this->participants()->sum('partecipa.amici') + $this->participants()->count();
    }

    public function getRealParticipantsCountAttribute(): int
    {
        return $this->participants()->count();
    }

    public function isFull(): bool
    {
        if (!$this->numeromax) return false;
        return $this->participants_count >= $this->numeromax;
    }

    public function isRegistrationOpen(): bool
    {
        if ($this->datascadenza && $this->datascadenza->year > 1 && $this->datascadenza->isPast()) {
            return false;
        }
        return true;
    }

    public function getDeadlineAttribute()
    {
        if (!$this->datascadenza || $this->datascadenza->year <= 1) {
            return null;
        }
        return $this->datascadenza;
    }

    public function canAddMoreGuests(User $user): bool
    {
        if (!$this->allow_guests) {
            return false;
        }

        $participation = $this->participants()->where('utente.userID', $user->getKey())->first();

        if (!$participation) {
            return false;
        }

        $currentGuests = $participation->pivot->amici ?? 0;
        $maxGuests = $this->max_guests_per_user ?? 10;
        $canAddMore = $currentGuests < $maxGuests;
        $eventNotFull = !$this->isFull();

        return $canAddMore && $eventNotFull;
    }

    public function getUserGuestsCount(User $user): int
    {
        $participation = $this->participants()->where('utente.userID', $user->getKey())->first();
        return $participation ? ($participation->pivot->amici ?? 0) : 0;
    }

    public function getOccupancyPercentageAttribute(): float
    {
        if (!$this->numeromax) {
            return 0;
        }
        return min(($this->participants_count / $this->numeromax) * 100, 100);
    }

    public function isAlmostFull(): bool
    {
        if (!$this->numeromax) {
            return false;
        }
        return $this->occupancy_percentage >= 80 && !$this->isFull();
    }

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

    public function getSafeDescriptionAttribute(): string
    {
        if (empty($this->descrizione)) {
            return '';
        }

        return SafeRichText::sanitize($this->descrizione, false);
    }

    public function getShortPreviewAttribute(): string
    {
        if (!empty($this->incipit)) {
            return Str::limit(trim($this->incipit), 150);
        }
        return $this->getHomepagePreview(150);
    }

    public function getHomepagePreview($length = 200): string
    {
        if (!empty($this->incipit)) {
            return Str::limit(trim($this->incipit), $length);
        }

        if (empty($this->descrizione)) {
            return '';
        }

        $plainText = strip_tags($this->descrizione);
        $plainText = html_entity_decode($plainText, ENT_QUOTES, 'UTF-8');
        $plainText = preg_replace('/\s+/', ' ', $plainText);
        $plainText = trim($plainText);

        return Str::limit($plainText, $length);
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if (!$this->immagine) {
            return null;
        }

        // Legacy images are stored in public/upload_immagini/
        if (file_exists(public_path("upload_immagini/{$this->immagine}"))) {
            return asset("upload_immagini/{$this->immagine}");
        }

        // New Laravel-uploaded images
        if (str_contains($this->immagine, '/')) {
            return Storage::disk('public')->url($this->immagine);
        }

        return Storage::disk('public')->url("events/{$this->getKey()}/{$this->immagine}");
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail) {
            return null;
        }

        if (file_exists(public_path("upload_immagini/{$this->thumbnail}"))) {
            return asset("upload_immagini/{$this->thumbnail}");
        }

        return $this->cover_image_url;
    }

    public function getHasImagesAttribute(): bool
    {
        return $this->images()->count() > 0;
    }

    public function getImagesCountAttribute(): int
    {
        return $this->images()->count();
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('pubblicato', 1);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('dataevento', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where('dataevento', '<=', now());
    }

    /**
     * True se la data/ora dell'evento non è più futura (concluso rispetto al calendario).
     */
    public function getIsPastEventAttribute(): bool
    {
        if (!$this->dataevento) {
            return false;
        }

        return $this->date->lte(now());
    }

    public function scopeOrdered($query, $direction = 'asc')
    {
        return $query->orderBy('dataevento', $direction);
    }
}
