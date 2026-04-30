<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;



class CategoryType extends Model

{

    protected $table = 'categorytypes';

    protected $primaryKey = 'Code';

    public $incrementing = false;

    public $timestamps = false;



    protected $fillable = [

        'Code',

        'CategoryType',

    ];

}

