<?php

use App\Exceptions\ApiException;
use App\Http\Middleware\EnsureApiEmailIsVerified;
use App\Http\Middleware\EnsureFullAccess;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Required behind cPanel/LiteSpeed (and Cloudflare) so HTTPS and
        // session cookies are detected correctly — otherwise forms 419.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'api.verified' => EnsureApiEmailIsVerified::class,
            'full.access' => EnsureFullAccess::class,
        ]);

        // Laravel sorts throttle ahead of unlisted middleware, so list these first:
        // a request refused with 403 must not count toward the per user limit (spec 0002, AC-12).
        $middleware->prependToPriorityList(ThrottleRequests::class, EnsureApiEmailIsVerified::class);
        $middleware->prependToPriorityList(ThrottleRequests::class, EnsureFullAccess::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*') || $request->expectsJson());

        // Every error under /api/ becomes { message, code }, with no exception detail (spec 0002, AC-13).
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*') || $e instanceof HttpResponseException) {
                return null;
            }

            return ApiException::from($e)->render();
        });
    })->create();
