<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure the authenticated user holds one of the given roles. Identity here is
 * established by the cross-service JWT (see AuthenticateWithJwt), which carries
 * the role as a plain string claim — so we compare the raw string (and tolerate
 * an enum, defensively).
 *
 * Usage: `->middleware('role:admin,staff')`.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $role = $user?->role;
        $role = is_object($role) ? ($role->value ?? null) : $role;

        if (! $user || ! in_array($role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
