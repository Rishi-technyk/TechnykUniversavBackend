<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionCategory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tee_session_categories';
    
    protected $fillable = ['category_type_Code', 'tee_session_id'];

    // Define the relationship with the CategoryType model
    public function categoryType()
    {
        return $this->belongsTo(CategoryType::class, 'category_type_Code', 'Code');
    }

    // Define the relationship with the TeeSession model
    public function teeSession()
    {
        return $this->belongsTo(TeeSession::class, 'tee_session_id');
    }
}

?>