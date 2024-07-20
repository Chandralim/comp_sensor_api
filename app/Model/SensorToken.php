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

    // protected $fillable = [
    //     'id', 'token', 'name',
    // ];

    public function lists()
    {
        return $this->hasMany(SensorList::class, 'id', "location_id");
    }
}
