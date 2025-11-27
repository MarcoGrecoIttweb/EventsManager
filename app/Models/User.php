<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'nickname',
        'email',
        'password',
        'photo',
        'description',
        'status',
        'is_admin'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
    ];

    public function events()
    {
        return $this->belongsToMany(Event::class)
            ->withPivot('guests_count')
            ->withTimestamps();
    }

    public function participatingEvents()
    {
        return $this->events()
            ->where('is_active', true)
            ->where('date', '>', now())
            ->orderBy('date');
    }

    public function pastEvents()
    {
        return $this->events()
            ->where('is_active', true)
            ->where('date', '<=', now())
            ->orderBy('date', 'desc');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isBanned(): bool
    {
        return $this->status === 'banned';
    }

    public function eventsWithGuests()
    {
        return $this->belongsToMany(Event::class)
            ->withPivot('guests_count')
            ->withTimestamps();
    }
}
