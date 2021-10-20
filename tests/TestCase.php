<?php

namespace Tests;

use ArtisanBuild\VaporLogger\VaporLoggerProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        \Cache::flush();
    }

    public function tearDown(): void
    {
        if (File::exists(base_path('vapor.yml'))) {
            File::delete(base_path('vapor.yml'));
        }
        \Cache::flush();
        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [VaporLoggerProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('app.key', 'base64:r0w0xC+mYYqjbZhHZ3uk1oH63VadA3RKrMW52OlIDzI=');
    }

    protected function isEnabledOnVapor($app)
    {
        File::put(base_path('vapor.yml'), File::get(__DIR__ . '/vapor.yml.stub'));
        $app['config']->set('vapor-logger.api_key', Str::random(16));
        $app['config']->set('vapor-logger.is_vapor', true);
    }

    protected function isEnabledOnVaporWithSlack($app)
    {
        File::put(base_path('vapor.yml'), File::get(__DIR__ . '/vapor.yml.stub'));
        $app['config']->set('vapor-logger.api_key', Str::random(16));
        $app['config']->set('vapor-logger.is_vapor', true);
        $app['config']->set('vapor-logger.add_channels', ['slack']);
    }
}
