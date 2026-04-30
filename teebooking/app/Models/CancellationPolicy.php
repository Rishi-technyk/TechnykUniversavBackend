<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancellationPolicy extends Model
{
    use HasFactory;

    protected $guarded = [];

     public function venue()
    {
       return $this->hasOne(VenueMaster::class, 'id', 'venue_id');
    }
}
