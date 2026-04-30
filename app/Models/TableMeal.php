<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableMeal extends Model
{
    use HasFactory;
    
    public function times()
{
    return $this->hasMany(TableTime::class, 'meal_id', 'id');
}
}
