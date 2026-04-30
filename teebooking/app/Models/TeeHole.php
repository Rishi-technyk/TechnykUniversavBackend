<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class TeeHole extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tee_holes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'hole_number', 'is_active', 'created_by', 'updated_by'
    ];
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
