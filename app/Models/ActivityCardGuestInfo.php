<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityCardGuestInfo extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function slot()
    {
       return $this->hasOne(Slot::class, 'id', 'slot_id');
    }
}