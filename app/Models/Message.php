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
        'thumbs_up',
        'thumbs_down',
        'feedback',
        'source_id',
        'question_message',
        'col_def',
        '_x',
        '_y',
        '_agg',
        '_order',
        '_sort'
    ];

    protected $casts = [
        'send_by' => 'string',
        'json' => 'encrypted:array',
        'message' => 'encrypted',
        'sql_query' => 'encrypted'

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

    public function pinnedItems()
    {
        return $this->hasMany(PinnedItem::class);
    }

    public function source()
    {
        return $this->belongsTo(Source::class);
    }
}
