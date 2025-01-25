<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Str;

class SensorDataRaw extends Model
{
    use Notifiable;

    public $timestamps = false;

    protected $hidden = [
        'sensor_list_id'
    ];
}
