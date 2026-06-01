<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards internal service-to-service API endpoints. Callers must present the
 * shared SERVICE_TOKEN via the X-Service-Token header. Without it the request
 * is rejected with 401 — these endpoints expose business data and must not be
 * publicly reachable.
 */
class VerifyServiceToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.service_token', '');
        $provided = (string) $request->header('X-Service-Token', '');

        if ($expected === '' || ! hash_equals($expected, $provided)) {
            abort(401, 'Invalid or missing service token.');
        }

        return $next($request);
    }
}
