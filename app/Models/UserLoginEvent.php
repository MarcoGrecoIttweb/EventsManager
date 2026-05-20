<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLoginEvent extends Model
{
    public const SOURCE_LARAVEL = 'laravel';

    public const SOURCE_LEGACY = 'legacy';

    protected $table = 'user_login_events';

    protected $fillable = [
        'user_id',
        'logged_in_at',
        'ip_address',
        'source',
    ];

    public $timestamps = false;

    protected $casts = [
        'logged_in_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'userID');
    }
}
