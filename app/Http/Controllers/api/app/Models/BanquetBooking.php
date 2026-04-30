<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BanquetBooking extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function occupant()
    {
       return $this->hasOne(OccupantMaster::class, 'id', 'occupant_type');
    }

    public function function()
    {
       return $this->hasOne(FunctionMaster::class, 'id', 'functionType');
    }

    public function transaction()
    {
       return $this->hasOne(Transaction::class, 'id', 'banquet_booking_id');
    }

    public function banquetCharge()
    {
       return $this->hasMany(BanquetBookingCharges::class,'banquet_booking_id', 'id');
    }
}
