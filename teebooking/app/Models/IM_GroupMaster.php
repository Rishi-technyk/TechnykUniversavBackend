<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IM_GroupMaster extends Model
{
    use HasFactory;

    protected $table = 'IM_GroupMaster';   // Table name

    protected $primaryKey = 'Code';        // Primary key

    public $timestamps = false;            // We’ll manage dates manually

    protected $fillable = [
        'MainGroupCode',
        'GroupName',
        'GroupDisplyas',
        'CreationDate',
        'ModificationDate',
        'Usercode',
    ];
    

    protected $casts = [
        'CreationDate'     => 'datetime',
        'ModificationDate' => 'datetime',
    ];
     public function locationGroupLinks()
    {
        return $this->hasMany(FB_LocationGroupLink::class, 'GroupCode', 'Code');
    }
    public function subGroups()
{
    return $this->hasMany(IM_SubGroupMaster::class, 'GroupCode', 'Code');
}
}
