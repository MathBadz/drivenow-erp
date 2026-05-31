<?php

namespace App\Http\Controllers;

use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Repositories\VehicleRepository;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly VehicleRepository $vehicles) {}

    public function __invoke(): Response
    {
        $byCategory = Vehicle::query()
            ->selectRaw('category, count(*) as aggregate')
            ->groupBy('category')
            ->pluck('aggregate', 'category');

        return Inertia::render('Dashboard', [
            'stats' => $this->vehicles->stats(),
            'byCategory' => [
                'sedan' => (int) $byCategory->get('sedan', 0),
                'hatchback' => (int) $byCategory->get('hatchback', 0),
                'suv' => (int) $byCategory->get('suv', 0),
                'van' => (int) $byCategory->get('van', 0),
                'pickup' => (int) $byCategory->get('pickup', 0),
            ],
            'recent' => VehicleResource::collection(
                Vehicle::query()->latest('id')->limit(5)->get()
            ),
        ]);
    }
}
