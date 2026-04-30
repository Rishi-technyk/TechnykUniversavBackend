<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilitySlot extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function facility()
    {
       return $this->hasOne(Facility::class, 'id', 'facility_id');
    }

    public function slot()
    {
       return $this->hasOne(Slot::class, 'id', 'slot_id');
    }

    public function session()
    {
       return $this->hasOne(Session::class, 'id', 'session_id');
    }
}
