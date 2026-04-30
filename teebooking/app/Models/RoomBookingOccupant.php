<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomBookingOccupant extends Model
{
    use HasFactory;

    protected $table = 'room_booking_occupants';
    protected $fillable = ['booking_id', 'occupant_type', 'name'];
}
