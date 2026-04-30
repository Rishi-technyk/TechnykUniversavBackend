<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TeeSlotInterval extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tee_tee_slot_intervals';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tee_sheet_id', 'session_name_id', 'session_time_id', 'slot_interval', 'tee_off_hole', 'is_active', 'created_at', 'created_by', 'updated_at', 'updated_by'
    ];
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
