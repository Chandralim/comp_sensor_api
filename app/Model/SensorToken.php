<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Str;

class SensorToken extends Model
{
    use Notifiable;

    public $timestamps = false;

    protected $hidden = [
        'token',
        'admin_id',
        'coor_lat',
        'coor_long'
    ];

    public function sensor_lists()
    {
        return $this->hasMany(SensorList::class, 'sensor_token_id', "id")->orderBy("id", "asc");
    }
}
