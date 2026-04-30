<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppModule extends Model
{
    protected $fillable = [
        'name',
        'subtitle',
        'icon',
        'navigate',
        'module_key',
        'layout',
        'position',
        'is_active',
        'data_json'
    ];

    protected $casts = [
        'data_json' => 'array',
        'is_active' => 'boolean'
    ];
}
