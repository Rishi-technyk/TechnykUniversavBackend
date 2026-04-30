<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomChargesMaster extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function room_category()
    {
       return $this->hasOne(RoomCategoryMaster::class, 'id', 'room_category_id');
    }

    public function category()
    {
       return $this->hasOne(CategoryMaster::class, 'Code', 'category_id');
    }

    public function categoryType()
    {
       return $this->hasOne(CategoryType::class, 'Code', 'category_type_id');
    }

    public function occupant()
    {
       return $this->hasOne(OccupantMaster::class, 'id', 'occupant_type_id');
    }
}
