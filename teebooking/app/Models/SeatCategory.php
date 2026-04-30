<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeatCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'price',
        'color',
        'start_row',
        'max_per_booking',
        'seat_type',
        'end_row',
        'layout_id'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function seats()
    {
        return $this->hasMany(Seat::class, 'category_id');
    }
}