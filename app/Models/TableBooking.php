<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MemberProfile;
use App\Models\TableMeal;
use App\Models\TableVenue;
use App\Models\TableTime;
use App\Models\Table;

class TableBooking extends Model
{
    use HasFactory;
    
       protected $fillable = [
        'member_id',
        'meal_id',
        'venue_id',
        'time_id',
        'table_id',
        'status', // ðŸ”¥ ADD THIS
        'booking_date'
    ];

    public function member()
    {
        return $this->belongsTo(MemberProfile::class, 'member_id');
    }

    public function meal()
    {
        return $this->belongsTo(TableMeal::class, 'meal_id');
    }

    public function venue()
    {
        return $this->belongsTo(TableVenue::class, 'venue_id');
    }

    public function time()
    {
        return $this->belongsTo(TableTime::class, 'time_id');
    }

    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

}
