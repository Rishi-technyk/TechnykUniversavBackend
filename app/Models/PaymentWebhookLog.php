<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentWebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'gateway_name',
        'gateway_slug',
        'transaction_id',
        'gateway_order_id',
        'gateway_transaction_id',
        'gateway_event_id',
        'gateway_event_type',
        'status',
        'signature_valid',
        'payload',
        'headers',
        'response',
        'processed_at',
    ];

    protected $casts = [
        'signature_valid' => 'boolean',
        'payload' => 'array',
        'headers' => 'array',
        'response' => 'array',
        'processed_at' => 'datetime',
    ];
}
