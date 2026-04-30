<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TableTime extends Model
{
    protected $table = 'table_times';

    protected $fillable = [
        'meal_id',
        'time',
        'status'
    ];

    public function meal()
    {
        return $this->belongsTo(TableMeal::class, 'meal_id');
    }

    public function bookings()
    {
        return $this->hasMany(TableBooking::class, 'time_id');
    }
}
