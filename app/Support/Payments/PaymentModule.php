<?php

namespace App\Support\Payments;

class PaymentModule
{
    public const GENERIC = 'generic';
    public const ROOM_BOOKING = 'room_booking';
    public const BANQUET_BOOKING = 'banquet_booking';
    public const FACILITY_BOOKING = 'facility_booking';
    public const EVENT_BOOKING = 'event_booking';
    public const CARD_RECHARGE = 'card_recharge';
    public const BILL_PAYMENT = 'bill_payment';

    public static function fromType(?string $type): string
    {
        $value = strtolower(trim((string) $type));

        return match ($value) {
            'room booking', 'room_booking' => self::ROOM_BOOKING,
            'banquet booking', 'banquet_booking' => self::BANQUET_BOOKING,
            'facility booking', 'facility_booking', 'activity', 'activity_booking' => self::FACILITY_BOOKING,
            'event booking', 'event_booking' => self::EVENT_BOOKING,
            'card recharge', 'card_recharge', 'recharge' => self::CARD_RECHARGE,
            'bill payment', 'bill_payment', 'subscription', 'statement', 'bill' => self::BILL_PAYMENT,
            default => self::GENERIC,
        };
    }

    public static function displayLabel(string $module): string
    {
        return match ($module) {
            self::ROOM_BOOKING => 'Room Booking',
            self::BANQUET_BOOKING => 'Banquet Booking',
            self::FACILITY_BOOKING => 'Facility Booking',
            self::EVENT_BOOKING => 'Event Booking',
            self::CARD_RECHARGE => 'Card Recharge',
            self::BILL_PAYMENT => 'Bill Payment',
            default => 'Payment',
        };
    }

    public static function transactionColumns(string $module, $referenceId): array
    {
        return match ($module) {
            self::ROOM_BOOKING => [
                'room_booking_id' => $referenceId,
                'banquet_booking_id' => null,
                'game_booking_id' => null,
            ],
            self::BANQUET_BOOKING => [
                'room_booking_id' => null,
                'banquet_booking_id' => $referenceId,
                'game_booking_id' => null,
            ],
            self::FACILITY_BOOKING => [
                'room_booking_id' => null,
                'banquet_booking_id' => null,
                'game_booking_id' => $referenceId,
            ],
            default => [
                'room_booking_id' => null,
                'banquet_booking_id' => null,
                'game_booking_id' => null,
            ],
        };
    }
}
