<?php

namespace App\Support\Payments;

class PaymentStatus
{
    public const INITIATED = 'initiated';
    public const PENDING = 'pending';
    public const PROCESSING = 'processing';
    public const SUCCESS = 'Paid';
    public const FAILED = 'Failed';
    public const CANCELLED = 'cancelled';
    public const REFUNDED = 'refunded';

    public static function all(): array
    {
        return [
            self::INITIATED,
            self::PENDING,
            self::PROCESSING,
            self::SUCCESS,
            self::FAILED,
            self::CANCELLED,
            self::REFUNDED,
        ];
    }

    public static function toLegacy(string $status): string
    {
        return match ($status) {
            self::SUCCESS => 'Paid',
            self::FAILED, self::CANCELLED => 'Failed',
            self::REFUNDED => 'Refunded',
            default => 'Not Paid',
        };
    }

    public static function isSuccessful(?string $status): bool
    {
        return $status === self::SUCCESS;
    }

    public static function isTerminal(?string $status): bool
    {
        return in_array($status, [self::SUCCESS, self::FAILED, self::CANCELLED, self::REFUNDED], true);
    }
}
