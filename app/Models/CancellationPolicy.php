<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\VenueMaster;

class CancellationPolicy extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function venue()
    {
        return $this->belongsTo(VenueMaster::class, 'venue_id', 'id');
    }
}
