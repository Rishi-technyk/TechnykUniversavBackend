<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function room()
    {
       return $this->hasOne(RoomCategoryMaster::class, 'id', 'room_category_id');
    }

    public function occupant()
    {
       return $this->hasOne(OccupantMaster::class, 'id', 'occupant_id');
    }

    public function category()
    {
       return $this->hasOne(CategoryMaster::class, 'id', 'category_id');
    }

    public function category_type()
    {
       return $this->hasOne(CategoryType::class, 'id', 'category_type_id');
    }
}
