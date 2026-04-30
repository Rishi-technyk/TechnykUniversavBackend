<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSeatingLayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'total_rows',
        'total_columns',
        'seat_size'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
    public function seats()
{
    return $this->hasMany(Seat::class,'layout_id');
}

}