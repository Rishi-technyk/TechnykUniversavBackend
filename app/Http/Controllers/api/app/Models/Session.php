<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $guarded = [];
    public function session()
    {
       return $this->hasOne(sessions::class, 'id', 'session_id');
    }
}
//SELECT `id`, `name`, `status`, `created_at`, `updated_at` FROM `sessions` WHERE 1