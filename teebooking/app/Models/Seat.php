<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'layout_id',
        'category_id',
        'row_label',
        'seat_number',
        'seat_code',
        'pos_x',
        'pos_y',
        'status'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function layout()
    {
        return $this->belongsTo(EventSeatingLayout::class, 'layout_id');
    }

    public function category()
    {
        return $this->belongsTo(SeatCategory::class, 'category_id');
    }

    public function bookings()
    {
        return $this->hasMany(BookingSeat::class, 'seat_id');
    }

}