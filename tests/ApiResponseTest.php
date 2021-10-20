<?php

namespace Tests;

use ArtisanBuild\VaporLogger\VaporLogger;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\TestTime\TestTime;

class ApiResponseTest extends TestCase
{
    /**
     * @test
     * @define-env isEnabledOnVapor
     */
    public function allows_one_instance_of_logged_event_per_second()
    {
        TestTime::freeze();

        Http::fake(function ($request) {
            return Http::response(null, 201);
        });

        Http::assertNothingSent();

        $this->assertNull(Cache::get(crc32(100 . 'This is only a test')));

        Log::debug('This is only a test');

        Http::assertSentCount(1);

        $this->assertNotNull(Cache::get(crc32(100 . 'This is only a test')));

        Log::debug('This is only a test');

        Http::assertSentCount(1);

        TestTime::addSecond();

        $this->assertNull(Cache::get(crc32(100 . 'This is only a test')));

        Log::debug('This is only a test');

        Http::assertSentCount(2);
    }

    /**
     * @test
     * @define-env isEnabledOnVapor
     */
    public function shuts_down_http_requests_indefinitely_if_account_suspended()
    {

        TestTime::freeze();

        Http::fake(function ($request) {
            return Http::response(null, 403);
        });

        Http::assertNothingSent();

        Log::debug('This is only a test');

        Http::assertSentCount(1);

        $this->assertEquals(VaporLogger::SUSPENDED, Cache::get('vapor-logger.inactive'));

        TestTime::addMinute();

        Log::debug('This is only a test');

        Http::assertSentCount(1);

        $this->assertEquals(VaporLogger::SUSPENDED, Cache::get('vapor-logger.inactive'));

    }

    /**
     * @test
     * @define-env isEnabledOnVapor
     */
    public function shuts_down_http_requests_indefinitely_if_account_not_authorized()
    {

        TestTime::freeze();

        Http::fake(function ($request) {
            return Http::response(null, 401);
        });

        Http::assertNothingSent();

        Log::debug('This is only a test');

        Http::assertSentCount(1);

        $this->assertEquals(VaporLogger::UNAUTHORIZED, Cache::get('vapor-logger.inactive'));

        TestTime::addMinute();

        Log::debug('This is only a test');

        Http::assertSentCount(1);

        $this->assertEquals(VaporLogger::UNAUTHORIZED, Cache::get('vapor-logger.inactive'));
    }

    /**
     * @test
     * @define-env isEnabledOnVapor
     */
    public function shuts_down_http_requests_for_ten_seconds_if_unknown_error()
    {

        TestTime::freeze();

        Http::fake(['*' => Http::sequence()
                    ->pushStatus(500)
                    ->pushStatus(201),
            ]);

        Http::assertNothingSent();

        Log::debug('This is only a test');

        Http::assertSentCount(1);

        $this->assertEquals(VaporLogger::UNKNOWN, Cache::get('vapor-logger.inactive'));

        TestTime::addSecond();

        Log::debug('This is only a test');

        Http::assertSentCount(1);

        $this->assertEquals(VaporLogger::UNKNOWN, Cache::get('vapor-logger.inactive'));

        TestTime::addSeconds(10);

        Log::debug('This is only a test');

        Http::assertSentCount(2);

        $this->assertNull(Cache::get('vapor-logger.inactive'));
    }
}
