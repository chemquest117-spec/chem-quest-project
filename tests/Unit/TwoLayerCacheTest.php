<?php

use App\Jobs\RefreshCacheJob;
use App\Support\CacheTTL;
use App\Support\MemoryCache;
use App\Support\TwoLayerCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    MemoryCache::flush();
    RefreshCacheJob::resetDispatched();
    Cache::flush();
});

describe('TwoLayerCache', function () {
    describe('basic remember flow', function () {
        it('computes and caches on first call (no SWR)', function () {
            $callCount = 0;
            $result = TwoLayerCache::remember('test_key', 300, 60, function () use (&$callCount) {
                $callCount++;
                return 'db_value';
            });

            expect($result)->toBe('db_value');
            expect($callCount)->toBe(1);
        });

        it('returns memory-cached value on second call', function () {
            $callCount = 0;
            $callback = function () use (&$callCount) {
                $callCount++;
                return 'db_value';
            };

            TwoLayerCache::remember('test_key', 300, 60, $callback);
            $result = TwoLayerCache::remember('test_key', 300, 60, $callback);

            expect($result)->toBe('db_value');
            expect($callCount)->toBe(1);
        });

        it('returns null when callback returns null', function () {
            $result = TwoLayerCache::remember('null_key', 300, 60, fn () => null);
            expect($result)->toBeNull();
        });
    });

    describe('forget', function () {
        it('removes from both memory and Redis', function () {
            TwoLayerCache::remember('forget_key', 300, 60, fn () => 'value');
            TwoLayerCache::forget('forget_key');

            expect(MemoryCache::get('forget_key'))->toBeNull();
            expect(Cache::get('forget_key'))->toBeNull();
        });
    });

    describe('SWR (Stale-While-Revalidate)', function () {
        it('stores SWR metadata when staleWindow is set', function () {
            TwoLayerCache::remember('swr_key', 300, 60, fn () => 'fresh_value', 120);

            $cached = Cache::get('swr_key');
            expect($cached)->toBeArray();
            expect($cached)->toHaveKeys(['value', 'expires_at', 'stale_until']);
            expect($cached['value'])->toBe('fresh_value');
            expect($cached['stale_until'])->toBeGreaterThan($cached['expires_at']);
        });

        it('serves fresh SWR data directly', function () {
            TwoLayerCache::remember('swr_fresh', 300, 60, fn () => 'fresh', 120);

            // Second call should return the value from cache
            $callCount = 0;
            $result = TwoLayerCache::remember('swr_fresh', 300, 60, function () use (&$callCount) {
                $callCount++;
                return 'recomputed';
            }, 120);

            expect($result)->toBe('fresh');
            expect($callCount)->toBe(0);
        });

        it('does not store SWR metadata when staleWindow is 0', function () {
            TwoLayerCache::remember('no_swr', 300, 60, fn () => 'plain_value', 0);

            $cached = Cache::get('no_swr');
            expect($cached)->toBe('plain_value');
        });
    });

    describe('backward compatibility', function () {
        it('works without staleWindow parameter', function () {
            $result = TwoLayerCache::remember('compat_key', 300, 60, fn () => 'value');
            expect($result)->toBe('value');

            // Should store plain value (no SWR wrapping)
            expect(Cache::get('compat_key'))->toBe('value');
        });

        it('reads legacy non-SWR data from Redis', function () {
            // Simulate legacy data already in Redis (no SWR metadata)
            Cache::put('legacy_key', 'legacy_value', 300);

            $result = TwoLayerCache::remember('legacy_key', 300, 60, fn () => 'should_not_compute');
            expect($result)->toBe('legacy_value');
        });
    });

    describe('observability logging', function () {
        it('logs cache miss on first call', function () {
            Log::shouldReceive('info')
                ->withArgs(function ($message, $context) {
                    return $message === 'cache_miss' && $context['layer'] === 'database';
                })
                ->once();

            // Allow other log calls
            Log::shouldReceive('debug')->zeroOrMoreTimes();
            Log::shouldReceive('info')->zeroOrMoreTimes();

            TwoLayerCache::remember('log_test', 300, 60, fn () => 'value');
        });

        it('logs redis hit when memory misses but redis has data', function () {
            // Pre-populate Redis only
            Cache::put('redis_only', 'redis_value', 300);

            Log::shouldReceive('info')
                ->withArgs(function ($message, $context) {
                    return $message === 'cache_hit' && $context['layer'] === 'redis';
                })
                ->once();

            Log::shouldReceive('debug')->zeroOrMoreTimes();
            Log::shouldReceive('info')->zeroOrMoreTimes();

            TwoLayerCache::remember('redis_only', 300, 60, fn () => 'should_not_compute');
        });
    });

    describe('stampede protection', function () {
        it('executes callback with lock when LockProvider available', function () {
            $callCount = 0;
            $result = TwoLayerCache::remember('stamp_key', 300, 60, function () use (&$callCount) {
                $callCount++;
                return 'locked_value';
            });

            expect($result)->toBe('locked_value');
            expect($callCount)->toBe(1);
        });
    });

    describe('request-level deduplication', function () {
        it('returns from request cache on repeated calls within same request', function () {
            $callCount = 0;
            $callback = function () use (&$callCount) {
                $callCount++;
                return 'value';
            };

            TwoLayerCache::remember('dedup_key', 300, 60, $callback);
            TwoLayerCache::remember('dedup_key', 300, 60, $callback);
            TwoLayerCache::remember('dedup_key', 300, 60, $callback);

            expect($callCount)->toBe(1);
        });
    });
});
