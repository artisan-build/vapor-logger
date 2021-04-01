<?php

namespace ArtisanBuild\VaporLogger;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Monolog\Handler\AbstractHandler;
use Symfony\Component\Yaml\Yaml;

class VaporLogger extends AbstractHandler
{
    const SUSPENDED = 'Account Suspended';
    const UNAUTHORIZED = 'API Token Not Authorized';
    const UNKNOWN = 'Unknown Problem';

    public function handle(array $record): bool
    {
        if (File::exists(base_path('vapor.yml'))) {
            $vapor = Yaml::parse(File::get(base_path('vapor.yml')));
            Arr::set($record['extra'], 'vapor_id', $vapor['id'] ?? null);
            Arr::set($record['extra'], 'vapor_name', $vapor['name'] ?? null);
        }

        Arr::set($record['extra'], 'event', $record['level'] . $record['message']);

        // Not configured. So just disable and don't bother our server.
        if (! config('vapor-logger.api_key')) {
            Cache::rememberForever('vapor-logger.inactive', fn () => self::UNAUTHORIZED);

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
            ->post(config('vapor-logger.api_url'), $record);

        // We will turn off the logger for a period of time if there is a problem with the
        // account or the service.
        if ($save->status() !== 201) {
            switch ($save->status()) {
                case 401:
                    // Account cannot be determined with token used in the request.
                    Cache::rememberForever('vapor-logger.inactive', fn () => self::UNAUTHORIZED);
                    break;
                case 403:
                    // Account suspended for non-payment or other reason
                    Cache::rememberForever('vapor-logger.inactive', fn () => self::SUSPENDED);
                    break;
                default:
                    // We don't know what went wrong, so take a 10 second break.
                    Cache::remember('vapor-logger.inactive', 10, fn () => self::UNKNOWN);
                    break;
            }

            return false;
        }

        // We saved the record to the log. Let's remember that for one second so we don't send it again too soon.
        Cache::remember(crc32(Arr::get($record, 'extra.event')), 1, fn () => time());

        return true;
    }
}
