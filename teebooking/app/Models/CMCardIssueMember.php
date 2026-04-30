<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CMCardIssueMember extends Model
{
    use HasFactory;

    // Table Name
    protected $table = 'CM_CardIssueMember';

    // Primary Key
    protected $primaryKey = 'RecNo';

    // The PK is auto-incrementing integer
    public $incrementing = true;

    // PK type
    protected $keyType = 'int';

    // If table does NOT have created_at / updated_at
    public $timestamps = false;

    // Fillable fields for mass assignment
    protected $fillable = [
        'MemNo',
        'MainID',
        'MainName',
        'Cardid',
        'CardName',
        'Card_SerialNo',
        'Status',
        'MemberType1',
        'MemberType2',
        'RecievedBy',
        'ReceivedOn',
        'Remark',
        'CreationDate',
        'ModificationDate',
        'UserCode',
    ];
}
