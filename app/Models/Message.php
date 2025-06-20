<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'guid',
        'user_id',
        'conversation_id',
        'send_by',
        'message',
        'json',
        'sql_query',
        'respond_type',
    ];

    protected $casts = [
        'send_by' => 'string',
        'json' => 'array',
    ];


    // Relatie: Message behoort toe aan een User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relatie: Message behoort tot een Conversation
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function pinnedGraphs()
    {
        return $this->hasMany(PinnedGraph::class);
    }
}
