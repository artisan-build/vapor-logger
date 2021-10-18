<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ConsoleTest extends TestCase
{
    /** @test */
    public function can_clear_cache_with_console_command()
    {
        Cache::remember('vapor-logger-inactive', 3000, fn() => 'Hello');

        $this->assertTrue(Cache::has('vapor-logger-inactive'));

        Artisan::call('vl:cache:clear');

        $this->assertFalse(Cache::has('vapor-logger-inactive'));
    }
}