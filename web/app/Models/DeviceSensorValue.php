<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceSensorValue extends Model
{
    protected $fillable = [
        'device_sensor_id',
        'value'
    ];

}
