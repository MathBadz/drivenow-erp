<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RentalResource;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RentalController extends Controller
{
    /**
     * JSON rental listing consumed by billing-service and analytics.
     * Supports `?status=active`.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $rentals = Rental::query()
            ->when($request->string('status')->toString() ?: null, fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('id')
            ->get();

        return RentalResource::collection($rentals);
    }

    public function show(Rental $rental): RentalResource
    {
        return new RentalResource($rental);
    }
}
