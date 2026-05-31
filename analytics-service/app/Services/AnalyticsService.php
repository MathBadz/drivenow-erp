<?php

namespace App\Services;

use Illuminate\Support\Carbon;

/**
 * Produces the analytics payload for the dashboard and reports.
 *
 * In production this aggregates from the sibling services' REST APIs
 * (fleet, rental, billing, maintenance). For the current frontend build it
 * generates a coherent, deterministic dataset so every chart renders without
 * requiring the other services to be online.
 */
class AnalyticsService
{
    /**
     * Twelve trailing month labels ending with the current month.
     *
     * @return array<int, string>
     */
    private function months(): array
    {
        return collect(range(11, 0))
            ->map(fn (int $i) => Carbon::now()->subMonths($i)->format('M'))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboard(): array
    {
        mt_srand(2026);
        $months = $this->months();

        $monthlyRevenue = [];
        $maintenanceMonthly = [];
        $totalRevenue = 0;
        $totalRentals = 0;

        foreach ($months as $index => $label) {
            $rentals = mt_rand(28, 64) + $index * 2;
            $revenue = $rentals * mt_rand(70, 110);
            $totalRevenue += $revenue;
            $totalRentals += $rentals;
            $monthlyRevenue[] = ['month' => $label, 'revenue' => $revenue, 'rentals' => $rentals];
            $maintenanceMonthly[] = ['month' => $label, 'cost' => mt_rand(800, 3200)];
        }

        $topVehicles = collect([
            ['name' => 'Toyota Fortuner', 'plate' => 'SUV-2024'],
            ['name' => 'Toyota Vios', 'plate' => 'ABC-1234'],
            ['name' => 'Ford Ranger', 'plate' => 'PKP-4521'],
            ['name' => 'Honda City', 'plate' => 'XYZ-5678'],
            ['name' => 'Toyota HiAce', 'plate' => 'VAN-0099'],
            ['name' => 'Suzuki Swift', 'plate' => 'HTB-3310'],
        ])->map(function (array $v): array {
            $rentals = mt_rand(40, 160);

            return [...$v, 'rentals' => $rentals, 'revenue' => $rentals * mt_rand(70, 130)];
        })->sortByDesc('rentals')->values()->all();

        $rented = mt_rand(10, 18);
        $available = mt_rand(12, 20);
        $maintenance = mt_rand(2, 6);
        $fleetTotal = $rented + $available + $maintenance;

        return [
            'kpis' => [
                'revenue' => $totalRevenue,
                'rentals' => $totalRentals,
                'utilization' => (int) round($rented / max(1, $fleetTotal) * 100),
                'active_customers' => mt_rand(180, 260),
                'avg_rental_value' => (int) round($totalRevenue / max(1, $totalRentals)),
                'maintenance_cost' => array_sum(array_column($maintenanceMonthly, 'cost')),
            ],
            'monthlyRevenue' => $monthlyRevenue,
            'maintenanceMonthly' => $maintenanceMonthly,
            'topVehicles' => $topVehicles,
            'utilization' => [
                'percent' => (int) round($rented / max(1, $fleetTotal) * 100),
                'rented' => $rented,
                'available' => $available,
                'maintenance' => $maintenance,
                'total' => $fleetTotal,
            ],
            'retention' => [
                'percent' => mt_rand(62, 78),
                'returning' => mt_rand(120, 180),
                'new' => mt_rand(40, 80),
            ],
            'paymentMix' => [
                'cash' => mt_rand(30, 45),
                'gcash' => mt_rand(30, 45),
                'card' => mt_rand(15, 30),
            ],
        ];
    }

    /**
     * Tabular report rows for a date range (generated sample data).
     *
     * @return array<int, array<string, mixed>>
     */
    public function report(string $from, string $to): array
    {
        mt_srand(crc32($from.$to));
        $start = Carbon::parse($from);
        $end = Carbon::parse($to);
        $days = max(1, min(120, $start->diffInDays($end) + 1));

        $rows = [];
        for ($i = 0; $i < $days; $i++) {
            $date = (clone $start)->addDays($i);
            $rentals = mt_rand(2, 14);
            $revenue = $rentals * mt_rand(70, 120);

            $rows[] = [
                'date' => $date->toDateString(),
                'rentals' => $rentals,
                'revenue' => $revenue,
                'returns' => mt_rand(0, $rentals),
                'maintenance_cost' => mt_rand(0, 400),
            ];
        }

        return $rows;
    }
}
