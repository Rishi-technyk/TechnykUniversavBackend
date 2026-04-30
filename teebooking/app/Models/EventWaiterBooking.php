<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventWaiterBooking extends Model
{
    protected $table = 'event_waiter_bookings';

    protected $fillable = [
        'event_id',
        'booking_id',
        'member_id',
        'quantity',
        'price'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'float',
        'total_amount' => 'float'
    ];

    // ðŸŸ¢ Relationship: Each waiter booking belongs to ONE TicketBooking
    public function booking()
    {
        return $this->belongsTo(TicketBooking::class, 'booking_id');
    }

    // ðŸŸ¢ Relationship: Each waiter booking belongs to ONE event
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    // ðŸŸ¢ Relationship: Each waiter booking belongs to ONE member
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
}
