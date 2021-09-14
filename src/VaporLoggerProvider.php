<?php

namespace ArtisanBuild\VaporLogger;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Monolog\Formatter\HtmlFormatter;

class VaporLoggerProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (Cache::get('vapor-logger-deployment') !== data_get($_SERVER, 'VAPOR_ARTIFACT_NAME')) {
            Cache::forget('vapor-logger-inactive');
            Cache::rememberForever('vapor-logger-deployment', fn() => data_get($_SERVER, 'VAPOR_ARTIFACT_NAME'));
        }
        if (Cache::get('vapor-logger-inactive', null) === null
            && (config('vapor-logger.is_vapor'))) {
            // We are running in Vapor or don't care that we aren't so we will override the logging settings
            // in Laravel.
            Config::set('logging.channels.vapor-logger', [
                'driver'  => 'monolog',
                'level'   => config('vapor-logger.log_level', 'debug'),
                'handler' => VaporLogger::class,
            ]);

            Config::set('logging.channels.vapor-stack', [
                'driver'            => 'stack',
                'channels'          => array_merge(['stderr', 'vapor-logger'], config('vapor-logger.add_channels')),
                'ignore_exceptions' => false,
            ]);

            Config::set('logging.default', 'vapor-stack');

            if (config('vapor-logger.heartbeat')) {
                $this->app->booted(function () {
                    $schedule = $this->app->make(Schedule::class);
                    $schedule->call(function (): void {
                        Log::debug('The vapor-logger package is managing logging for this environment.');
                    })->hourly();
                });
            }

        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'vapor-logger');
    }
}
