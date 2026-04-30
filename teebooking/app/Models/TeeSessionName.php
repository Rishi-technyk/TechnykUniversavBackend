<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeeSessionName extends Model
{
    protected $table = 'tee_session_names';
    protected $fillable = ['name', 'is_active', 'created_by', 'updated_by'];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}


?>