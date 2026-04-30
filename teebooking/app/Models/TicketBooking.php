<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketBooking extends Model
{
    protected $fillable = [
        'event_id',
        'member_id',
        'booking_no',
        'total_amount',
        'payment_status',
        'razorpay_order_id',
        'razorpay_payment_id',
        'payment_type',
        'admin_id',
        'Name',
        'Mobile'
    ];

    protected $casts = [
        'total_amount' => 'float'
    ];

    // ðŸ”— Relationships
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class)
        ->select('id','Email','DisplayName','SC_ID','MemberId');
    }

    public function participants()
    {
        return $this->hasMany(Participant::class, 'booking_id');
    }
   public function waiterBooking()
{
    return $this->hasOne(EventWaiterBooking::class, 'booking_id');
}

   public function seats()
{
    return $this->hasMany(EventBookingSeat::class, 'booking_id');
}
   public function admin()
{
    return $this->belongsTo(Member::class, 'admin_id');
}

}
