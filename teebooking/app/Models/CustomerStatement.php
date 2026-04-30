<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerStatement extends Model
{
    protected $table = 'CustomerStatement';


    protected $fillable = [
        'RecNo',
        'BillNo',
        'BillDate',
        'MemberId',
        'Amount',
        'LocationName',
        'PayMode',
        'LocationCode',
        'YearCode',
        'Balance',
        'SNo',
        'TimeStamp',
    ];
  
}
