<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $mockGuard = \Mockery::mock(\App\Services\SystemGuard::class);
        $mockGuard->shouldReceive('isSystemHealthy')->andReturn(true);
        $this->app->instance(\App\Services\SystemGuard::class, $mockGuard);
    }
}
