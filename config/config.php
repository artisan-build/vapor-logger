<?php

return [
    'api_key' => env('VAPOR_LOGGER_KEY', null),
    'api_url' => env('VAPOR_LOGGER_API', 'https://api.vaporlog.co/api/log'), // POST Url to capture logs
    'is_vapor' => env('VAPOR_LOGGER_IS_VAPOR', isset($_SERVER['VAPOR_SSM_PATH'])),
    'log_level' => env('VAPOR_LOGGER_LEVEL', env('LOG_LEVEL', 'debug')),
    'add_channels' => strlen(env('VAPOR_LOGGER_ADD_CHANNELS', '')) === 0 ? []
        : explode(',', env('VAPOR_LOGGER_ADD_CHANNELS')),
    'throttle' => [
        'identical' => env('VAPOR_LOGGER_IDENTICAL_THROTTLE', 1),
        'failure' => env('VAPOR_LOGGER_FAILURE_THROTTLE', 10),
    ],
    'heartbeat' => env('VAPOR_LOGGER_HEARTBEAT', false),
];