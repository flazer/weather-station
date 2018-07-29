<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceSensorValue;
use App\Models\SensorType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;

class ApiController extends Controller
{

    public function details(Request $request) {
        $errors = [];

        $validation = Validator::make($request->all(), [
            'device' => 'required|string|min:2|max:60|exists:devices,slug'
        ]);

        if ($validation->fails()) {
            $errors = $validation->errors()->getMessages();
        }

        if ($validation->passes()) {
            $device = Device::where('slug', $request->device)->first();

            if ($device->status > Device::STATUS_RUN) {
                $errors['status'] = 'Device is disabled.';
            }

            if (empty($errors)) {
                $result = [];
                foreach ($device->sensors as $sensor) {
                    $meta = $sensor->type;
                    $values = DeviceSensorValue::where('device_sensor_id', $sensor->id)
                        ->select('value', 'created_at')
                        ->where('created_at', '>=', Carbon::now()->subDay())
                        ->orderBy('created_at', 'ASC')
                        ->get();

                    //Build new structure for easier frontend-handling
                    $result[$meta->slug]['unit'] = $meta->unit;
                    $result[$meta->slug]['unit'] = $meta->unit;
                    $result[$meta->slug]['type'] = $meta->name;
                    $result[$meta->slug]['name'] = $device->name;

                    if (count($values) > 0) {
                        $result[$meta->slug]['latest_date'] = $values->last()->created_at->format(config('sensors.format_datetime'));
                        foreach ($values AS $value) {
                            $result[$meta->slug]['values'][] = $value->value;
                            $result[$meta->slug]['labels'][] = $value->created_at->format(config('sensors.format_time'));
                        }
                    }
                }

                return $result;
            }
        }

        return [
            'error' => $errors,
        ];
    }

    public function read(Request $request) {
        $errors = [];

        $validation = Validator::make($request->all(), [
            'device' => 'required|string|min:2|max:60|exists:devices,slug'
        ]);

        if ($validation->fails()) {
            $errors = $validation->errors()->getMessages();
        }

        if ($validation->passes()) {
            $device = Device::where('slug', $request->device)->first();

            if ($device->status > Device::STATUS_RUN) {
                $errors['status'] = 'Device is disabled.';
            }

            if (!$device->updated_at) {
                $errors['update'] = 'Device has never been updated. (No data?)';
            }

            if (empty($errors)) {
                $data = [
                    'device' => [
                        'name' => $device->name,
                        'updated' => [
                            'time' => $device->updated_at->format(config('sensors.format_time')),
                            'date' => $device->updated_at->format(config('sensors.format_date'))
                        ]
                    ]
                ];

                foreach ($device->sensors AS $sensor) {
                    $latest = $sensor->latest();
                    $data['sensors'][$sensor->id] = [
                        'type' => $sensor->type,
                        'value' => $latest->value,
                        'time' => $latest->created_at
                    ];
                }

                return $data;
            }
        }

        return [
            'error' => $errors,
        ];

    }

    public function save(Request $request) {
        $errors = [];

        $data = $request->all();
        if (isset($data['token'])) $data['token'] = md5($data['token']);
        $validation = Validator::make($data, [
            'token' => 'required|string|min:2|max:60|exists:devices,token'
        ]);

        if ($validation->fails()) {
            echo "meh;";
            $errors = $validation->errors()->getMessages();
        }

        if ($validation->passes()) {
            $device = Device::where('token', $data['token'])->first();

            if ($device) {
                $updated_at = ($device->updated_at) ? $device->updated_at : Carbon::now()->subMinutes(1);
                if ($updated_at->addSeconds(60) > Carbon::now()) {
                    $errors['api_limit'] = 'Only one call per device per minute.';
                }

                if ($device->status > Device::STATUS_RUN) {
                    $errors['status'] = 'Device is disabled.';
                }

                if (empty($errors)) {
                    $values = [];
                    $sensorTypes = SensorType::all();
                    foreach ($sensorTypes as $v) {
                        if ($request->has($v->slug)) {
                            $key = $v->slug;
                            $values[$v->id] = $request->$key;
                        }
                    }

                    $result = $this->_writeDB($device, $values);

                    if (!empty($result)) {
                        $device->updated_at = Carbon::now();
                        $device->save();

                        return $result;
                    } else {
                        $errors['unkown'] = 'No values updated. Maybe you sent unknown sensortypes for your device.';
                    }
                }
            }
        }


        return [
            'error' => $errors,
        ];
    }

    private function _writeDB($device, $values) {
        $result = [];

        foreach ($values AS $typeId => $value) {
            $deviceSensor = $device->getSensorByTypeId($typeId);

            if ($deviceSensor) {
                $entry = new DeviceSensorValue();
                $entry->device_sensor_id = $deviceSensor->id;
                $entry->value = $value;
                $entry->save();

                $deviceSensor->updated_at = Carbon::now();
                $deviceSensor->save();

                $result[] = [
                    'type' => $deviceSensor->type->slug,
                    'saved' => true
                ];
            }
        }

        return $result;
    }
}