<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AC_ModeOfPayment extends Model
{
    use HasFactory;

    protected $table = 'AC_ModeOfPayment';
    
    // Primary key
    protected $primaryKey = 'Code';
    
    public $incrementing = true;
    protected $keyType = 'int';

    // Timestamps
    const CREATED_AT = 'CreationDate';
    const UPDATED_AT = 'ModificationDate';
    public $timestamps = true;

    // Mass assignable attributes
    protected $fillable = [
        'ModeDesc',
        'ComPer',
        'Location',
        'Status',
        'UserCode'
    ];
}
