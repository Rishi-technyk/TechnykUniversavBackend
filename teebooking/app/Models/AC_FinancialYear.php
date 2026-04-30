<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AC_FinancialYear extends Model
{
    use HasFactory;

    protected $table = 'AC_FinancialYear';
    protected $primaryKey = 'YearCode';
    public $incrementing = false; // Since YearCode is not auto-increment
    public $timestamps = false;   // We are manually handling CreationDate & ModificationDate

    protected $fillable = [
        'YearCode',
        'DateFrom',
        'DateTo',
        'FinancialYear',
        'CreationDate',
        'ModificationDate',
        'Remarks',
        'DisplayAs',
        'AliasName',
    ];
}
