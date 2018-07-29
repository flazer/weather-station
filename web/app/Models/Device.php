<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{

    const STATUS_RUN  = 'RUN';
    const STATUS_STOP = 'STOP';

    public function sensors() {
        return $this->hasMany('App\Models\DeviceSensor', 'device_id', 'id');
    }

    public function hasSensorType($typeId) {
        $sensors = $this->sensors();

        if ($sensors && count($sensors) > 0) {
            foreach ($sensors as $key => $sensor) {
                if ($sensor->type_id == $typeId) {
                    return $sensor;
                }
            }

            return false;
        }
    }

    public function getSensorByTypeId($typeId) {
        return DeviceSensor::where('device_id', $this->id)
            ->where('type_id', $typeId)
            ->first();
    }

}
