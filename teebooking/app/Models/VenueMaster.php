<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VenueMaster extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function group()
    {
       return $this->hasOne(Grouping::class, 'id', 'group_id');
    }
}
