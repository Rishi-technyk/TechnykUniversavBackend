<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberReceipt extends Model
{
    use HasFactory;

    
    protected $guarded = [];

    protected $table = 'memberreceipts';

      protected $primaryKey = 'BillNo';
}
