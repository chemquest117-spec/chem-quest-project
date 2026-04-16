<?php

use App\Support\CacheTTL;

describe('CacheTTL', function () {
    describe('static data constants', function () {
        it('has Redis TTL between 1 and 6 hours', function () {
            expect(CacheTTL::STATIC_REDIS)->toBeGreaterThanOrEqual(3600);
            expect(CacheTTL::STATIC_REDIS)->toBeLessThanOrEqual(6 * 3600);
        });

        it('has memory TTL shorter than Redis TTL', function () {
            expect(CacheTTL::STATIC_MEMORY)->toBeLessThan(CacheTTL::STATIC_REDIS);
        });

        it('has stale window defined', function () {
            expect(CacheTTL::STATIC_STALE)->toBeGreaterThan(0);
        });
    });

    describe('semi-dynamic data constants', function () {
        it('has Redis TTL between 5 and 30 minutes', function () {
            expect(CacheTTL::SEMI_REDIS)->toBeGreaterThanOrEqual(5 * 60);
            expect(CacheTTL::SEMI_REDIS)->toBeLessThanOrEqual(30 * 60);
        });

        it('has memory TTL shorter than Redis TTL', function () {
            expect(CacheTTL::SEMI_MEMORY)->toBeLessThan(CacheTTL::SEMI_REDIS);
        });
    });

    describe('dynamic data constants', function () {
        it('has Redis TTL between 1 and 5 minutes', function () {
            expect(CacheTTL::DYNAMIC_REDIS)->toBeGreaterThanOrEqual(60);
            expect(CacheTTL::DYNAMIC_REDIS)->toBeLessThanOrEqual(5 * 60);
        });

        it('has memory TTL shorter than Redis TTL', function () {
            expect(CacheTTL::DYNAMIC_MEMORY)->toBeLessThan(CacheTTL::DYNAMIC_REDIS);
        });

        it('has the shortest Redis TTL of all tiers', function () {
            expect(CacheTTL::DYNAMIC_REDIS)->toBeLessThan(CacheTTL::SEMI_REDIS);
            expect(CacheTTL::SEMI_REDIS)->toBeLessThan(CacheTTL::STATIC_REDIS);
        });
    });

    describe('user memory-only constants', function () {
        it('has a reasonable TTL for per-user data', function () {
            expect(CacheTTL::USER_MEMORY)->toBeGreaterThanOrEqual(300);
            expect(CacheTTL::USER_MEMORY)->toBeLessThanOrEqual(3600);
        });
    });

    describe('TTL hierarchy', function () {
        it('maintains memory TTL < Redis TTL across all tiers', function () {
            expect(CacheTTL::STATIC_MEMORY)->toBeLessThan(CacheTTL::STATIC_REDIS);
            expect(CacheTTL::SEMI_MEMORY)->toBeLessThan(CacheTTL::SEMI_REDIS);
            expect(CacheTTL::DYNAMIC_MEMORY)->toBeLessThan(CacheTTL::DYNAMIC_REDIS);
        });

        it('maintains dynamic < semi < static for Redis TTL', function () {
            expect(CacheTTL::DYNAMIC_REDIS)->toBeLessThan(CacheTTL::SEMI_REDIS);
            expect(CacheTTL::SEMI_REDIS)->toBeLessThan(CacheTTL::STATIC_REDIS);
        });
    });
});
