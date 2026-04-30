<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableMaster extends Model
{
    use HasFactory;

    // Specify the table name if it doesn't follow Laravel's naming convention
    protected $table = 'FB_TableMaster';

    // Primary key
    protected $primaryKey = 'Code';

    // If your primary key is not auto-incrementing
    public $incrementing = true; // set to false if Code is not auto-increment

    // Data type of primary key
    protected $keyType = 'int';

    // Disable default timestamps if your table doesn't use created_at and updated_at
    public $timestamps = false; // set true if you want to use Eloquent timestamps

    // Fields that are mass assignable
    protected $fillable = [
        'TableNo',
        'Description',
        'MainLocationCode',
        'CreationDate',
        'ModificationDate',
        'UserCode',
    ];

    // If you want to cast dates automatically
    protected $casts = [
        'CreationDate' => 'datetime',
        'ModificationDate' => 'datetime',
    ];
}
