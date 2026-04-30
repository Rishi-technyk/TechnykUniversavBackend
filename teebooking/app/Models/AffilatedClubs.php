<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffilatedClubs extends Model
{
    protected $table = 'AffilatedClubs';

    public function phones()
    {
        return $this->hasMany(AffilatedClubsPhones::class, 'club_id');
    }
    
  
    // public $timestamps = false;

    // protected $fillable = [
    //     'key_name',
    //     'key_value',
    //     'is_active'
    // ];
}
