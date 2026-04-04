<?php

namespace App\Http\Middleware;

use App\Services\SystemGuard;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLicense
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Don't protect the suspended route itself to avoid loop
        if ($request->is('sys-suspended') || $request->routeIs('sys.suspended')) {
            return $next($request);
        }

        $guard = app(SystemGuard::class);

        if (! $guard->isSystemHealthy()) {
            return redirect()->route('sys.suspended');
        }

        return $next($request);
    }
}
