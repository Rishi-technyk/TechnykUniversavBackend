<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DummyMemberFinanceSeeder extends Seeder
{
    private const MEMBER_SC_ID = 'SL-0001';
    private const MEMBER_CREDIT_LIMIT = 15000.00;
    private const CARD_CLOSING_BALANCE = 2845.00;
    private const CARD_CLOSING_DATE = '2026-04-22';

    public function run(): void
    {
        $member = DB::table('memberprofile')
            ->where('SC_ID', self::MEMBER_SC_ID)
            ->first();

        if (!$member) {
            throw new RuntimeException('Member with SC_ID ' . self::MEMBER_SC_ID . ' was not found.');
        }

        DB::transaction(function () use ($member) {
            $this->seedMemberProfile($member->id);
            $this->seedReceipt();
            $this->seedCardClosingBalance();
            $this->seedCustomerStatements();
            $this->seedLedgerCredits();
        });
    }

    private function seedMemberProfile(int $memberId): void
    {
        DB::table('memberprofile')
            ->where('id', $memberId)
            ->update([
                'credit_limit' => self::MEMBER_CREDIT_LIMIT,
                'updated_at' => now(),
            ]);
    }

    private function seedReceipt(): void
    {
        DB::table('memberreceipts')->updateOrInsert(
            [
                'Mem_Id' => self::MEMBER_SC_ID,
                'BillNo' => 2026,
            ],
            [
                'BillAmt' => 3326.17,
                'AdditionalAmt' => 0,
                'BalanceAmt' => 1526.17,
                'ReceivingDate' => '2026-04-23 10:30:00',
                'BillMonth' => 3,
                'BillYear' => 2026,
                'PayStatus' => 'partial',
                'TxnRefrenceNo' => 'DMY-TXN-SL0001-2026',
                'BankRefrenceNo' => 'DMY-BANK-2026',
                'TransactionID' => 'DMY-RZP-2026',
                'ImportStatus' => 0,
                'PaymentResponse' => 'Dummy seeded receipt payment response',
                'BillMonthYear' => 'Mar2026',
                'PaidFrom' => 'Mobile App',
                'PaymentReceived' => 1800.00,
                'updated_at' => now(),
            ]
        );
    }

    private function seedCardClosingBalance(): void
    {
        DB::table('cardclosingbalance')->updateOrInsert(
            ['MemberID' => self::MEMBER_SC_ID],
            [
                'CardBalance' => self::CARD_CLOSING_BALANCE,
                'ClosingDate' => self::CARD_CLOSING_DATE,
            ]
        );
    }

    private function seedCustomerStatements(): void
    {
        $rows = [
            [
                'RecNo' => 990001,
                'BillNo' => 880001,
                'BillDate' => '2026-03-29 10:15:00',
                'Amount' => 3000.00,
                'LocationName' => 'Card Recharge Desk',
                'PayMode' => 'Online',
                'LocationCode' => 101,
                'YearCode' => 2026,
                'Balance' => 3000.00,
                'SNo' => 1,
            ],
            [
                'RecNo' => 990002,
                'BillNo' => 880002,
                'BillDate' => '2026-04-01 11:15:00',
                'Amount' => -425.00,
                'LocationName' => 'Golf Lounge',
                'PayMode' => 'Member Card',
                'LocationCode' => 201,
                'YearCode' => 2026,
                'Balance' => 2575.00,
                'SNo' => 2,
            ],
            [
                'RecNo' => 990003,
                'BillNo' => 880003,
                'BillDate' => '2026-04-03 12:15:00',
                'Amount' => -180.00,
                'LocationName' => 'Coffee Shop',
                'PayMode' => 'Member Card',
                'LocationCode' => 202,
                'YearCode' => 2026,
                'Balance' => 2395.00,
                'SNo' => 3,
            ],
            [
                'RecNo' => 990004,
                'BillNo' => 880004,
                'BillDate' => '2026-04-06 13:15:00',
                'Amount' => 2000.00,
                'LocationName' => 'Card Recharge Desk',
                'PayMode' => 'UPI',
                'LocationCode' => 101,
                'YearCode' => 2026,
                'Balance' => 4395.00,
                'SNo' => 4,
            ],
            [
                'RecNo' => 990005,
                'BillNo' => 880005,
                'BillDate' => '2026-04-09 14:15:00',
                'Amount' => -640.00,
                'LocationName' => 'Banquet Snack Bar',
                'PayMode' => 'Member Card',
                'LocationCode' => 203,
                'YearCode' => 2026,
                'Balance' => 3755.00,
                'SNo' => 5,
            ],
            [
                'RecNo' => 990006,
                'BillNo' => 880006,
                'BillDate' => '2026-04-12 10:15:00',
                'Amount' => -320.00,
                'LocationName' => 'Pro Shop',
                'PayMode' => 'Member Card',
                'LocationCode' => 204,
                'YearCode' => 2026,
                'Balance' => 3435.00,
                'SNo' => 6,
            ],
            [
                'RecNo' => 990007,
                'BillNo' => 880007,
                'BillDate' => '2026-04-15 11:15:00',
                'Amount' => 1500.00,
                'LocationName' => 'Card Recharge Desk',
                'PayMode' => 'Cash',
                'LocationCode' => 101,
                'YearCode' => 2026,
                'Balance' => 4935.00,
                'SNo' => 7,
            ],
            [
                'RecNo' => 990008,
                'BillNo' => 880008,
                'BillDate' => '2026-04-18 12:15:00',
                'Amount' => -885.00,
                'LocationName' => 'Family Dining',
                'PayMode' => 'Member Card',
                'LocationCode' => 205,
                'YearCode' => 2026,
                'Balance' => 4050.00,
                'SNo' => 8,
            ],
            [
                'RecNo' => 990009,
                'BillNo' => 880009,
                'BillDate' => '2026-04-20 13:15:00',
                'Amount' => -455.00,
                'LocationName' => 'Sports Bar',
                'PayMode' => 'Member Card',
                'LocationCode' => 206,
                'YearCode' => 2026,
                'Balance' => 3595.00,
                'SNo' => 9,
            ],
            [
                'RecNo' => 990010,
                'BillNo' => 880010,
                'BillDate' => '2026-04-22 14:15:00',
                'Amount' => -750.00,
                'LocationName' => 'Club Restaurant',
                'PayMode' => 'Member Card',
                'LocationCode' => 207,
                'YearCode' => 2026,
                'Balance' => self::CARD_CLOSING_BALANCE,
                'SNo' => 10,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('customerstatement')->updateOrInsert(
                [
                    'MemberId' => self::MEMBER_SC_ID,
                    'BillNo' => $row['BillNo'],
                ],
                array_merge($row, [
                    'MemberId' => self::MEMBER_SC_ID,
                    'TimeStamp' => Carbon::parse($row['BillDate'])->toDateTimeString(),
                ])
            );
        }
    }

    private function seedLedgerCredits(): void
    {
        $rows = [
            [
                'voucher_no' => 'DMY-PMT-001',
                'voucher_date' => '2026-04-05',
                'particulars' => 'Member Bill Payment',
                'credit_amt' => 500.00,
                'debit_amt' => 0.00,
                'narrations' => 'Dummy online payment posted against March bill',
            ],
            [
                'voucher_no' => 'DMY-PMT-002',
                'voucher_date' => '2026-04-14',
                'particulars' => 'Member Bill Payment',
                'credit_amt' => 700.00,
                'debit_amt' => 0.00,
                'narrations' => 'Dummy UPI payment posted against March bill',
            ],
            [
                'voucher_no' => 'DMY-PMT-003',
                'voucher_date' => '2026-04-23',
                'particulars' => 'Member Bill Payment',
                'credit_amt' => 600.00,
                'debit_amt' => 0.00,
                'narrations' => 'Dummy cash payment posted against March bill',
            ],
        ];

        foreach ($rows as $row) {
            DB::table('MemberAccountLedger')->updateOrInsert(
                [
                    'member_id' => self::MEMBER_SC_ID,
                    'voucher_no' => $row['voucher_no'],
                ],
                array_merge($row, [
                    'member_id' => self::MEMBER_SC_ID,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
