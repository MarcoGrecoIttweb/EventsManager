<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPageVisit extends Model
{
    protected $table = 'event_page_visits';

    protected $fillable = [
        'event_id',
        'user_id',
        'visits_count',
        'first_visited_at',
        'last_visited_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'visits_count' => 'integer',
        'first_visited_at' => 'datetime',
        'last_visited_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'userID');
    }
}
