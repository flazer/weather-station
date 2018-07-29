<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceSensor extends Model
{

    public function type() {
        return $this->hasOne('App\Models\SensorType', 'id', 'type_id');
    }

    public function values() {
        return $this->hasMany('App\Models\DeviceSensorValue', 'device_sensor_id', 'id');
    }

    public function latest() {
        $value = $this->values()->orderBy('created_at', 'DESC')->first();
        return $value;
    }


}
