<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AC_UserMaster extends Model
{
    // Table name
    protected $table = 'AC_UserMaster';

    // Primary key
    protected $primaryKey = 'UserCode';

    // If primary key is not auto-incrementing
    public $incrementing = true;

    // Primary key type
    protected $keyType = 'int';

    // Disable timestamps if your table does not have created_at / updated_at
    public $timestamps = false;

    // Fillable fields for mass assignment
    protected $fillable = [
        'UserName',
        'UPassword',
        'TypeCode',
        'TitleCode',
        'DesignationCode',
        'FName',
        'MName',
        'LName',
        'AUserCode',
        'CreationDate',
        'ModificationDate',
        'UserStatus',
        'device_id',
        'device_app_version',
        'has_notification_permission',
        'access_token',
        'Mobile',        // added Mobile
        'LocationCode',  // added LocationCode
        'OTP'            // added OTP field
    ];

    // Hidden fields (optional)
    protected $hidden = [
        'UPassword',
        'access_token',
    ];

    // Define relation to locations (if needed)
    public function location()
    {
        return $this->belongsTo(IM_LocationMaster::class, 'LocationCode', 'LocationCode');
    }
}
