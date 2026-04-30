<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomPrice extends Model
{
    use HasFactory;
    protected $table = 'room_prices';
    protected $fillable = ['room_type_id', 'member_category_id', 'occupant_type_id', 'price', 'gst'];


    public function room()
    {
        return $this->belongsTo(Room::class, 'room_type_id', 'id');
    }

    public function occupants()
    {
        return $this->hasOne(OccupantType::class, 'id', 'occupant_type_id');
    }

    public function categoryMaster()
    {
        return $this->hasOne(CategoryMaster::class, 'code', 'member_category_id');
    }
}
