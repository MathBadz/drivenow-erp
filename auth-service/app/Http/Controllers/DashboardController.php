<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the Operations Hub dashboard with statistics, system activity
     * logs and system/runtime details.
     */
    public function __invoke(): Response
    {
        $counts = User::query()
            ->selectRaw('count(*) as users')
            ->selectRaw("sum(case when role = 'admin' then 1 else 0 end) as admins")
            ->selectRaw("sum(case when role = 'staff' then 1 else 0 end) as staff")
            ->selectRaw("sum(case when role = 'maintenance' then 1 else 0 end) as maintenance")
            ->selectRaw("sum(case when role = 'customer' then 1 else 0 end) as customers")
            ->first();

        return Inertia::render('Dashboard', [
            'stats' => [
                'users' => (int) $counts->users,
                'admins' => (int) $counts->admins,
                'staff' => (int) $counts->staff,
                'maintenance' => (int) $counts->maintenance,
                'customers' => (int) $counts->customers,
            ],
            'activity' => ActivityLog::recent(10)->map(fn (ActivityLog $log) => [
                'id' => $log->id,
                'event' => $log->event,
                'description' => $log->description,
                'level' => $log->level,
                'causer' => $log->causer,
                'created_at' => $log->created_at?->toIso8601String(),
                'ago' => $log->created_at?->diffForHumans(),
            ]),
            'system' => [
                'environment' => App::environment(),
                'php_version' => PHP_VERSION,
                'laravel_version' => App::version(),
                'database' => 'PostgreSQL',
                'timezone' => Setting::value('timezone', 'Asia/Manila'),
                'currency' => Setting::value('currency', 'USD'),
                'server_time' => Carbon::now()->toDayDateTimeString(),
                'settings_count' => Setting::query()->count(),
            ],
            'services' => $this->servicesWithStatus(),
        ]);
    }

    /**
     * The service registry with a live online/offline flag per service. Health
     * is probed concurrently against each service's /up endpoint and cached
     * briefly so the dashboard stays responsive and we don't hammer the network.
     *
     * @return array<int, array{name: string, port: int, url: string, self: bool, online: bool}>
     */
    private function servicesWithStatus(): array
    {
        // `url` is the browser-facing origin (dashboard links). `health` is the
        // in-cluster address the server probes — under Docker that's the
        // internal DNS name, since `localhost:800X` from inside a container
        // points at the container itself, not the sibling service.
        $internal = config('services.internal', []);
        $public = config('services.public', []);
        $services = [
            ['name' => 'Operations Hub', 'port' => 8001, 'url' => $public['auth'] ?? 'http://localhost:8001', 'self' => true],
            ['name' => 'Fleet', 'port' => 8002, 'url' => $public['fleet'] ?? 'http://localhost:8002', 'self' => false, 'health' => $internal['fleet'] ?? null],
            ['name' => 'Rentals', 'port' => 8003, 'url' => $public['rental'] ?? 'http://localhost:8003', 'self' => false, 'health' => $internal['rental'] ?? null],
            ['name' => 'Customers', 'port' => 8004, 'url' => $public['crm'] ?? 'http://localhost:8004', 'self' => false, 'health' => $internal['crm'] ?? null],
            ['name' => 'Billing', 'port' => 8005, 'url' => $public['billing'] ?? 'http://localhost:8005', 'self' => false, 'health' => $internal['billing'] ?? null],
            ['name' => 'Maintenance', 'port' => 8006, 'url' => $public['maintenance'] ?? 'http://localhost:8006', 'self' => false, 'health' => $internal['maintenance'] ?? null],
            ['name' => 'Analytics', 'port' => 8007, 'url' => $public['analytics'] ?? 'http://localhost:8007', 'self' => false, 'health' => $internal['analytics'] ?? null],
            ['name' => 'Client Portal', 'port' => 8008, 'url' => $public['client'] ?? 'http://localhost:8008', 'self' => false, 'health' => $internal['client'] ?? null],
        ];

        $status = Cache::remember('hub.service_status', now()->addSeconds(15), function () use ($services) {
            $targets = array_values(array_filter($services, fn (array $s): bool => ! $s['self']));

            try {
                $responses = Http::pool(fn (Pool $pool) => array_map(
                    fn (array $s) => $pool->as((string) $s['port'])->timeout(2)->get(rtrim($s['health'] ?? $s['url'], '/').'/up'),
                    $targets,
                ));
            } catch (\Throwable) {
                $responses = [];
            }

            $online = [];
            foreach ($targets as $s) {
                $response = $responses[(string) $s['port']] ?? null;
                $online[$s['port']] = $response instanceof ClientResponse && $response->successful();
            }

            return $online;
        });

        return array_map(function (array $s) use ($status): array {
            $s['online'] = $s['self'] ? true : ($status[$s['port']] ?? false);
            unset($s['health']); // internal address — never expose to the browser

            return $s;
        }, $services);
    }
}
