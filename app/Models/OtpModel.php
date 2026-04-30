<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpModel extends Model
{
    protected $table = 'otp';
    
    protected $primaryKey = 'RecNo';
  
    public $timestamps = false;
    protected $fillable = [
        'MemberId', 'OTP'
    ];
   
   
    
   
}
