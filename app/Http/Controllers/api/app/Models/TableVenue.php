<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TableVenue extends Model
{
    protected $table = 'table_venues';

    protected $fillable = [
        'name',
        'status'
    ];

    public function bookings()
    {
        return $this->hasMany(TableBooking::class, 'venue_id');
    }
}
