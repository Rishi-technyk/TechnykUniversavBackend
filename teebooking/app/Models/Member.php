<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Notifications\Notifiable;

use Laravel\Passport\HasApiTokens;



class Member extends Authenticatable

{

    use HasApiTokens, HasFactory, Notifiable;



    protected $table = "memberprofile";

    //protected $primaryKey = "id";


public $timestamps = false;
    /**

     * The attributes that are mass assignable.

     *

     * @var array<int, string>

     */

    protected $fillable = [

        'id', 'SC_ID', 'MemberID', 'DisplayName', 'Password', 'hash_password', 'Email', 'Mobile', 'Status', 'cdate', 'mdate', 'Gender', 'Address', 'Phone', 'DOB', 'CategoryTypeSub', 'Category', 'city', 'state', 'country', 'pin', 'CategoryCode', 'SpouseName', 'SpouseDOB', 'AnniversaryDate', 'role'

    ];



    /**

     * The attributes that should be hidden for serialization.

     *

     * @var array<int, string>

     */

    protected $hidden = [

        'password',

        'remember_token',

    ];



    /**

     * The attributes that should be cast.

     *

     * @var array<string, string>

     */

    protected $casts = [

        'email_verified_at' => 'datetime',

    ];



    public function category()

    {

        return $this->hasOne(\App\Models\CategoryMaster::class, 'code', 'Category');

    }

}

