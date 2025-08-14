<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dashboard extends Model
{
    protected $fillable = [
        'guid',
        'user_id',
        'name',
        'default'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pinned_graphs()
    {
        return $this->hasMany(PinnedGraph::class)->orderBy('display_order', 'asc');
    }

    public function pinned_tables()
    {
        return $this->hasMany(PinnedTable::class)->orderBy('display_order', 'asc');
    }

    public function pinned_items()
    {
        return $this->hasMany(PinnedItem::class)->orderBy('display_order', 'asc');
    }
}
