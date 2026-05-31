<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly CustomerRepository $customers) {}

    public function __invoke(): Response
    {
        $byTier = Customer::query()
            ->selectRaw('tier, count(*) as aggregate')
            ->groupBy('tier')
            ->pluck('aggregate', 'tier');

        return Inertia::render('Dashboard', [
            'stats' => $this->customers->stats(),
            'byTier' => [
                'regular' => (int) $byTier->get('regular', 0),
                'silver' => (int) $byTier->get('silver', 0),
                'gold' => (int) $byTier->get('gold', 0),
                'platinum' => (int) $byTier->get('platinum', 0),
            ],
            'loyaltyLeaders' => CustomerResource::collection(
                Customer::query()->orderByDesc('loyalty_points')->limit(5)->get()
            ),
            'recent' => CustomerResource::collection(
                Customer::query()->latest('id')->limit(5)->get()
            ),
        ]);
    }
}
