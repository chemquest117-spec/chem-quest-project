<?php

namespace App\Support;

use App\Jobs\RefreshCacheJob;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Production-grade hybrid cache with four resilience features:
 *
 * 1. Request Micro-Cache  → MemoryCache request layer (zero-cost dedup)
 * 2. Memory Cache         → per-process in-memory store
 * 3. Redis Cache           → shared across processes (with SWR metadata)
 * 4. Database (callback)   → source of truth behind stampede lock
 *
 * Features:
 * - Stale-While-Revalidate (SWR): serves stale data instantly, refreshes in background
 * - Cache Stampede Protection: atomic locks prevent thundering herd on cache miss
 * - Observability: structured logging for hit/miss/stale/refresh events
 * - Normalized TTLs: via CacheTTL constants
 *
 * Designed to minimize Redis command usage under strict quotas (500K/month).
 */
final class TwoLayerCache
{
    /**
     * Remember a value using the full hybrid pipeline.
     *
     * Flow: request cache → memory → redis (SWR) → [lock → callback] → redis → memory
     *
     * @param  string    $key              Global, non-user-specific cache key.
     * @param  int       $redisTtlSeconds  TTL for Redis store (fresh window).
     * @param  int       $memoryTtlSeconds TTL for in-process memory store.
     * @param  callable  $callback         Closure to regenerate the value from DB.
     * @param  int       $staleWindow      Additional seconds to serve stale data while refreshing.
     *                                     Set to 0 to disable SWR (hard expiration).
     */
    public static function remember(
        string $key,
        int $redisTtlSeconds,
        int $memoryTtlSeconds,
        callable $callback,
        int $staleWindow = 0,
    ): mixed {
        // ── Layer 1 + 2: Request cache → Memory cache ──────────────
        $memValue = MemoryCache::get($key);
        if ($memValue !== null) {
            // If memory holds SWR-wrapped data, unwrap it
            if (is_array($memValue) && array_key_exists('value', $memValue) && array_key_exists('expires_at', $memValue)) {
                return self::handleSwr($key, $memValue, $redisTtlSeconds, $memoryTtlSeconds, $staleWindow, $callback);
            }

            return $memValue;
        }

        // ── Layer 3: Redis cache ───────────────────────────────────
        $cached = Cache::get($key);
        if ($cached !== null) {
            // Check if this is SWR-wrapped data
            if (is_array($cached) && array_key_exists('value', $cached) && array_key_exists('expires_at', $cached)) {
                // Promote to memory cache
                MemoryCache::put($key, $cached, $memoryTtlSeconds);
                Log::info('cache_hit', ['key' => $key, 'layer' => 'redis']);

                return self::handleSwr($key, $cached, $redisTtlSeconds, $memoryTtlSeconds, $staleWindow, $callback);
            }

            // Legacy non-SWR data from Redis — promote to memory
            MemoryCache::put($key, $cached, $memoryTtlSeconds);
            Log::info('cache_hit', ['key' => $key, 'layer' => 'redis']);

            return $cached;
        }

        // ── Layer 4: Database (cache miss) ─────────────────────────
        Log::info('cache_miss', ['key' => $key, 'layer' => 'database']);

        $value = self::computeWithStampedeProtection($key, $callback);

        if ($value === null) {
            return null;
        }

        // Write-through with SWR metadata if stale window is set
        self::writeThrough($key, $value, $redisTtlSeconds, $memoryTtlSeconds, $staleWindow);

        return $value;
    }

    /**
     * Forget a key from all cache layers.
     */
    public static function forget(string $key): void
    {
        MemoryCache::forget($key);
        Cache::forget($key);
    }

