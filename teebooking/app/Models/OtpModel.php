<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpModel extends Model
{
    protected $table = 'OTP';
    protected $primaryKey = 'RecNo';
  
    public $timestamps = false;
    protected $fillable = [
        'MemberId', 'OTP'
    ];
   
   
    
   
}
