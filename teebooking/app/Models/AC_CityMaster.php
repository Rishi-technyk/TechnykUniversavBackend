<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AC_CityMaster extends Model
{
    use HasFactory;

    // Define the table name
    protected $table = 'AC_CityMaster';

    // Define the primary key columns (composite key behavior handled manually)
    protected $primaryKey = 'CityCode';

    // Disable auto-increment since composite key includes CityName + StateCode
    public $incrementing = false;

    // If primary key is not an integer, set this to false
    protected $keyType = 'string';

    // Allow mass assignment
    protected $fillable = [
        'CityCode',
        'CityName',
        'StateCode',
        'CreationDate',
        'ModificationDate',
        'UserCode',
    ];

    // Disable timestamps (we're manually handling CreationDate and ModificationDate)
    public $timestamps = false;

    /**
     * Relationship: Each city belongs to one state.
     */
    public function state()
    {
        return $this->belongsTo(AC_StateMaster::class, 'StateCode', 'StateCode');
    }

    /**
     * Automatically set CreationDate and ModificationDate when creating/updating.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->CreationDate = now();
            $model->ModificationDate = now();
        });

        static::updating(function ($model) {
            $model->ModificationDate = now();
        });
    }
}
