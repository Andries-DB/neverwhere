<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PinnedGraph extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'message_id',
        'title',
        'sort_chart',
        '_x',
        '_y',
        '_agg',
        'json',
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
