<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MMRRegistrationSetting extends Model
{
    protected $table = 'm_m_r_registration_settings';

    protected $fillable = [
        'start_date',
        'end_date',
    ];
}
