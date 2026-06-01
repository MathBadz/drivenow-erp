<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state', 'drivenow_token']);

        // Behind the gateway/reverse proxy — honour X-Forwarded-* so client IP,
        // host and scheme (for throttling, logging, URL generation) are real.
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            \App\Http\Middleware\AuthenticateWithJwt::class,
            \App\Http\Middleware\RecordActivity::class,
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // The framework's middleware-priority sorting hoists the `auth`
        // middleware (it implements AuthenticatesRequests) ahead of our
        // appended JWT middleware, so `auth` would check for a user before the
        // JWT cookie establishes one. Pin AuthenticateWithJwt to run *before*
        // Authenticate so cross-service SSO works on `auth`-guarded routes.
        $middleware->prependToPriorityList(
            before: \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            prepend: \App\Http\Middleware\AuthenticateWithJwt::class,
        );
        $middleware->redirectGuestsTo(fn (\Illuminate\Http\Request $request) => rtrim((string) config('services.auth.url', 'http://localhost:8001'), '/').'/sso/bounce?return='.urlencode(rtrim((string) config('app.url'), '/').'/'.ltrim($request->path(), '/').($request->getQueryString() ? '?'.$request->getQueryString() : '')));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
