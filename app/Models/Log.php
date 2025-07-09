<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        'guid',
        'user_id',
        'sort',
        'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
