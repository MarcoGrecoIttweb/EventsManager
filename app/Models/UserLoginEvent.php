<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLoginEvent extends Model
{
    protected $table = 'user_login_events';

    protected $fillable = [
        'user_id',
        'logged_in_at',
        'ip_address',
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
