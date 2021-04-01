<?php

return [
    'api_key' => env('VAPOR_LOGGER_KEY', null),
    'api_url' => env('VAPOR_LOGGER_API', 'https://api.vaporlog.co/entry'), // POST Url to capture logs
    'is_vapor' => isset($_SERVER['VAPOR_SSM_PATH']),
    'log_level' => env('VAPOR_LOGGER_LEVEL', env('LOG_LEVEL', 'debug')),
    'throttle' => [
        'identical' => env('VAPOR_LOGGER_IDENTICAL_THROTTLE', 1),
        'failure' => env('VAPOR_LOGGER_FAILURE_THROTTLE', 10),
    ],
];