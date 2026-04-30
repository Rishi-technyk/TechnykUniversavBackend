<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberReceipt extends Model
{
    protected $table = 'MemberReceipts';

    protected $fillable = [
        'Mem_Id',
        'BillNo',
        'BillAmt',
        'AdditionalAmt',
        'BalanceAmt',
        'ReceivingDate',
        'BillMonth',
        'BillYear',
        'PayStatus',
        'TxnRefrenceNo',
        'BankRefrenceNo',
        'TransactionID',
        'ImportStatus',
        'PaymentResponse',
        'BillMonthYear',
        'PaymentReceived'
    ];

    // Optionally define relationships here if applicable
}
