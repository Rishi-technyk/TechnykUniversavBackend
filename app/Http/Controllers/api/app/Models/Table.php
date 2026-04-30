<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    protected $table = 'tables';

    protected $fillable = [
        'meal_id',
        'name',
        'status'
    ];

    public function meal()
    {
        return $this->belongsTo(TableMeal::class, 'meal_id');
    }

    public function bookings()
    {
        return $this->hasMany(TableBooking::class, 'table_id');
    }
}
