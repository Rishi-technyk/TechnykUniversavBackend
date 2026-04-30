<?php

namespace App\Helpers;

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentHelper
{
    public static function createOrder($member, $amount, $type, $moduleId,$prefix)
{
    $txnid = $prefix.'_'.substr(hash('sha256', mt_rand() . microtime()), 0, 20);
\Log::info(config('services.razorpay.key'));
    $api = new Api(
        config('services.razorpay.key'),
        config('services.razorpay.secret')
    );

    $order = $api->order->create([
        'receipt' => $txnid,
        'amount' => (int) ($amount * 100),
        'currency' => 'INR',
        'notes' => [
            'member_id' => $member->MemberID,
            'type' => $type
        ]
    ]);

    // Determine module column
    $columns = [
        'game_booking_id' => null,
        'room_booking_id' => null,
        'banquet_booking_id' => null,
    ];

    if ($type === 'Facility Booking') {
        $columns['game_booking_id'] = $moduleId;
    } elseif ($type === 'Room Booking') {
        $columns['room_booking_id'] = $moduleId;
    } elseif ($type === 'Banquet Booking') {
        $columns['banquet_booking_id'] = $moduleId;
    }

    DB::table('transactions')->insert(array_merge($columns, [
        'member_id' => $member->MemberID,
        'amount' => $amount,
        'order_id' => $txnid,
        'transID' => $order['id'],
        'type' => $type,
        'payment_status' => 'Not Paid',
        'transaction_date' => now(),
        'Importflag'=>1,
        'payment_type'=>'HDFC',
        'entry_come'=>'App',
        'created_at' => now(),
    ]));

    return [
        'order_id' => $order['id'],
        'razorpayKey' => config('services.razorpay.key'),
        'amount' => $amount
    ];
}
public static function verifyPayment($request)
{
    $api = new Api(
        config('services.razorpay.key'),
        config('services.razorpay.secret')
    );

    DB::beginTransaction();

    try {

        $transaction = DB::table('transactions')
            ->where('transID', $request->razorpay_order_id)
            ->lockForUpdate()
            ->first();

        if (!$transaction) {
            throw new \Exception('Transaction not found');
        }

        // prevent duplicate processing
        if ($transaction->payment_status === 'Paid') {
            DB::commit();
            return self::successResponse($transaction, null);
        }

        // if payment id missing → treat as failure
        if (!$request->razorpay_payment_id) {
            DB::table('transactions')
                ->where('id', $transaction->id)
                ->update(['payment_status' => 'Failed']);

            DB::commit();
            return self::failureResponse($transaction);
        }

        $attributes = [
            'razorpay_order_id' => $request->razorpay_order_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature
        ];

        try {
            $api->utility->verifyPaymentSignature($attributes);
        } catch (SignatureVerificationError $e) {
            throw new \Exception('Signature verification failed');
        }

        $payment = $api->payment->fetch($request->razorpay_payment_id);

        if ($payment->status !== 'captured') {
            throw new \Exception('Payment not captured');
        }

        if ($payment->order_id !== $request->razorpay_order_id) {
            throw new \Exception('Order mismatch');
        }

        DB::table('transactions')
            ->where('id', $transaction->id)
            ->update([
                'payment_status' => 'Paid',
                'bank_refrance_no' => $request->razorpay_payment_id,
                'bank_response' => json_encode($payment->toArray()),
                'transaction_date' => now(),
            ]);

        self::updateModuleStatus($transaction);

        DB::commit();

        return self::successResponse($transaction, $payment);

    } catch (\Exception $e) {

        DB::rollBack();

        Log::error('Payment error', ['error' => $e->getMessage()]);

        if (!empty($request->razorpay_order_id)) {
            DB::table('transactions')
                ->where('transID', $request->razorpay_order_id)
                ->update(['payment_status' => 'Failed']);
        }

        return self::failureResponse($transaction ?? null);
    }
}

private static function updateModuleStatus($transaction)
{
    if ($transaction->game_booking_id) {
        DB::table('game_bookings')
            ->where('id', $transaction->game_booking_id)
            ->update([
                'payment_status' => 'Paid',
                'status' => 'Active'
            ]);
    }

    if ($transaction->room_booking_id) {
        DB::table('room_bookings')
            ->where('id', $transaction->room_booking_id)
            ->update(['status' => 'Active']);
    }

    if ($transaction->banquet_booking_id) {
        DB::table('banquet_bookings')
            ->where('id', $transaction->banquet_booking_id)
            ->update(['payment_status' => 'Paid']);
            
              DB::table('banquet_booking_charges')
            ->where('banquet_booking_id', $transaction->banquet_booking_id)
            ->update(['payment_status' => 'Paid']);
    }
    if ($transaction->type === 'Card Recharge') {
    // nothing here (handled in controller)
}
}
private static function successResponse($transaction, $payment)
{
    return [
        'success' => true,
        'data' => [
            'MemberName' => auth()->user()->DisplayName,
            'MemberID' => auth()->user()->MemberID,
            'MemberSCID' => auth()->user()->SC_ID,
            'paid_amount' => $transaction->amount,
            'reference_number' => $payment->id,
            'Status' => 'Success'
        ]
    ];
}
private static function failureResponse($transaction)
{
    return [
        'success' => false,
        'data' => [
            'MemberName' => auth()->user()->DisplayName ?? '',
            'MemberID' => auth()->user()->MemberID ?? '',
            'MemberSCID' => auth()->user()->SC_ID ?? '',
            'paid_amount' => $transaction->amount ?? 0,
            'reference_number' => $transaction->transID,
            'orderId'=>$transaction->order_id,
            'Status' => 'Failed',
            
        ]
    ];
}

}