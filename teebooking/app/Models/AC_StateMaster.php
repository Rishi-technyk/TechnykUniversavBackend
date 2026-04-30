<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AC_StateMaster extends Model
{
    use HasFactory;

    // Table name
    protected $table = 'AC_StateMaster';

    // Primary key
    protected $primaryKey = 'StateCode';

    // The primary key is not necessarily auto-incrementing
    public $incrementing = true;

    // Primary key type
    protected $keyType = 'int';

    // Disable timestamps (no created_at / updated_at)
    public $timestamps = false;

    // Mass assignable columns
    protected $fillable = [
        'StateCode',
        'StateName',
        'DisplayAs',
        'CountryCode',
        'CreationDate',
        'ModificationDate',
        'UserCode',
    ];
}
