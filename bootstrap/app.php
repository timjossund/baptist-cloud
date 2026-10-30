<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('follows:send-digests')
            ->dailyAt('07:00')
            ->timezone('America/Chicago')
            ->withoutOverlapping();
    })
    ->withMiddleware(function (Middleware $middleware) {
        // Required behind cPanel/LiteSpeed (and Cloudflare) so HTTPS and
        // session cookies are detected correctly — otherwise forms 419.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
