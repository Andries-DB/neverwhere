<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PinnedTable extends Model
{
    protected $fillable = [
        'user_id',
        'message_id',
        'json',
        'title',
        'width',
    ];

    protected $casts = [
        'json' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
