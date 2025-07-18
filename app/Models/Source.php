<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    protected $fillable = [
        'hex_color',
        'name',
        'webhook',
        'company_id',
        'model'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_source')->withTimestamps();
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function userGroups()
    {
        return $this->belongsToMany(UserGroup::class);
    }
}
