<?php

namespace App\Models;
use App\Models\TeeSessionName;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeeSessionTime extends Model
{
    protected $table = 'tee_session_time';
    
    protected $fillable = [
        'session_name_id',
        'start_time',
        'end_time',
        'is_active',
        'created_by',
        'updated_by'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
    
    public function sessionName()
    {
        return $this->belongsTo(TeeSessionName::class, 'session_name_id');
    }
}