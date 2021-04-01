<?php

namespace ArtisanBuild\VaporLogger;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class VaporLoggerProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (Cache::get('vapor-logger-inactive', null) === null
            && (config('vapor-logger.is_vapor'))) {
            // We are running in Vapor or don't care that we aren't so we will override the logging settings
            // in Laravel.
            Config::set('logging.channels.vapor-logger', [
                'driver'  => 'monolog',
                'level'   => config('valor-logger.log_level', 'debug'),
                'handler' => VaporLogger::class,
            ]);

            Config::set('logging.channels.vapor-stack', [
                'driver'            => 'stack',
                'channels'          => ['stderr', 'vapor-logger'],
                'ignore_exceptions' => false,
            ]);

            Config::set('logging.default', 'vapor-stack');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'vapor-logger');
    }
}
