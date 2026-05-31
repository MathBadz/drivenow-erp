<?php

namespace App\Http\Controllers;

use App\Enums\RentalStatus;
use App\Http\Resources\RentalResource;
use App\Models\Rental;
use App\Repositories\RentalRepository;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly RentalRepository $rentals) {}

    public function __invoke(): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => $this->rentals->stats(),
            'upcoming' => RentalResource::collection(
                Rental::query()
                    ->whereIn('status', [RentalStatus::Pending->value, RentalStatus::Approved->value])
                    ->orderBy('pickup_date')
                    ->limit(6)
                    ->get()
            ),
            'pendingApprovals' => RentalResource::collection(
                Rental::query()
                    ->where('status', RentalStatus::Pending->value)
                    ->latest('id')
                    ->limit(5)
                    ->get()
            ),
        ]);
    }
}
