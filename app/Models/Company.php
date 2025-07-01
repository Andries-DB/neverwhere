<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'guid',
        'company',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function sources()
    {
        return $this->hasMany(Source::class);
    }

    public function reports()
    {
        return $this->belongsToMany(Report::class, 'company_report');
    }
}
