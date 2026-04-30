<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameBookingGuest extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function occupant()
    {
       return $this->hasOne(OccupantMaster::class, 'id', 'occupant_id');
    }

    public function slot()
    {
       return $this->hasOne(Slot::class, 'id', 'slot_id');
    }
}
