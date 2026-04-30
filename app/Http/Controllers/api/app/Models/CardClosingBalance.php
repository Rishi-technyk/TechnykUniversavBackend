<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardClosingBalance extends Model
{
    protected $table = 'CardClosingBalance';

    protected $fillable = [
        'RecNo',
        'MemberID',
        'CardBalance',
        'is_updated',
        'ClosingDate',
    ];
}
