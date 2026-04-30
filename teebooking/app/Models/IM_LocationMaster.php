<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IM_LocationMaster extends Model
{
    protected $table = 'IM_LocationMaster';
    protected $primaryKey = 'code';
    public $timestamps = false;

    protected $fillable = [
        'Substore',
        'LocationName',
        'Type',
        'Displayas',
        'CreationDate',
        'Modificationdate',
        'Usercode',
        'SCApplicable',
        'VendorID'
    ];

    public function users()
    {
        return $this->hasMany(AC_UserMaster::class, 'LocationCode', 'LocationCode');
    }
}
