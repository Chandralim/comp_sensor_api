<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Str;

class Location extends Model
{
    use Notifiable;
    
    public $timestamps = false;

    protected $fillable = [
        'id', 'is_tenant','grup','name',
    ];

    // public function flowmeter()
    // {
    //     return $this->hasOne(FlowMeter::class, "location_id", "id");
    // }

    // public function sensors()
    // {
    //     return $this->hasMany(Sensor::class, "location_id", "id");
    // }
}
