<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MemberProfile;

class MMRRegistration extends Model
{
    use HasFactory;

    public function member()
    {
       return $this->hasOne(MemberProfile::class, 'id', 'member_id');
    }
}
