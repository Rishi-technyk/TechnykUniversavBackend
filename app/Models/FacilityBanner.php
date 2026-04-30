<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityBanner extends Model
{
    use HasFactory;

    protected $table = 'facility_banners';

    protected $fillable = [
        'facility_id',
        'image',
        'title',
        'description',
        'status',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
