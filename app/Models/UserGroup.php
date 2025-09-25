<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    protected $fillable = [
        'name',
        'company_id',
        'guid'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_group_user');
    }

    public function sources()
    {
        return $this->belongsToMany(Source::class, 'user_group_source');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
