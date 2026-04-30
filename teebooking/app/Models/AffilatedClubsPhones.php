<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffilatedClubsPhones extends Model
{
    protected $table = 'AffilatedClubsPhones';

    public function club()
    {
        return $this->belongsTo(AffilatedClubs::class, 'club_id');
    }
    
  
    // public $timestamps = false;

    // protected $fillable = [
    //     'key_name',
    //     'key_value',
    //     'is_active'
    // ];
}
