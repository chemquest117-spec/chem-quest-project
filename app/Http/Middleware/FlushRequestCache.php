<?php

namespace App\Http\Middleware;

use App\Support\MemoryCache;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Flush the request-scoped micro-cache at the start of each request.
 *
 * This ensures that request-level deduplication is isolated per HTTP lifecycle
 * and stale data from a previous request (on the same PHP worker) is not leaked.
 */
class FlushRequestCache
{
    public function handle(Request $request, Closure $next): Response
    {
        MemoryCache::flushRequestCache();

        return $next($request);
    }
}
