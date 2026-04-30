<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IM_MainGroupMaster extends Model
{
    protected $table = 'IM_MainGroupMaster';   // Table name

    protected $primaryKey = 'Code';            // Primary key column

    public $incrementing = false;              // Code is not auto-increment
    protected $keyType = 'string';             // Key type is string (VARCHAR)

    public $timestamps = false;                // We’ll manage dates manually

    protected $fillable = [
        'Code',
        'MaingroupName',
        'MaingroupDisplayAs',
        'CreationDate',
        'ModificationDate',
        'Usercode',
        'Prefix',
    ];

    protected $casts = [
        'CreationDate'     => 'datetime',
        'ModificationDate' => 'datetime',
    ];

    // 🔹 Relation with IM_GroupMaster (MainGroup has many Groups)
    public function groupMasters()
    {
        return $this->hasMany(IM_GroupMaster::class, 'MainGroupCode', 'Code');
    }
}
