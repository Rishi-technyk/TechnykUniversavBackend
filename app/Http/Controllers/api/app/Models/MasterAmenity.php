<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Room;

class MasterAmenity extends Model
{
    use HasFactory;
    protected $table = 'master_amenities';

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'role_user', 'amenity_id', 'room_id');
    }
}
