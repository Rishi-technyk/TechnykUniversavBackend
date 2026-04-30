<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberAccountLedger extends Model
{
    protected $table = 'MemberAccountLedger'; // Assuming the table name is 'MemberAccountLedger'
    protected $primaryKey = 'id';
    public $timestamps = false;

    // Other necessary fields
}