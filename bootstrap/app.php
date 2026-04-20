<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckBanned;
use App\Http\Middleware\CheckLicense;
use App\Http\Middleware\FlushRequestCache;
use App\Http\Middleware\SetLocale;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->web(prepend: [
            FlushRequestCache::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
            CheckLicense::class,
            CheckBanned::class,
        ]);

        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);

        // Convert common DB errors into friendly localized messages
        $exceptions->render(function (QueryException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'A database error occurred. Our team has been notified.',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ], 500);
            }

            return response()->view('errors.500', [
                'exception' => $e,
                'friendlyDefault' => 'We are having trouble connecting to our database. This is usually temporary—please try again in a moment.',
            ], 500);
        });

        // Handle expired sessions / CSRF tokens gracefully
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            return back()->with('error', 'Your session has expired. please refresh and try again.');
        });
    })->create();
