<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasUlids;
    protected $guarded = [];

    protected $casts = [
        'checked_at' => 'datetime',
        'is_canceled' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(Event::class);
    }

    public function event()
    {
        return $this->belongsTo(User::class);
    }
}
