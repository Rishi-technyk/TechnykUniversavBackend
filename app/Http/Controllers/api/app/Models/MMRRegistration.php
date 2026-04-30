<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MMRRegistration extends Model
{
    protected $table = 'm_m_r_registrations';

    protected $fillable = [
        'member_id',
        'member_SC_ID',
        'start_date',
        'end_date',
    ];
}
