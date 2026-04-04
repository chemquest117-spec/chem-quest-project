<?php

namespace Tests;

use App\Services\SystemGuard;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $mockGuard = \Mockery::mock(SystemGuard::class);
        $mockGuard->shouldReceive('isSystemHealthy')->andReturn(true);
        $this->app->instance(SystemGuard::class, $mockGuard);
    }
}
