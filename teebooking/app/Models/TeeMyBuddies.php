<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeeMyBuddies extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tee_my_buddies';

    protected $fillable = ['member_id', 'is_active', 'created_by', 'updated_by'];
}

?>