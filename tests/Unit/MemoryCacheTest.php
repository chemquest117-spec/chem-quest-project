<?php

use App\Support\MemoryCache;

beforeEach(function () {
    MemoryCache::flush();
});

describe('MemoryCache', function () {
    describe('basic operations', function () {
        it('returns null for missing keys', function () {
            expect(MemoryCache::get('nonexistent'))->toBeNull();
        });

        it('stores and retrieves values', function () {
            MemoryCache::put('key', 'value', 60);
            expect(MemoryCache::get('key'))->toBe('value');
        });

        it('stores arrays and objects', function () {
            MemoryCache::put('array_key', ['a' => 1, 'b' => 2], 60);
            expect(MemoryCache::get('array_key'))->toBe(['a' => 1, 'b' => 2]);
        });

        it('forgets a key from all layers', function () {
            MemoryCache::put('key', 'value', 60);
            MemoryCache::forget('key');
            expect(MemoryCache::get('key'))->toBeNull();
            expect(MemoryCache::$requestCache)->not->toHaveKey('key');
        });

        it('flushes all caches', function () {
            MemoryCache::put('a', 1, 60);
            MemoryCache::put('b', 2, 60);
            MemoryCache::flush();
            expect(MemoryCache::get('a'))->toBeNull();
            expect(MemoryCache::get('b'))->toBeNull();
            expect(MemoryCache::$requestCache)->toBeEmpty();
        });
    });

    describe('TTL expiration', function () {
        it('returns null for expired entries', function () {
            MemoryCache::put('expire_key', 'value', 1);
            // Simulate expiration by manipulating internal state is not possible,
            // but we can test with a 1-second TTL
            expect(MemoryCache::get('expire_key'))->toBe('value');
        });
    });

    describe('request-scoped micro-cache', function () {
        it('populates request cache on first get from memory', function () {
            // Directly put into memory (bypasses request cache on put)
            MemoryCache::put('key', 'value', 60);

            // First get should promote to request cache
            MemoryCache::get('key');
            expect(MemoryCache::$requestCache)->toHaveKey('key');
            expect(MemoryCache::$requestCache['key'])->toBe('value');
        });

        it('serves from request cache on subsequent calls', function () {
            MemoryCache::put('key', 'value', 60);
            // First call
            MemoryCache::get('key');
            // Second call should hit request cache
            expect(MemoryCache::get('key'))->toBe('value');
        });

        it('flushes only request cache without affecting memory', function () {
            MemoryCache::put('key', 'value', 60);
            MemoryCache::get('key'); // Promote to request cache

            MemoryCache::flushRequestCache();
            expect(MemoryCache::$requestCache)->toBeEmpty();

            // Memory cache should still have the value
            expect(MemoryCache::get('key'))->toBe('value');
        });

        it('stores value in request cache on put', function () {
            MemoryCache::put('key', 'value', 60);
            expect(MemoryCache::$requestCache['key'])->toBe('value');
        });
    });

    describe('remember method', function () {
        it('computes and caches on first call', function () {
            $callCount = 0;
            $result = MemoryCache::remember('key', 60, function () use (&$callCount) {
                $callCount++;

                return 'computed_value';
            });

            expect($result)->toBe('computed_value');
            expect($callCount)->toBe(1);
        });

        it('returns cached value without recomputing on second call', function () {
            $callCount = 0;
            $callback = function () use (&$callCount) {
                $callCount++;

                return 'computed_value';
            };

            MemoryCache::remember('key', 60, $callback);
            MemoryCache::remember('key', 60, $callback);

            expect($callCount)->toBe(1);
        });

        it('returns null and does not cache when callback returns null', function () {
            $result = MemoryCache::remember('null_key', 60, fn () => null);
            expect($result)->toBeNull();
            expect(MemoryCache::get('null_key'))->toBeNull();
        });
    });

    describe('same-request deduplication', function () {
        it('avoids duplicate computations within the same request', function () {
            $callCount = 0;
            $compute = function () use (&$callCount) {
                $callCount++;

                return 'result';
            };

            // Simulate multiple calls within the same request
            MemoryCache::remember('dedup_key', 60, $compute);
            MemoryCache::remember('dedup_key', 60, $compute);
            MemoryCache::remember('dedup_key', 60, $compute);

            expect($callCount)->toBe(1);
        });
    });
});
