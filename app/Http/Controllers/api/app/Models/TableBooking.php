<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TableBooking extends Model
{
    protected $table = 'table_bookings';

    protected $fillable = [
        'member_id',
        'meal_id',
        'venue_id',
        'time_id',
        'table_id',
        'booking_date',
        'status'
    ];

    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function meal()
    {
        return $this->belongsTo(TableMeal::class, 'meal_id');
    }

    public function time()
    {
        return $this->belongsTo(TableTime::class, 'time_id');
    }

    public function venue()
    {
        return $this->belongsTo(TableVenue::class, 'venue_id');
    }
}
