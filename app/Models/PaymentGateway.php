<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'environment',
        'is_active',
        'merchant_id',
        'key_id',
        'key_secret',
        'salt',
        'webhook_secret',
        'app_id',
        'encryption_key',
        'status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'merchant_id' => 'encrypted',
        'key_id' => 'encrypted',
        'key_secret' => 'encrypted',
        'salt' => 'encrypted',
        'webhook_secret' => 'encrypted',
        'app_id' => 'encrypted',
        'encryption_key' => 'encrypted',
    ];

    protected static function booted(): void
    {
        static::saved(function (PaymentGateway $gateway) {
            if ($gateway->is_active) {
                static::whereKeyNot($gateway->id)->update(['is_active' => false]);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }
}
