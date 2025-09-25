<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class PinnedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'message_id',
        'title',
        'type',
        'sort_chart',
        '_x',
        '_y',
        '_agg',
        '_order',
        'json',
        'width',
        'display_order',
        'dashboard_id',
        'col_def',
        '_order_dir',
        'last_updated',
        'config',
        'total_row',
        'integrated_chart',
    ];

    protected $casts = [
        'json' => 'array',
        'col_def' => 'array',
        'integrated_chart' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function dashboard()
    {
        return $this->belongsTo(Dashboard::class);
    }
}
