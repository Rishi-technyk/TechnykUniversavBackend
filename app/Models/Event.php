<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'name',
        'event_date',
        'booking_start_at',
        'booking_end_at',
        'location',
        'max_per_member_tickets',
        'complimentary_age',
        'max_tickets',
        'banner',
        'status'
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'booking_start_at' => 'datetime',
        'booking_end_at' => 'datetime',
    ];

    // ðŸ”— Relationships
    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class);
    }

    public function bookings()
    {
        return $this->hasMany(TicketBooking::class);
    }

    // âœ… Scope
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
     public function waiter()
    {
        return $this->hasOne(EventWaiter::class);
    }
      public function seatingLayout()
    {
        return $this->hasOne(EventSeatingLayout::class);
    }

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    public function categories()
    {
        return $this->hasMany(SeatCategory::class);
    }

}
