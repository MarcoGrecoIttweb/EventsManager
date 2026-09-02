<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use App\Support\SafeRichText;
use App\Support\StrLimit;
use App\Models\EventWaitlistEntry;

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
        'greeting_box_enabled', 'greeting_box_duration', 'greeting_box_message',
        'greeting_box_max_width', 'greeting_box_border_color', 'greeting_box_bg_color',
        'iscrizioni_chiuse',
    ];

    protected $casts = [
        'dataevento' => 'datetime',
        'datascadenza' => 'datetime',
        'costo' => 'float',
        'pubblicato' => 'integer',
        'elenco_visibile' => 'boolean',
        'allow_guests' => 'boolean',
        'greeting_box_enabled' => 'boolean',
        'iscrizioni_chiuse' => 'boolean',
        'visite' => 'integer',
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
     * (giorno della settimana, giorno, mese, h. ore).
     */
    public function getItalianEventDateAttribute(): ?string
    {
        if (!$this->dataevento) {
            return null;
        }

        try {
            $d = $this->date->copy()->locale('it');

            return $d->translatedFormat('l, j F') . ', H. ' . $d->format('H:i');
        } catch (\Throwable $e) {
            try {
                $d = \Carbon\Carbon::parse($this->dataevento)->locale('it');

                return $d->translatedFormat('l, j F') . ', H. ' . $d->format('H:i');
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
        if ($this->costo === null || (float) $this->costo == 0.0) {
            return null;
        }
        // Solo importo formattato; il simbolo € è mostrato una sola volta nella vista (es. dettaglio evento).
        return number_format((float) $this->costo, 2, ',', '.');
    }

    /**
     * Link pubblico album foto (salvato in url_galleria). Solo URL http/https validi.
     */
    public function getGoogleAlbumUrlAttribute(): ?string
    {
        $raw = trim((string) ($this->attributes['url_galleria'] ?? ''));
        if ($raw === '') {
            return null;
        }
        if (! filter_var($raw, FILTER_VALIDATE_URL)) {
            return null;
        }
        $lower = strtolower($raw);
        if (strpos($lower, 'https://') !== 0 && strpos($lower, 'http://') !== 0) {
            return null;
        }

        return $raw;
    }

    /**
     * Query testuale per Google Maps (geocoding).
     * Con indirizzo solo se $withExactAddress (es. utente autenticato).
     */
    public function googleMapsQuery(bool $withExactAddress = false): ?string
    {
        $bits = [];
        $dove = trim((string) ($this->dove ?? ''));
        if ($dove !== '') {
            $bits[] = $dove;
        }
        if ($withExactAddress) {
            $street = trim(preg_replace('/\s+/', ' ', trim((string) ($this->via ?? '')) . ' ' . trim((string) ($this->civico ?? ''))));
            if ($street !== '') {
                $bits[] = $street;
            }
        }
        $city = trim((string) ($this->citta ?? ''));
        if ($city !== '') {
            $bits[] = $city;
        }
        if ($bits === []) {
            return null;
        }
        $bits[] = 'Italia';

        return implode(', ', $bits);
    }

    /**
     * URL per iframe embed. Con GOOGLE_MAPS_API_KEY: Embed API ufficiale.
     * Senza chiave: iframe classico (può essere limitato da Google).
     */
    public function googleMapsEmbedUrl(bool $withExactAddress = false): ?string
    {
        $q = $this->googleMapsQuery($withExactAddress);
        if ($q === null) {
            return null;
        }
        $encoded = rawurlencode($q);
        $key = config('services.google_maps.api_key');
        if (! empty($key)) {
            return 'https://www.google.com/maps/embed/v1/place?key=' . urlencode($key) . '&q=' . $encoded;
        }

        return 'https://maps.google.com/maps?q=' . $encoded . '&output=embed&hl=it';
    }

    public function googleMapsExternalUrl(bool $withExactAddress = false): ?string
    {
        $q = $this->googleMapsQuery($withExactAddress);
        if ($q === null) {
            return null;
        }

        return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($q);
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

    public function ratings(): HasMany
    {
        return $this->hasMany(EventRating::class, 'event_id', 'IDevento');
    }

    /**
     * L'utente ha partecipato a questo evento (a prescindere dagli ospiti portati).
     */
    public function wasAttendedBy(User $user): bool
    {
        return $this->participants()->where('utente.userID', $user->getKey())->exists();
    }

    /**
     * L'utente può votare l'evento: deve averlo frequentato e l'evento deve essere già iniziato
     * (si può votare anche durante lo svolgimento, non solo a evento concluso).
     */
    public function canBeRatedBy(User $user): bool
    {
        return $this->hasStarted() && $this->wasAttendedBy($user);
    }

    /**
     * True dalla data/ora di inizio dell'evento in poi (durante o dopo, non solo il giorno successivo).
     */
    public function hasStarted(): bool
    {
        if (!$this->dataevento) {
            return false;
        }

        try {
            return $this->dataevento->lte(now());
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function ratingByUser(User $user): ?EventRating
    {
        return $this->ratings->firstWhere('user_id', $user->getKey());
    }

    public function getAverageRatingAttribute(): ?float
    {
        $avg = $this->ratings->avg('rating');

        return $avg !== null ? round((float) $avg, 1) : null;
    }

    public function getRatingsCountAttribute(): int
    {
        return $this->ratings->count();
    }

    /**
     * Iscrive automaticamente l'organizzatore come partecipante (se non già presente).
     * Utile per eventi creati/duplicati dal pannello admin.
     */
    public function enrollOrganizerAsParticipant(): void
    {
        $organizerId = (int) ($this->id_organizzatore ?? 0);
        if ($organizerId <= 0) {
            return;
        }

        try {
            $exists = $this->participants()
                ->where('utente.userID', $organizerId)
                ->exists();

            if ($exists) {
                return;
            }

            $this->participants()->attach($organizerId, [
                'amici' => 0,
                'data_iscrizione' => now()->format('Y-m-d H:i:s'),
                'ospiti_inseriti_il' => null,
            ]);
        } catch (\Throwable $e) {
            // Non bloccare la creazione evento se la pivot legacy non è disponibile/ha vincoli.
            \Log::warning('Impossibile iscrivere organizzatore come partecipante', [
                'event_id' => $this->getKey(),
                'organizer_id' => $organizerId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'id_evento', 'IDevento');
    }

    public function images(): HasMany
    {
        return $this->hasMany(EventImage::class, 'event_id', 'IDevento')->orderBy('order');
    }

    public function waitlistEntries(): HasMany
    {
        return $this->hasMany(EventWaitlistEntry::class, 'event_id', 'IDevento');
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
        if ($this->iscrizioni_chiuse) {
            return false;
        }
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

        if (!$this->isRegistrationOpen()) {
            return false;
        }

        $participation = $this->participants()->where('utente.userID', $user->getKey())->first();

        if (!$participation) {
            return false;
        }

        $currentGuests = (int) ($participation->pivot->amici ?? 0);
        $maxGuests = (int) ($this->max_guests_per_user ?? 10);
        if ($maxGuests < 1) {
            $maxGuests = 10;
        }
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
        $raw = (string) ($this->attributes['descrizione'] ?? '');
        if ($raw === '') {
            return '';
        }

        $decoded = $raw;
        for ($i = 0; $i < 3; $i++) {
            $next = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($next === $decoded) {
                break;
            }
            $decoded = $next;
        }

        $cleanContent = SafeRichText::sanitize($decoded, false);

        if ($cleanContent !== '' && strpos($cleanContent, '<') === false) {
            return nl2br(e($cleanContent), false);
        }

        return $cleanContent;
    }

    public function getSafeDescriptionNoImagesAttribute(): string
    {
        $html = $this->safe_description;
        if ($html === '') {
            return '';
        }

        $html = preg_replace('/<img\b[^>]*>/i', '', $html) ?? $html;

        return $html;
    }

    /**
     * Descrizione evento per stampa partecipanti (senza immagini, senza paragrafi vuoti).
     */
    public function getPrintDescriptionHtmlAttribute(): string
    {
        $html = (string) $this->safe_description_no_images;
        if ($html !== '') {
            $html = preg_replace(
                '/<p\b[^>]*>\s*(?:&nbsp;|\xc2\xa0|<br\s*\/?>)?\s*<\/p>/iu',
                '',
                $html
            ) ?? $html;
            $html = preg_replace(
                '/<div\b[^>]*>\s*(?:&nbsp;|\xc2\xa0|<br\s*\/?>)?\s*<\/div>/iu',
                '',
                $html
            ) ?? $html;
            $html = preg_replace(
                '/<p\b[^>]*>\s*<span\b[^>]*>\s*<\/span>\s*<\/p>/iu',
                '',
                $html
            ) ?? $html;
        }

        if ($this->descriptionHasVisibleText($html)) {
            return trim($html);
        }

        $incipit = trim((string) ($this->incipit ?? ''));
        if ($incipit !== '') {
            return '<p>' . e($incipit) . '</p>';
        }

        return '';
    }

    /**
     * Testo visibile per decidere se mostrare la descrizione in stampa.
     */
    public function getPrintDescriptionPlainAttribute(): string
    {
        return $this->plainTextFromHtml($this->print_description_html);
    }

    private function descriptionHasVisibleText(string $html): bool
    {
        return $this->plainTextFromHtml($html) !== '';
    }

    private function plainTextFromHtml(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $plain = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plain = str_replace(["\xc2\xa0", '&nbsp;'], ' ', $plain);
        $plain = preg_replace('/\s+/u', ' ', $plain) ?? $plain;

        return trim($plain);
    }

    public function getShortPreviewAttribute(): string
    {
        if (!empty($this->incipit)) {
            return StrLimit::limit(trim($this->incipit), 150);
        }
        return $this->getHomepagePreview(150);
    }

    public function getHomepagePreview($length = 200): string
    {
        if (!empty($this->incipit)) {
            return StrLimit::limit(trim($this->incipit), $length);
        }

        if (empty($this->descrizione)) {
            return '';
        }

        $plainText = strip_tags($this->descrizione);
        $plainText = html_entity_decode($plainText, ENT_QUOTES, 'UTF-8');
        $plainText = preg_replace('/\s+/', ' ', $plainText);
        $plainText = trim($plainText);

        return StrLimit::limit($plainText, $length);
    }

    /**
     * Testo completo mostrato nelle liste pubbliche (incipit o descrizione in plain text).
     */
    public function getFullPublicPreviewAttribute(): string
    {
        if (!empty($this->incipit)) {
            return trim($this->incipit);
        }

        if (empty($this->descrizione)) {
            return '';
        }

        $plainText = strip_tags($this->descrizione);
        $plainText = html_entity_decode($plainText, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plainText = preg_replace('/\s+/', ' ', $plainText);

        return trim($plainText);
    }

    public function isPublicPreviewTruncated(int $length = 100): bool
    {
        $full = $this->full_public_preview;

        return $full !== '' && mb_strlen($full) > $length;
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
        // Un evento resta "in programma" per tutto il giorno della sua data.
        // Diventa "passato" solo dal giorno successivo (dopo mezzanotte).
        return $query->where('dataevento', '>=', now()->startOfDay());
    }

    public function scopePast($query)
    {
        // Considera "passati" solo gli eventi con data precedente a oggi.
        return $query->where('dataevento', '<', now()->startOfDay());
    }

    /**
     * True se la data/ora dell'evento non è più futura (concluso rispetto al calendario).
     */
    public function getIsPastEventAttribute(): bool
    {
        if (!$this->dataevento) {
            return false;
        }

        try {
            // Un evento è "passato" solo dal giorno successivo alla sua data.
            return $this->date->lt(now()->startOfDay());
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function scopeOrdered($query, $direction = 'asc')
    {
        return $query->orderBy('dataevento', $direction);
    }
}
