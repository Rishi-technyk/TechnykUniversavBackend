<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomBookingTemp extends Model
{
    use HasFactory;
    protected $table = 'room_bookings_temp';
    protected $fillable = ['id', 'member_id', 'booking_number', 'room_id', 'checkin', 'checkout', 'occupant_type_id', 'total_nights', 'total_rooms', 'occupant_type_1', 'occupant_name_1', 'occupant_type_2', 'occupant_name_2', 'price', 'gst', 'status',];
}
