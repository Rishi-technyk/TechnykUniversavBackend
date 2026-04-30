<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeeBooking extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tee_booking';

    // /**
    //  * The attributes that are mass assignable.
    //  *
    //  * @var array
    //  */
    // protected $fillable = [
    //     'golf_timing_id', 'tee_date', 'tee_day', 'remarks', 'booking_status', 'is_active', 'created_by', 'updated_by'
    // ];
    protected $fillable = ['booking_date', 'golf_start_time', 'golf_end_time', 'is_active', 'created_by', 'updated_by'];

    public function teeSheets()
    {
        return $this->hasMany(TeeSheet::class);
    }
    
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}

?>