<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    public function meal()
    {
        return $this->belongsTo(TableMeal::class, 'meal_id');
    }
}
