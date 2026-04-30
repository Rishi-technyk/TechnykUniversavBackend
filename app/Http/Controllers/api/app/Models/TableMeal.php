<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TableMeal extends Model
{
    protected $table = 'table_meals';

    protected $fillable = [
        'name',
        'status'
    ];

    public function tables()
    {
        return $this->hasMany(Table::class, 'meal_id');
    }

    public function times()
    {
        return $this->hasMany(TableTime::class, 'meal_id');
    }
}
