<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeatLock extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'seat_id',
        'user_id',
        'locked_until'
    ];

    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}