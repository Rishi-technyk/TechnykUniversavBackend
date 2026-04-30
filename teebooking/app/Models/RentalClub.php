<?php
// app/RentalClub.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalClub extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tee_rental_clubs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'is_active', 'created_by', 'updated_by'
    ];
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}


?>