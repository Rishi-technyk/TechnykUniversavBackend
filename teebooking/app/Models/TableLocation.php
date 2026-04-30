<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableLocation extends Model
{
    use HasFactory;

    // Specify the table name
    protected $table = 'FB_LocationTableLink'; // replace with your actual table name

    // Primary key
    protected $primaryKey = 'Code'; // assuming 'Code' is the PK

    public $incrementing = true;
    protected $keyType = 'int';

    // Disable timestamps if not using Laravel defaults
    public $timestamps = false;

    // Mass assignable fields
    protected $fillable = [
        'LocationName',
        'Description',
        'CreationDate',
        'ModificationDate',
        'UserCode',
    ];

    // Cast dates
    protected $casts = [
        'CreationDate' => 'datetime',
        'ModificationDate' => 'datetime',
    ];

    /**
     * Relationship: A location has many tables
     */
    public function tables()
    {
        return $this->hasMany(TableMaster::class, 'MainLocationCode', 'Code');
    }
}
