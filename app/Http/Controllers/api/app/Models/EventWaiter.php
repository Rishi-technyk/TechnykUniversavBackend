<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventWaiter extends Model
{
    protected $table = 'event_waiters';

    protected $fillable = [
        'max_waiters',
        'event_id',
        'max_waiters_per_member',
        'waiter_cost',
        'status'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
