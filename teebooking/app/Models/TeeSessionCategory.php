<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeeSessionCategory extends Model
{
    protected $table = 'tee_session_categories';
    use HasFactory;

    protected $fillable = ['category_type_Code', 'tee_session_id'];

   
}
