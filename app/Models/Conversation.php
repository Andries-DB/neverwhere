<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'status',
        'guid',
        'visible'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Relatie: Conversation behoort toe aan een User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relatie: Conversation heeft vele messages
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
