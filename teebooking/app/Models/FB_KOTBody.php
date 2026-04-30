<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FB_KOTBody extends Model
{
    use HasFactory;

    protected $table = 'FB_KOTBody'; // exact table name

    protected $fillable = [
        'KOTNo',
        'Itemcode',
        'ItemName',
        'OpenItem',
        'UnitCode',
        'Qty',
        'ActualQty',
        'Rate',
        'SchemeRate',
        'Amount',
        'DiscountPer',
        'TaxType',
        'TaxPer',
        'SCPer',
        'CreationDate',
        'ModificationDate',
        'UserCode',
        'LocationCode',
        'YearCode',
        'sKotStatus',
        'KotID',
        'ItemStatus',
        'Remarks',
    ];

    public $timestamps = true;

    protected $casts = [
        'Qty' => 'decimal:2',
        'ActualQty' => 'decimal:2',
        'Rate' => 'decimal:2',
        'SchemeRate' => 'decimal:2',
        'Amount' => 'decimal:2',
        'DiscountPer' => 'decimal:2',
        'TaxPer' => 'decimal:2',
        'SCPer' => 'decimal:2',
        'CreationDate' => 'datetime',
        'ModificationDate' => 'datetime',
    ];
}
