<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CircularsCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function circulars()
    {
        return $this->hasMany(Circular::class, 'category_id');
    }
}
