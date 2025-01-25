<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
    'username',
    'fullname',
    'role',
    'api_token',
    'password',
  ];

  public function generateToken()
  {
    $this->api_token = Str::random(200) . $this->id . Str::random(5) . "/#" . MyLib::getMillis();
    $this->save();
    return $this->api_token;
  }
}
