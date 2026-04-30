<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OccupantType extends Model
{
    use HasFactory;
    protected $table = 'occupant_types';

    public function price()
    {
        return $this->belongsTo(RoomPrice::class, 'id', 'occupant_type_id');
    }

    public function priceByOccupantType()
    {
        return $this->hasOne(RoomPrice::class, 'room_type_id ', 'occupant_type_id');
    }
}
