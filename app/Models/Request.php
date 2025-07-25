<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'description',
        'steps',
        'priority',
        'environment'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
