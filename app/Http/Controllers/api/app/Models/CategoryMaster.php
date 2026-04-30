<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;



class CategoryMaster extends Model

{

    use HasFactory;



    protected $table = "categorymaster";

    protected $primaryKey = "code";

    protected $guarded = [];

    public function member()

    {

        return $this->belongsTo(\App\Models\Member::class, 'Category', 'id');

    }



    public function roomPrice()

    {

        return $this->belongsTo(RoomPrice::class, 'member_category_id', 'id');

    }

}

