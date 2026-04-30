<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockRoom extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function room_category()
    {
       return $this->hasOne(RoomCategoryMaster::class, 'id', 'room_category_id');
    }
}
