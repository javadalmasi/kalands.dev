<?php

use App\Http\Middleware\Admin;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\EnsureDashboardAuthKey;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\ValidateSignature;
use App\Http\Middleware\Verified;
use App\Http\Middleware\Verify2FA;
use App\Providers\QueueSitemapAutoStartServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        QueueSitemapAutoStartServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectTo(
            guests: '/auth/login',
            users: '/'
        );

        $middleware->append(SecurityHeaders::class);

        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->preventRequestForgery();

        $middleware->alias([
            'admin' => Admin::class,
            'dash.authkey' => EnsureDashboardAuthKey::class,
            'permission' => CheckPermission::class,
            'signed' => ValidateSignature::class,
            'verified' => Verified::class,
            '2fa' => Verify2FA::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('queue:work --queue=sitemap,default --timeout=300 --tries=3')->everyMinute()->withoutOverlapping();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash([
            'current_password',
            'password',
            'password_confirmation',
        ]);
    })->create();
