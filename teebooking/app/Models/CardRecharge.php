<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardRecharge extends Model
{
    use HasFactory;

    // Specify the table name if it doesn't follow Laravel's naming convention
    protected $table = 'CardRecharge';

    protected $primaryKey = 'CardRechargeNo'; // Set the primary key

    // Disable timestamps if your table doesn't have created_at and updated_at columns
    public $timestamps = false;

    // Define the fillable attributes
    protected $fillable = [
        'CardRechargeNo',
        'RechargeAmt',
        'RechargeDate',
        'PayStatus',
        'TransactionType',
        'PayMode',
        'TxnRefrenceNo',
        'BankRefrenceNo',
        'TransactionID',
        'ImportStatus',
        'PaymentResponse',
        'OrderResponse',
        'WebhookResponse',
    ];
}
