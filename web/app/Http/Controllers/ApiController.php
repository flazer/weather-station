<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceSensorValue;
use App\Models\SensorType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

            $start_date = $this->_parseDate($request, 'date_start', '00:00:00');
            $end_date = $this->_parseDate($request, 'date_end', '23:59:59');
            $group = $this->_parseGroup($request);

            //Correct end_date if below start_date
            if ($start_date > $end_date) {
                $end_date = $start_date->copy()->addDay(1)->subSecond(1);
            }

            if (empty($errors)) {
                $result = [
                    'selection' => [
                        'date' => [
                            'start_date' => $start_date->format(config('sensors.format_date')),
                            'end_date'  => $end_date->format(config('sensors.format_date')),
                        ],
                        'datetime' => [
                            'start_date' => $start_date->format(config('sensors.format_datetime')),
                            'end_date'  => $end_date->format(config('sensors.format_datetime')),
                        ],

                        'group' => $group
                    ]
                ];


                foreach ($device->sensors as $sensor) {
                    $meta = $sensor->type;

                    $query = DB::table('device_sensor_values')
                        ->where('device_sensor_id', $sensor->id)
                        ->where('created_at', '>=', $start_date)
                        ->where('created_at', '<=', $end_date);

                    switch ($group) {
                        case 'hourly':
                            $sqlPattern = '%Y-%m-%d %H';
                            $format = 'Y-m-d H';
                            break;

                        case 'daily':
                            $sqlPattern = '%Y-%m-%d';
                            $format = 'Y-m-d';
                            break;

                        case 'weekly':
                            $sqlPattern = '%u-%Y';
                            $format = 'W-Y';
                            break;

                        case 'monthly':
                            $sqlPattern = '%Y-%m';
                            $format = 'Y-m';
                            break;

                        default:
                            $format = 'Y-m-d H:i:s';
                            break;
                    }

                    if (isset($sqlPattern)) {
                        $query = $query->selectRaw('DATE_FORMAT(created_at, "' . $sqlPattern . '") created_at, ROUND(AVG(value), 2) value')
                            ->groupBy(DB::raw('DATE_FORMAT(created_at, "' . $sqlPattern . '")'));
                    } else {
                        $query = $query->orderBy('created_at', 'ASC');
                    }

                    $values = $query->get();

                    //Build new structure for easier frontend-handling
                    $result['datasets'][$meta->slug]['unit'] = $meta->unit;
                    $result['datasets'][$meta->slug]['unit'] = $meta->unit;
                    $result['datasets'][$meta->slug]['type'] = $meta->name;
                    $result['datasets'][$meta->slug]['name'] = $device->name;

                    if (count($values) > 0) {
                        $date_format = ($group) ? config('sensors.format_datetime' . '_' . $group) : config('sensors.format_datetime');
                        $created_at = $this->_buildDateTime($format, $values->last()->created_at, $group);
                        $result['datasets'][$meta->slug]['latest_date'] = $created_at->format($date_format);
                        foreach ($values AS $value) {
                            $created_at = $this->_buildDateTime($format, $value->created_at, $group);
                            $result['datasets'][$meta->slug]['values'][] = $value->value;
                            $result['datasets'][$meta->slug]['labels'][] = $created_at->format($date_format);
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
                    if ($latest) {
                        $data['sensors'][$sensor->id] = [
                            'type' => $sensor->type,
                            'value' => $latest->value,
                            'time' => $latest->created_at
                        ];
                    }
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

    /**
     * Tries parsing a valid date out of request object.
     * If found date is set to future, it will correct it
     * to NOW().
     * Returns a carbon object.
     *
     * @param Request $request
     * @param $field
     * @param string $clock
     * @return Carbon
     */
    private function _parseDate(Request $request, $field, $clock = '00:00:00') {
        $timestamp = strtotime(date('Y-m-d ' . $clock));

        if ($request->has('selection')) {
            $selection = $request->selection;
            if (isset($selection[$field])) {
                $timestamp = strtotime($selection[$field] . ' ' . $clock);

                if ($timestamp > time()) {
                    $timestamp = time();
                }
            }
        }

        $resDate = Carbon::createFromTimestamp($timestamp);

        return $resDate;
    }

    /**
     * Tries parsing a valid group out of request params.
     * Will return null if nothing was found.
     *
     * @param Request $request
     * @return null|string
     */
    private function _parseGroup(Request $request) {
        $group = null;

        if ($request->has('selection')) {
            $selection = $request->selection;
            if (isset($selection['group']) && strlen($selection['group']) > 0) {
                $param = trim(strtolower($selection['group']));
                $groups = array_flip(config('sensors.groups'));
                if (isset($groups[$param])) {
                    $group = $param;
                }
            }
        }

        return $group;
    }


    /**
     * Nasty helper-method, because DateTime doesn't support
     * W(eek)-parameter to build a valid date from string
     *
     * @param $format
     * @param $dateString
     * @param bool $group
     * @return Carbon
     */
    private function _buildDateTime($format, $dateString, $group = false) {
        $result = null;

        if ($group == 'weekly') {
            list ($week, $year) = explode('-',  $dateString);
            return (new Carbon)->setISODate($year, $week);
        }

        return Carbon::createFromFormat($format, $dateString);
    }
}