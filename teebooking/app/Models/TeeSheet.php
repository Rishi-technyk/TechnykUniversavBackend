<?php 

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TeeSheet extends Model
{
  
    protected $table = 'tee_sheet';

    // protected $fillable = [
    //     'tee_booking_id', 'tee_time', 'is_locked_by_admin', 'is_active', 'created_at', 'created_by', 'updated_at', 'updated_by'
    // ];
    
    protected $fillable = ['tee_time', 'tee_booking_id', 'session_id', 'slot_interval', 'tee_off_hole_id', 'is_active', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    public function teeBooking()
    {
        return $this->belongsTo(TeeBooking::class);
    }
    public function teeBookingDetails()
    {
        return $this->hasMany(TeeBookingDetails::class, 'tee_sheet_id', 'id');
    }

    public function teeHole()
    {
        return $this->belongsTo(TeeHole::class, 'tee_off_hole_id');
    }

    public function scopeActive($query)
    {
        return $query->where('tee_sheet.is_active', 1);
    }

    // Define the relationship with the Session model
    public function session()
    {
        return $this->belongsTo(TeeSession::class, 'session_id');
    }
}



?>