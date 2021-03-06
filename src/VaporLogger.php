<?php

namespace ArtisanBuild\VaporLogger;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Monolog\Handler\AbstractHandler;
use Symfony\Component\Yaml\Yaml;
use Throwable;

class VaporLogger extends AbstractHandler
{
    const SUSPENDED = 'Account Suspended';
    const UNAUTHORIZED = 'API Token Not Authorized';
    const UNKNOWN = 'Unknown Problem';

    public function handle(array $record): bool
    {
        Arr::set($record['extra'], 'vapor_id', 1);
        Arr::set($record['extra'], 'vapor_name', config('app.name'));
        Arr::set($record['extra'], 'vapor_env', config('app.env'));

        if (File::exists(base_path('vapor.yml'))) {
            $vapor = Yaml::parse(File::get(base_path('vapor.yml')));
            Arr::set($record['extra'], 'vapor_id', $vapor['id'] ?? null);
            Arr::set($record['extra'], 'vapor_name', $vapor['name'] ?? null);
            Arr::set($record['extra'],'vapor_env',
                last(explode('-', $_SERVER['VAPOR_SSM_PATH'] ?? '-default')));
        }

        Arr::set($record['extra'], 'event', $record['level'] . $record['message']);

        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof Throwable) {
            $trace = $record['context']['exception']->getTrace();
            Arr::set($record['extra'], 'trace', $trace);
            $record['message'] .= ' in ' . data_get($trace[0], 'file');
            $record['message'] .= ' at line ' . data_get($trace[0], 'line');
        }

        // Not configured. So just disable and don't bother our server.
        if (! config('vapor-logger.api_key')) {
            Cache::remember('vapor-logger.inactive', 300, fn () => self::UNAUTHORIZED);

            return false;
        }

        // Already logged this exact same event within the past second.
        if (Cache::get(crc32(Arr::get($record, 'extra.event'))) !== null) {
            return false;
        }

        // Either the account or the service is disabled at the moment.
        if (Cache::get('vapor-logger.inactive') !== null) {
            return false;
        }

        // Send the record to the API to be recorded and expect a 201 result if all goes well.
        $save = Http::withToken(config('vapor-logger.api_key'))
            ->timeout(1)
            ->post(config('vapor-logger.api_url'), $record);

        // We will turn off the logger for a period of time if there is a problem with the
        // account or the service.
        if ($save->status() !== 201) {
            switch ($save->status()) {
                case 401:
                    // Account cannot be determined with token used in the request.
                    Cache::remember('vapor-logger.inactive', 300, fn () => self::UNAUTHORIZED);
                    break;
                case 403:
                    // Account suspended for non-payment or other reason
                    Cache::remember('vapor-logger.inactive', 300, fn () => self::SUSPENDED);
                    break;
                default:
                    // We don't know what went wrong, so take a >=10 second break.
                    Cache::remember('vapor-logger.inactive',
                        max((int) config('vapor-logger.throttle.failure'), 10), fn () => self::UNKNOWN);
                    break;
            }

            return false;
        }

        // We saved the record to the log. Let's remember that for >=1 second so we don't send it again too soon.
        Cache::remember(crc32(Arr::get($record, 'extra.event')),
            max((int) config('vapor-logger.throttle.identical'), 1), fn () => time());

        return true;
    }
}
