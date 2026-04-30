<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BanquetBookingCharges extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function venue()
    {
       return $this->hasOne(VenueMaster::class, 'id', 'vanue_id');
    }

    public function session()
    {
       return $this->hasOne(Session::class, 'id', 'session_id');
    }

    public function banquet()
    {
       return $this->hasOne(BanquetBooking::class, 'id', 'banquet_booking_id');
    }
}


// <?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class BanquetBookingCharges extends Model
// {
//     use HasFactory;

//     protected $guarded = [];

//     public function venue()
//     {
//       return $this->hasOne(VenueMaster::class, 'id', 'vanue_id');
//     }

//     public function banquet()
//     {
//       return $this->hasOne(BanquetBooking::class, 'id', 'banquet_booking_id');
//     }
//     public function banquet()
//     {
//       return $this->hasOne(BanquetBooking::class, 'id', 'banquet_booking_id');
//     }
// }
