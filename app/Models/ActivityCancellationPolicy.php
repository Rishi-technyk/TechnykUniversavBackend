<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityCancellationPolicy extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function facility()
    {
       return $this->hasOne(Facility::class, 'id', 'facility_id');
    }
}
