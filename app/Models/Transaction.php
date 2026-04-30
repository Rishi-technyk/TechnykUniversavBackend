<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $guarded = [];

    protected $casts = [
        'raw_response' => 'array',
        'webhook_response' => 'array',
        'processed_at' => 'datetime',
        'transaction_date' => 'datetime',
    ];
}
