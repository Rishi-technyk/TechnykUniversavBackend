<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeeSession extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tee_sessions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'session_name', 'start_time', 'end_time',
        'is_active', 'created_by', 'updated_by'
    ];
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
    
    public function sessionCategories()
    {
        return $this->hasMany(SessionCategory::class, 'tee_session_id');
    }
    
    public function categories()
    {
        return $this->belongsToMany(SessionCategory::class, 'tee_session_categories', 'tee_session_id', 'category_type_Code');
    }
}

?>