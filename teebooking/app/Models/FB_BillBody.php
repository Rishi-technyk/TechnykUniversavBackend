<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FB_BillBody extends Model
{
    use HasFactory;

    protected $table = 'FB_BillBody';

    protected $fillable = [
       'KOTNo',
        'BillNo',
        'MemberID',
        'WaiterCode',
        'TableCode',
        'PAX',
        'ModeOfPayment',
        'OpeningBalance',
        'ClosingBalance',
        'CreationDate',
        'ModificationDate',
        'UserCode',
        'LocationCode',
        'YearCode',
    ];

    public $timestamps = true;

    protected $casts = [
        'CreationDate' => 'datetime',
        'ModificationDate' => 'datetime',
    ];
}
