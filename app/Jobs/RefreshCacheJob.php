<?php

namespace App\Jobs;

use App\Support\MemoryCache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Background job to refresh a stale cache entry.
 *
 * Dispatched by TwoLayerCache when a value is within its stale window.
 * The user gets the stale data immediately; this job rebuilds the cache
 * asynchronously (or synchronously on `sync` queue driver).
 */
class RefreshCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Track which keys have already been dispatched within this process
     * to avoid duplicate dispatches in the same request lifecycle.
     */
    private static array $dispatched = [];

    public int $tries = 1;

    public int $timeout = 30;

    public function __construct(
        public string $key,
        public int $redisTtl,
        public int $memoryTtl,
        public int $staleWindow,
        public string $callbackClass,
        public string $callbackMethod,
        public array $callbackArgs = [],
    ) {}

    public function handle(): void
    {
        try {
            $value = call_user_func(
                [$this->callbackClass, $this->callbackMethod],
                ...$this->callbackArgs
            );

            if ($value === null) {
                return;
            }

            // Wrap with SWR metadata and write to Redis
            $wrapped = [
                'value'      => $value,
                'expires_at' => microtime(true) + $this->redisTtl,
                'stale_until' => microtime(true) + $this->redisTtl + $this->staleWindow,
            ];

            Cache::put($this->key, $wrapped, $this->redisTtl + $this->staleWindow);
            MemoryCache::put($this->key, $wrapped, $this->memoryTtl);

            Log::debug('cache_refresh', [
                'key'   => $this->key,
                'layer' => 'background_job',
            ]);
        } catch (\Throwable $e) {
            Log::warning('cache_refresh_failed', [
                'key'   => $this->key,
                'error' => $e->getMessage(),
            ]);
        } finally {
            unset(self::$dispatched[$this->key]);
        }
    }

    /**
     * Check if a refresh has already been dispatched for this key
     * within the current process lifetime.
     */
    public static function isDispatched(string $key): bool
    {
        return isset(self::$dispatched[$key]);
    }

    /**
     * Mark a key as dispatched within the current process.
     */
    public static function markDispatched(string $key): void
    {
        self::$dispatched[$key] = true;
    }

    /**
     * Reset dispatch tracking (mainly for testing).
     */
    public static function resetDispatched(): void
    {
        self::$dispatched = [];
    }
}
