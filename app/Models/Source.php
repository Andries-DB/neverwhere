<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    protected $fillable = [
        'hex_color',
        'name',
        'webhook'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_source')->withTimestamps();
    }
}
