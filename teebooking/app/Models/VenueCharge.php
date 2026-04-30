<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VenueCharge extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function venue()
    {
       return $this->hasOne(VenueMaster::class, 'id', 'venue_id');
    }

    public function session()
    {
       return $this->hasOne(Session::class, 'id', 'session_id');
    }

    public function occupant()
    {
       return $this->hasOne(OccupantMaster::class, 'id', 'occupant_id');
    }
        protected $casts = [
        'min_pax' => 'array',
        'max_pax' => 'array',
        'rate'    => 'array',
    ];
}
