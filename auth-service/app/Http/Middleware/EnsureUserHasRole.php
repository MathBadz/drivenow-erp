<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Ensure the authenticated user holds one of the given roles.
     *
     * Usage: `->middleware('role:admin')` or `->middleware('role:admin,staff')`.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role?->value, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
