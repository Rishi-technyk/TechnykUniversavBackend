<?php 

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Member;

class NotificationUser extends Model
{
  
      protected $table = 'notification_users'; // or 'notification_user' if you go with Option 2

    protected $fillable = [
        'notification_id',
        'user_id',
        'sent_at',
        'success',
    ];
      public function user()
    {
        return $this->belongsTo(Member::class, 'user_id');
    }
    public function notification()
{
    return $this->belongsTo(Notifications::class, 'notification_id');
}
public function getFormattedSentAtAttribute()
{
    return \Carbon\Carbon::parse($this->sent_at)->format('d M y h.i A');
}
}



?>