    /**
     * Handle Stale-While-Revalidate logic.
     *
     * - If data is fresh → return immediately
     * - If data is stale but within stale window → return stale, trigger background refresh
     * - If data is past stale window → treat as cache miss
     */
    private static function handleSwr(
        string $key,
        array $wrapped,
        int $redisTtlSeconds,
        int $memoryTtlSeconds,
        int $staleWindow,
        callable $callback,
    ): mixed {
        $now = microtime(true);
        $expiresAt = $wrapped['expires_at'] ?? 0;
        $staleUntil = $wrapped['stale_until'] ?? $expiresAt;
        $value = $wrapped['value'];

        if ($now < $expiresAt) {
            // ✅ Fresh — serve immediately
            return $value;
        }

        if ($now < $staleUntil && $staleWindow > 0) {
            // ⚠️ Stale but within grace window — serve stale, refresh in background
            Log::info('cache_stale', ['key' => $key, 'layer' => 'swr', 'action' => 'serving_stale']);
            self::triggerBackgroundRefresh($key, $redisTtlSeconds, $memoryTtlSeconds, $staleWindow, $callback);

            return $value;
        }

        // ❌ Past stale window — treat as expired, remove and recompute
        MemoryCache::forget($key);

        return null; // Caller will fall through to cache miss
    }

    /**
     * Protect against cache stampede using Laravel atomic locks.
     *
     * Only one request rebuilds the cache; others wait briefly (up to 3 seconds)
     * or fall back to executing the callback if locking is unavailable.
     */
    private static function computeWithStampedeProtection(string $key, callable $callback): mixed
    {
        $store = Cache::getStore();

        if ($store instanceof LockProvider) {
            $lockKey = "{$key}:lock";

            try {
                return Cache::lock($lockKey, 10)->block(3, function () use ($callback) {
                    return $callback();
                });
            } catch (\Throwable $e) {
                // Lock timeout or failure — fall through to direct computation
                Log::warning('cache_lock_timeout', ['key' => $key, 'error' => $e->getMessage()]);

                return $callback();
            }
        }

        // No lock provider available — execute callback directly
        return $callback();
    }

    /**
     * Write-through to both Redis (with SWR metadata) and memory.
     */
    private static function writeThrough(
        string $key,
        mixed $value,
        int $redisTtlSeconds,
        int $memoryTtlSeconds,
        int $staleWindow,
    ): void {
        if ($staleWindow > 0) {
            // Store with SWR metadata
            $wrapped = [
                'value'       => $value,
                'expires_at'  => microtime(true) + $redisTtlSeconds,
                'stale_until' => microtime(true) + $redisTtlSeconds + $staleWindow,
            ];

            // Redis TTL covers fresh + stale window
            Cache::put($key, $wrapped, $redisTtlSeconds + $staleWindow);
            MemoryCache::put($key, $wrapped, $memoryTtlSeconds);
        } else {
            // No SWR — simple write-through (backward compatible)
            Cache::put($key, $value, $redisTtlSeconds);
            MemoryCache::put($key, $value, $memoryTtlSeconds);
        }
    }

    /**
     * Trigger a background refresh for stale data.
     *
     * Uses a callable-based approach: if the callback is a Closure, we fall back
     * to synchronous refresh. For class-method callbacks, we dispatch a queued job.
     *
     * Deduplicates within the same process to prevent flooding the queue.
     */
    private static function triggerBackgroundRefresh(
        string $key,
        int $redisTtlSeconds,
        int $memoryTtlSeconds,
        int $staleWindow,
        callable $callback,
    ): void {
        // Prevent duplicate refreshes within the same process
        if (RefreshCacheJob::isDispatched($key)) {
            return;
        }

        RefreshCacheJob::markDispatched($key);

        // Closures can't be serialized for queue dispatch —
        // execute refresh synchronously in a non-blocking way via after-response
        if ($callback instanceof \Closure) {
            app()->terminating(function () use ($key, $callback, $redisTtlSeconds, $memoryTtlSeconds, $staleWindow) {
                try {
                    $value = $callback();
                    if ($value !== null) {
                        self::writeThrough($key, $value, $redisTtlSeconds, $memoryTtlSeconds, $staleWindow);
                        Log::debug('cache_refresh', ['key' => $key, 'layer' => 'swr_terminating']);
                    }
                } catch (\Throwable $e) {
                    Log::warning('cache_refresh_failed', ['key' => $key, 'error' => $e->getMessage()]);
                } finally {
                    RefreshCacheJob::resetDispatched();
                }
            });

            return;
        }

        // For serializable callbacks (class methods), dispatch a queued job
        if (is_array($callback) && count($callback) === 2) {
            RefreshCacheJob::dispatch(
                $key,
                $redisTtlSeconds,
                $memoryTtlSeconds,
                $staleWindow,
                $callback[0],
                $callback[1],
            );
        }
    }
}
