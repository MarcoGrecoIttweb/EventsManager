<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CookieConsentLog extends Model
{
    protected $table = 'cookie_consent_logs';

    protected $fillable = [
        'user_id',
        'consent',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'consent' => 'array',
    ];
}

