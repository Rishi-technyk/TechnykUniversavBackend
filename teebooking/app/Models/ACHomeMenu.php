<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ACHomeMenu extends Model
{
    use HasFactory;

    // Table name
    protected $table = 'AC_home_menus';

    // Primary key
    protected $primaryKey = 'id';

    // Fillable fields
    protected $fillable = [
        'name',
        'icon',
        'navigate',
        'status',
        'type'
    ];

    // Timestamps are enabled by default
}
