<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IM_SubGroupMaster extends Model
{
    use HasFactory;

    protected $table = 'IM_SubGroupMaster';   // Table name

    protected $primaryKey = 'Code';           // Primary key

    public $timestamps = false;               // Table doesn't use created_at/updated_at

    protected $fillable = [
        'GroupCode',
        'SubgroupName',
        'SubgroupDisplyas',
        'CreationDate',
        'Modificationdate',
        'Usercode',
        'GSTTaxCode',
    ];

    protected $casts = [
        'CreationDate'     => 'datetime',
        'Modificationdate' => 'datetime',
    ];

    // 🔗 Relationship: SubGroup belongs to a Group
    public function group()
    {
        return $this->belongsTo(IM_GroupMaster::class, 'GroupCode', 'Code');
    }
    
}
