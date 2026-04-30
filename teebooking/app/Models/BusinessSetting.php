<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    protected $table = 'tee_business_settings';
    
  
    // public $timestamps = false;

    protected $fillable = [
        'key_name',
        'key_value',
        'is_active'
    ];
}
