<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Str;

class SensorList extends Model
{
    use Notifiable;

    public $timestamps = false;

    // protected $fillable = [
    //     'id', 'token', 'name',
    // ];

    // public function location()
    // {
    //     return $this->hasOne(Location::class, 'id', "location_id");
    // }
}
