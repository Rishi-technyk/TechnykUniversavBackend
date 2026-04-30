<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FB_KOTModifier extends Model
{
    use HasFactory;

    protected $table = 'FB_KOTModifiers';
    protected $primaryKey = 'Code';
    public $timestamps = false; // We handle timestamps manually

    protected $fillable = [
        'ModifierName',
        'DisplayAs',
        'Status',
        'CreationDate',
        'ModificationDate',
        'UserCode',
    ];

    // Auto-manage creation & modification dates
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->CreationDate = now();
        });

        static::updating(function ($model) {
            $model->ModificationDate = now();
        });
    }
}
