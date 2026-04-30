<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameBooking extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function facility()
    {
       return $this->hasOne(Facility::class, 'id', 'facility_id');
    }

    public function game_type()
    {
       return $this->hasOne(GameType::class, 'id', 'game_type_id');
    }

    public function session()
    {
       return $this->hasOne(Session::class, 'id', 'session_id');
    }

    public function game_item()
    {
       return $this->hasMany(GameBookingSlot::class, 'game_booking_id', 'id');
    }
}
