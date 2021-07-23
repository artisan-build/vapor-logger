<?php

namespace Tests;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use TiMacDonald\Log\LogFake;

class ConfigurationTest extends TestCase
{
    /**
     * @test
     * @define-env isEnabledOnVapor
     */
    public function logs_to_the_correct_channel_on_vapor()
    {
        Log::swap(new LogFake());

        Http::fake();
        Log::debug('This is only a test');

        Log::channel('vapor-stack')->assertLogged('debug');
        Log::channel('stack')->assertNotLogged('debug');
    }

    /**
     * @test
     * @define-env isEnabledOnVaporWithSlack
     */
    public function logs_to_the_correct_channel_on_vapor_with_slack()
    {
        Log::swap(new LogFake());

        Http::fake();
        Log::debug('This is only a test');

        Log::channel('vapor-stack')->assertLogged('debug');
        Log::channel('stack')->assertNotLogged('debug');

        $this->assertContains('slack', config('logging.channels.vapor-stack.channels'));
    }

    /**
     * @test
     */
    public function logs_to_the_default_channel_if_not_enabled()
    {
        Log::swap(new LogFake());

        Log::debug('This is only a test');

        Log::channel('stack')->assertLogged('debug');
        Log::channel('vapor-stack')->assertNotLogged('debug');
    }
}
