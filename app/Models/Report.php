<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'guid',
        'name',
        'link',
    ];

    public function user()
    {
        return $this->belongsToMany(User::class, 'user_report')->withTimestamps();
    }

    public function company()
    {
        return $this->belongsToMany(Company::class, 'company_report')->withTimestamps();
    }
}
