<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $fillable = [
        'booking_id',
        'ticket_type',
        'ticket_id',
        'name',
        'mobile',
        'email',
        'relation',
        'qr_code',
        'entry_status',
        'food_status',
        'consumed_alcohol',
        'consumed_non_alcohol',
        'amount',
        'is_complimentary',
        'entry_at',
        'food_at'
    ];

    protected $casts = [
        'entry_status' => 'boolean',
        'food_status' => 'boolean',
        'entry_at' => 'datetime',
        'food_at' => 'datetime'
    ];

    // ðŸ”— Relationships
    public function booking()
    {
        return $this->belongsTo(TicketBooking::class, 'booking_id');
    }

public function event()
{
    return $this->belongsTo(Event::class);
}

public function ticketType()
{
    return $this->belongsTo(TicketType::class, 'ticket_id', 'id');
}

    // âœ… Helpers
    public function canEnter()
    {
        return !$this->entry_status;
    }

    public function canTakeFood()
    {
        return $this->entry_status && !$this->food_status;
    }
    
}
