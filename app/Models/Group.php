<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    protected $table = 'gruppi';
    protected $primaryKey = 'Id_gruppo';
    public $timestamps = false;

    protected $fillable = ['nome'];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'gruppo_utente', 'id_gruppo', 'id_utente', 'Id_gruppo', 'userID');
    }
}
