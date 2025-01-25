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

    protected $hidden = [
        'sensor_token_id',
        'uname',
        'value_down_limit',
        'value_top_limit'
    ];

    public function sensor_datas()
    {
        return $this->hasMany(SensorDataRaw::class, 'sensor_list_id', "id");
    }
}
