<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;



class RoomBooking extends Model

{

    use HasFactory;

    

    protected $table = 'room_bookings';

    protected $guarded = [];

public function roomItem()
    {
       return $this->hasMany(RoomBookingItem::class,'booking_id', 'id');
    }


}

