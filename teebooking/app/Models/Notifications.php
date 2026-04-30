<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $guarded = []; // ✅ fixed typo

    public function notificationUsers()
    {
        return $this->hasMany(NotificationUser::class, 'notification_id');
    }
    public function user()
{
    return $this->belongsTo(Member::class, 'user_id');
}
}
