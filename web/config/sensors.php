<?php

return [
    'update_interval_secs' => env('SENSORS_UPDATE_INTERVAL_SECS', 300),
    'format_time' => env('SENSORS_FORMAT_TIME', 'H:i'),
    'format_date' => env('SENSORS_FORMAT_DATE', 'd.m.Y'),
    'format_datetime' => env('SENSORS_FORMAT_DATETIME', 'd.m.Y H:i'),
    'format_datetime_hourly' => env('SENSORS_FORMAT_DATETIME_HOURLY', 'd.m.Y H:30'),
    'format_datetime_daily' => env('SENSORS_FORMAT_DATETIME_HOURLY', 'd.m.Y'),
    'format_datetime_weekly' => env('SENSORS_FORMAT_DATETIME_WEEKLY', 'W - Y'),
    'format_datetime_monthly' => env('SENSORS_FORMAT_DATETIME_MONTHLY', 'M Y'),
    'format_datepicker' => env('SENSORS_FORMAT_DATEPICKER', 'DD.MM.YYYY'),

    'groups' => [
        'hourly',
        'daily',
        'weekly',
        'monthly'
    ]
]
?>