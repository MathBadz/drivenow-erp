<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the Operations Hub dashboard with high-level user statistics.
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
        ]);
    }
}
