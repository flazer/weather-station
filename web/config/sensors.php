<?php

return [
    'update_interval_secs' => env('SENSORS_UPDATE_INTERVAL_SECS', 300),
    'format_time' => env('SENSORS_FORMAT_TIME', 'H:i'),
    'format_date' => env('SENSORS_FORMAT_DATE', 'd.m.Y'),
    'format_datetime' => env('SENSORS_FORMAT_DATETIME', 'd.m.Y H:i'),
]
?>