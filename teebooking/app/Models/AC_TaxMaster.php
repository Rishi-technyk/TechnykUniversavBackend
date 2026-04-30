<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AC_TaxMaster extends Model
{
    use HasFactory;

    protected $table = 'AC_TaxMaster';

    protected $primaryKey = 'Code';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'CreationDate';
    const UPDATED_AT = 'ModificationDate';
    public $timestamps = true;

    protected $fillable = [
        'TaxName',
        'ValuePercentage',
        'CessPercentage',
        'TaxType',
        'TaxDesc',
        'Usercode'
    ];
}
