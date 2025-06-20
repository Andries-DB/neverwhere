<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    protected $fillable = [
        'hex_color',
        'name',
        'webhook',
        'company_id'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_source')->withTimestamps();
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTimestamps();
    }
}
