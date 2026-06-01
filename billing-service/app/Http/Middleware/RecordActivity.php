<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lightweight, uniform audit logging across every service. Records successful
 * state-changing requests (POST/PUT/PATCH/DELETE) to the application log
 * channel with the acting user, route and outcome. Read requests are ignored
 * to keep the log signal-rich.
 */
class RecordActivity
{
    /** @var array<int, string> */
    private array $mutating = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (in_array($request->method(), $this->mutating, true) && $response->getStatusCode() < 400) {
            Log::channel('stack')->info('activity', [
                'service' => config('app.name'),
                'actor' => optional($request->user())->name ?? 'guest',
                'action' => $request->route()?->getName() ?? $request->path(),
                'method' => $request->method(),
                'status' => $response->getStatusCode(),
                'ip' => $request->ip(),
            ]);
        }

        return $response;
    }
}
