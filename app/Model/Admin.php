<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Illuminate\Database\Eloquent\Model;
use Str;
use App\Helpers\MyLib;

class Admin extends Authenticatable
{
    use Notifiable;
    
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'fullname','role', 'api_token', 'password',
    ];

    // /**
    //  * The attributes that should be hidden for arrays.
    //  *
    //  * @var array
    //  */
    // protected $hidden = [
    //     'password', 'api_token',
    // ];

    // /**
    //  * The attributes that should be cast to native types.
    //  *
    //  * @var array
    //  */
    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    // ];

    public function generateToken()
    {
      $this->api_token=Str::random(200).$this->id.Str::random(5)."/#".MyLib::getMillis();
      // $this->last_access_at=MyLib::getMillis();
      $this->save();
      return $this->api_token;
    }
}
