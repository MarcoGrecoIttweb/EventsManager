<?php

namespace App\Models;

use App\Support\SafeRichText;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Legacy table mapping
    protected $table = 'utente';
    protected $primaryKey = 'userID';

    // Timestamps mapping
    const CREATED_AT = 'iscrittodal';
    const UPDATED_AT = null;

    // Disable remember_token (legacy table doesn't have it)
    protected $rememberTokenName = '';

    protected $fillable = [
        'username', 'password', 'password_laravel', 'nome', 'cognome', 'email',
        'mail_visibile', 'datanascita', 'ruolo', 'residenza',
        'sesso', 'descr', 'avatar', 'abilitato', 'note_utente',
        'telefono', 'invia', 'ultimo_accesso',
    ];

    protected $hidden = [
        'password', 'password_laravel',
    ];

    // Laravel usa password_laravel per l'autenticazione, lasciando intatto il campo
    // password (MD5) usato dal sito legacy
    public function getAuthPassword(): string
    {
        return $this->password_laravel ?? $this->password;
    }

    protected $casts = [
        'datanascita' => 'date',
        'iscrittodal' => 'datetime',
        'ultimo_accesso' => 'datetime',
        'mail_visibile' => 'boolean',
        'invia' => 'boolean',
    ];

    // ─── Accessors (legacy → Laravel names used by views) ─────────

    public function getIdAttribute()
    {
        return $this->userID;
    }

    public function getCreatedAtAttribute()
    {
        return $this->iscrittodal ? \Illuminate\Support\Carbon::parse($this->iscrittodal) : null;
    }

    public function getNameAttribute()
    {
        // Alias legacy: "name" → "nome"
        return $this->nome;
    }

    public function setNameAttribute($value)
    {
        // Alias legacy: "name" → "nome"
        $this->nome = $value;
    }

    public function getNomeAttribute($value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return $value;
        }
        $lower = mb_strtolower($value, 'UTF-8');
        return mb_convert_case($lower, MB_CASE_TITLE, 'UTF-8');
    }

    public function setNomeAttribute($value): void
    {
        // Salva già in formato "Titolo" per avere coerenza ovunque (DB incluso)
        $value = trim((string) $value);
        if ($value === '') {
            $this->attributes['nome'] = $value;
            return;
        }
        $lower = mb_strtolower($value, 'UTF-8');
        $this->attributes['nome'] = mb_convert_case($lower, MB_CASE_TITLE, 'UTF-8');
    }

    public function getCognomeAttribute($value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return $value;
        }
        $lower = mb_strtolower($value, 'UTF-8');
        return mb_convert_case($lower, MB_CASE_TITLE, 'UTF-8');
    }

    public function getNicknameAttribute()
    {
        return $this->username;
    }

    public function setNicknameAttribute($value)
    {
        $this->attributes['username'] = $value;
    }

    public function getIsAdminAttribute()
    {
        return (int) $this->ruolo === 0;
    }

    public function getStatusAttribute()
    {
        return match ((int) $this->abilitato) {
            1 => 'approved',
            2 => 'banned',
            default => 'pending',
        };
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['abilitato'] = match ($value) {
            'approved' => 1,
            'banned' => 2,
            default => 0,
        };
    }

    public function getPhotoAttribute()
    {
        return $this->avatar;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->avatar) {
            return null;
        }

        // Legacy avatars in public/upload_avatar/
        if (file_exists(public_path("upload_avatar/{$this->avatar}"))) {
            return asset("upload_avatar/{$this->avatar}");
        }

        // New Laravel-uploaded photos
        return asset("storage/photos/{$this->avatar}");
    }

    public function setPhotoAttribute($value)
    {
        $this->attributes['avatar'] = $value;
    }

    public function getDescriptionAttribute()
    {
        return $this->descr;
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['descr'] = $value;
    }

    /**
     * Descrizione profilo con HTML consentito (stesso schema dell'evento).
     */
    public function getSafeDescrAttribute(): string
    {
        $raw = $this->attributes['descr'] ?? '';
        if ($raw === '' || $raw === null) {
            return '';
        }

        return SafeRichText::sanitize((string) $raw, true);
    }

    public function getResidenzaAttribute($value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return $value;
        }

        $lower = mb_strtolower($value, 'UTF-8');
        return mb_convert_case($lower, MB_CASE_TITLE, 'UTF-8');
    }

    // ─── Relationships ────────────────────────────────────────────

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'partecipa', 'id_utente', 'id_evento', 'userID', 'IDevento')
            ->withPivot('amici', 'data_iscrizione', 'ospiti_inseriti_il');
    }

    public function participatingEvents()
    {
        return $this->events()
            ->where('pubblicato', 1)
            ->where('dataevento', '>', now())
            ->orderBy('dataevento');
    }

    public function pastEvents()
    {
        return $this->events()
            ->where('pubblicato', 1)
            ->where('dataevento', '<=', now())
            ->orderBy('dataevento', 'desc');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'id_utente', 'userID');
    }

    public function friends(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'amici', 'id_di_chi', 'id_amico', 'userID', 'userID');
    }

    public function isFriendOf(User $user): bool
    {
        return $this->friends()->where('utente.userID', $user->getKey())->exists();
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'gruppo_utente', 'id_utente', 'id_gruppo', 'userID', 'Id_gruppo');
    }

    // ─── Helper methods ───────────────────────────────────────────

    public function isAdmin(): bool
    {
        return (int) $this->ruolo === 0;
    }

    public function isOrganizer(): bool
    {
        return (int) $this->ruolo === 1;
    }

    public function canManageEvents(): bool
    {
        return $this->isAdmin() || $this->isOrganizer();
    }

    public function getRoleNameAttribute(): string
    {
        return match ((int) $this->ruolo) {
            0 => 'Admin',
            1 => 'Organizzatore',
            default => 'Utente',
        };
    }

    public function isApproved(): bool
    {
        return (int) $this->abilitato === 1;
    }

    public function isPending(): bool
    {
        return (int) $this->abilitato === 0;
    }

    public function isBanned(): bool
    {
        return (int) $this->abilitato === 2;
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeNonAdmin($query)
    {
        return $query->where('ruolo', '!=', 0);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('abilitato', match ($status) {
            'approved' => 1,
            'banned' => 2,
            default => 0,
        });
    }

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

    public function eventsWithGuests()
    {
        return $this->events();
    }
}
