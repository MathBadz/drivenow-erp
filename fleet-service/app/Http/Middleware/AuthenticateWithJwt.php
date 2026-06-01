<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Establishes the authenticated staff user from the cross-service JWT cookie
 * issued by auth-service. Identity is carried entirely by the signed token —
 * this service never queries another service's database. The user is set for
 * the current request only (stateless); no user rows are persisted locally.
 */
class AuthenticateWithJwt
{
    public function __construct(private JwtService $jwt) {}

    public function handle(Request $request, Closure $next): Response
    {
        $claims = $this->jwt->verify($request->cookie('drivenow_token'));

        if ($claims !== null && isset($claims['sub'])) {
            $user = new User();
            $user->forceFill([
                'id' => $claims['sub'],
                'name' => $claims['name'] ?? 'User',
                'email' => $claims['email'] ?? '',
                'role' => $claims['role'] ?? null,
                'role_label' => $claims['role_label'] ?? null,
            ]);
            $user->exists = true;

            Auth::setUser($user);
        }

        return $next($request);
    }
}
