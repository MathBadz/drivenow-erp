<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Reads a customer's rental history from rental-service. Returns an empty
 * list when the service is unreachable so the profile page still renders.
 */
class RentalGateway
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function historyFor(string $email): array
    {
        $base = config('services.rental.url');

        if (! $base) {
            return [];
        }

        try {
            $response = Http::timeout(2)->acceptJson()
                ->withHeaders(['X-Service-Token' => (string) config('services.service_token')])
                ->get("{$base}/api/v1/rentals");

            if ($response->successful()) {
                return collect($response->json('data') ?? [])
                    ->where('customer_email', $email)
                    ->values()
                    ->all();
            }
        } catch (\Throwable) {
            // rental-service offline.
        }

        return [];
    }
}
