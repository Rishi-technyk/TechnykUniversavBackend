<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MasterAmenity;

class Room extends Model
{
    use HasFactory;
    protected $table = 'rooms';

    public function roomPrices()
    {
        return $this->hasMany(RoomPrice::class, 'room_type_id', 'id');
    }

    public function roomAmenity()
    {
        return $this->belongsToMany(MasterAmenity::class, 'room_amenities', 'room_id', 'amenity_id');
    }
}
