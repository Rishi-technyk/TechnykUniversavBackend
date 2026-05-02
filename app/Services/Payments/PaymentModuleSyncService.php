<?php

namespace App\Services\Payments;

use App\Models\BanquetBooking;
use App\Models\BanquetBookingCharges;
use App\Models\Member;
use App\Models\RoomBooking;
use App\Models\RoomBookingItem;
use App\Models\TicketBooking;
use App\Models\Transaction;
use App\Support\Payments\PaymentModule;
use Illuminate\Support\Facades\DB;

class PaymentModuleSyncService
{
    public function markPaid(Transaction $transaction): void
    {
        $module = PaymentModule::fromType($transaction->payment_type ?: $transaction->type);

        switch ($module) {
            case PaymentModule::ROOM_BOOKING:
                if ($transaction->room_booking_id) {
                    RoomBooking::where('id', $transaction->room_booking_id)->update(['status' => 'Active']);
                    RoomBookingItem::where('booking_id', $transaction->room_booking_id)->update(['status' => 'Active']);
                }
                break;

            case PaymentModule::BANQUET_BOOKING:
                if ($transaction->banquet_booking_id) {
                    BanquetBooking::where('id', $transaction->banquet_booking_id)->update([
                        'status' => 'Active',
                        'payment_status' => 'Paid',
                    ]);
                    BanquetBookingCharges::where('banquet_booking_id', $transaction->banquet_booking_id)->update([
                        'status' => 'Active',
                        'payment_status' => 'Paid',
                    ]);
                }
                break;

            case PaymentModule::FACILITY_BOOKING:
                if ($transaction->game_booking_id) {
                    DB::table('game_bookings')
                        ->where('id', $transaction->game_booking_id)
                        ->update([
                            'payment_status' => 'Paid',
                            'status' => 'Active',
                            'updated_at' => now(),
                        ]);

                    DB::table('game_booking_slots')
                        ->where('game_booking_id', $transaction->game_booking_id)
                        ->update([
                            'status' => 'Active',
                            'updated_at' => now(),
                        ]);
                }
                break;

            case PaymentModule::EVENT_BOOKING:
                if ($transaction->module_reference_id) {
                    TicketBooking::where('id', $transaction->module_reference_id)->update(['payment_status' => 'paid']);
                }
                break;

            case PaymentModule::CARD_RECHARGE:
                $this->syncCardRecharge($transaction);
                break;

            case PaymentModule::BILL_PAYMENT:
                $this->syncBillPayment($transaction);
                break;

            default:
                break;
        }
    }

    protected function resolveMemberIdentifiers(Transaction $transaction): array
    {
        $member = Member::where('MemberID', $transaction->member_id)
            ->orWhere('SC_ID', $transaction->member_id)
            ->first();

        return [
            'member_id' => $member->MemberID ?? $transaction->member_id,
            'sc_id' => $member->SC_ID ?? $transaction->member_id,
            'name' => $member->DisplayName ?? '',
        ];
    }

    protected function syncCardRecharge(Transaction $transaction): void
    {
        $member = $this->resolveMemberIdentifiers($transaction);
        $reference = $transaction->gateway_transaction_id
            ?: $transaction->bank_refrance_no
            ?: $transaction->gateway_order_id
            ?: $transaction->transID;
        $cardRecharge = DB::table('CardRecharge')
            ->where('TxnRefrenceNo', $transaction->order_id)
            ->first();

        $alreadyProcessed = $cardRecharge
            && in_array(strtolower((string) $cardRecharge->PayStatus), ['success', 'paid'], true);

        if ($cardRecharge) {
            DB::table('CardRecharge')
                ->where('TxnRefrenceNo', $transaction->order_id)
                ->update([
                    'PayStatus' => 'Success',
                    'BankRefrenceNo' => $reference,
                    'PaymentResponse' => $transaction->raw_response ?: $transaction->bank_response ?: '{}',
                    'WebhookResponse' => $transaction->webhook_response ?: '{}',
                    'OrderResponse' => $transaction->raw_response ?: '{}',
                    'TransactionID' => $reference ?: $transaction->order_id,
                    'ImportStatus' => $transaction->ImportFlag ?? $transaction->Importflag ?? 0,
                    'RechargeDate' => now(),
                ]);
        } else {
            DB::table('CardRecharge')->insert([
                'Card_ID' => $member['sc_id'],
                'RechargeAmt' => (float) $transaction->amount,
                'RechargeDate' => now(),
                'PayStatus' => 'Success',
                'TxnRefrenceNo' => $transaction->order_id,
                'BankRefrenceNo' => $reference,
                'TransactionID' => $reference ?: $transaction->order_id,
                'ImportStatus' => $transaction->ImportFlag ?? $transaction->Importflag ?? 0,
                'PaymentResponse' => $transaction->raw_response ?: $transaction->bank_response ?: '{}',
                'OrderResponse' => $transaction->raw_response ?: '{}',
                'TransactionType' => 'Card Recharge',
                'PayMode' => 'mobile',
                'WebhookResponse' => $transaction->webhook_response ?: '{}',
            ]);
        }

        if ($alreadyProcessed) {
            return;
        }

        $existingBalance = DB::table('cardclosingbalance')
            ->where('MemberID', $member['sc_id'])
            ->first();

        if ($existingBalance) {
            DB::table('cardclosingbalance')
                ->where('MemberID', $member['sc_id'])
                ->update([
                    'CardBalance' => (float) $existingBalance->CardBalance + (float) $transaction->amount,
                    'ClosingDate' => now(),
                ]);
        } else {
            DB::table('cardclosingbalance')->insert([
                'MemberID' => $member['sc_id'],
                'CardBalance' => (float) $transaction->amount,
                'ClosingDate' => now(),
            ]);
        }
    }

    protected function syncBillPayment(Transaction $transaction): void
    {
        $member = $this->resolveMemberIdentifiers($transaction);
        $reference = $transaction->gateway_transaction_id
            ?: $transaction->bank_refrance_no
            ?: $transaction->gateway_order_id
            ?: $transaction->transID;
        $receipt = DB::table('memberreceipts')
            ->where('Mem_Id', $member['sc_id'])
            ->first();

        if (!$receipt) {
            return;
        }

        $alreadyProcessed = strtolower((string) $receipt->PayStatus) === 'success'
            && (string) $receipt->BankRefrenceNo === (string) $reference;

        DB::table('memberreceipts')
            ->where('Mem_Id', $member['sc_id'])
            ->update([
                'PayStatus' => 'Success',
                'BankRefrenceNo' => $reference,
                'PaymentResponse' => $transaction->raw_response ?: $transaction->bank_response ?: '{}',
                'PaymentReceived' => $alreadyProcessed
                    ? (float) $receipt->PaymentReceived
                    : (float) $receipt->PaymentReceived + (float) $transaction->amount,
                'ReceivingDate' => now(),
            ]);
    }
}
