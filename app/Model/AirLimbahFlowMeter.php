<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
// use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Str;

class AirLimbahFlowMeter extends Model
{
    use Notifiable;
    
    public $timestamps = false;

    protected $primaryKey = null;
    // protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'created_at', 'wwtp_id','tenant_id', 'real_time_val', 'total_val',
    ];
}
