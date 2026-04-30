<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    protected $fillable = [
        'name',
        'event_id',
        'type',
        'amount',
        'max_per_member',
        'required_fields',
        'image_background',
        'status'
    ];

    protected $casts = [
        'required_fields' => 'array',
        'amount' => 'float'
    ];

    // ðŸ”— Relationships
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
