<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KOTSetting extends Model
{
    use HasFactory;

    protected $table = 'KOT_settings';

    protected $fillable = [
        'service_name',
        'status',
    ];
}
