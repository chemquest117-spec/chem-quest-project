<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Enhanced per-process in-memory TTL cache with request-scoped micro-caching.
 *
 * Four-layer lookup order:
 * 1. Request cache  — zero-cost dedup within a single HTTP request
 * 2. Memory cache   — persists across requests within the same PHP worker
 *
 * Notes:
 * - Persists across requests handled by the same PHP worker process.
 * - Does not share across multiple PHP workers (acceptable for single-instance).
 * - Request cache is reset at the start of each new request lifecycle.
 */
final class MemoryCache
{
    /** @var array<string, array{expiresAt: float, value: mixed}> Per-process cache with TTL */
    private static array $store = [];

    /** @var array<string, mixed> Request-scoped cache — no TTL, cleared per-request */
    public static array $requestCache = [];

    /**
     * Get a value, checking request cache first, then memory cache.
     */
    public static function get(string $key): mixed
    {
        // Layer 1: Request cache (instant, no TTL check)
        if (array_key_exists($key, self::$requestCache)) {
            Log::debug('cache_hit', ['key' => $key, 'layer' => 'request']);

            return self::$requestCache[$key];
        }

        // Layer 2: Memory cache (per-process, TTL-aware)
        $item = self::$store[$key] ?? null;
        if (! $item) {
            return null;
        }

        if ($item['expiresAt'] < microtime(true)) {
            unset(self::$store[$key]);

            return null;
        }

        // Promote to request cache for subsequent calls in this request
        self::$requestCache[$key] = $item['value'];
        Log::debug('cache_hit', ['key' => $key, 'layer' => 'memory']);

        return $item['value'];
    }

    /**
     * Store a value in both request cache and memory cache.
     */
    public static function put(string $key, mixed $value, int $ttlSeconds): void
    {
        self::$store[$key] = [
            'expiresAt' => microtime(true) + max(1, $ttlSeconds),
            'value' => $value,
        ];

        // Also populate request cache
        self::$requestCache[$key] = $value;
    }

    /**
     * Remember a value: check request → memory → compute via callback.
     */
    public static function remember(string $key, int $ttlSeconds, callable $callback): mixed
    {
        $hit = self::get($key);
        if ($hit !== null) {
            return $hit;
        }

        $value = $callback();

        if ($value !== null) {
            self::put($key, $value, $ttlSeconds);
            Log::debug('cache_miss', ['key' => $key, 'layer' => 'memory', 'action' => 'computed']);
        }

        return $value;
    }

    /**
     * Remove a key from both request cache and memory cache.
     */
    public static function forget(string $key): void
    {
        unset(self::$store[$key], self::$requestCache[$key]);
    }

    /**
     * Flush all caches (both request and memory).
     */
    public static function flush(): void
    {
        self::$store = [];
        self::$requestCache = [];
    }

    /**
     * Clear only the request-scoped cache.
     * Should be called at the start of each request lifecycle.
     */
    public static function flushRequestCache(): void
    {
        self::$requestCache = [];
    }
}
