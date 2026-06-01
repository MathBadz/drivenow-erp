<?php

namespace App\Services;

/**
 * Minimal, dependency-free HS256 JWT issuer/verifier used for stateless
 * cross-service single sign-on. The signing secret is shared between services
 * (a KEY, never a database) — see config('services.jwt_secret').
 */
class JwtService
{
    private function secret(): string
    {
        return (string) config('services.jwt_secret', '');
    }

    private function b64(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function unb64(string $data): string
    {
        return (string) base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Issue a signed token for the given claims.
     *
     * @param  array<string, mixed>  $claims
     */
    public function issue(array $claims, int $ttlSeconds = 7200): string
    {
        $header = $this->b64((string) json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $now = time();
        $payload = $this->b64((string) json_encode(array_merge($claims, [
            'iat' => $now,
            'exp' => $now + $ttlSeconds,
        ])));
        $signature = $this->b64(hash_hmac('sha256', "{$header}.{$payload}", $this->secret(), true));

        return "{$header}.{$payload}.{$signature}";
    }

    /**
     * Verify a token's signature and expiry. Returns the claims or null.
     *
     * @return array<string, mixed>|null
     */
    public function verify(?string $token): ?array
    {
        if (! $token || $this->secret() === '' || substr_count($token, '.') !== 2) {
            return null;
        }

        [$header, $payload, $signature] = explode('.', $token);
        $expected = $this->b64(hash_hmac('sha256', "{$header}.{$payload}", $this->secret(), true));

        if (! hash_equals($expected, $signature)) {
            return null;
        }

        $claims = json_decode($this->unb64($payload), true);

        if (! is_array($claims) || (isset($claims['exp']) && (int) $claims['exp'] < time())) {
            return null;
        }

        return $claims;
    }
}
