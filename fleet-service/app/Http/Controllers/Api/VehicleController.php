<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VehicleController extends Controller
{
    /**
     * JSON vehicle listing consumed by rental-service and others.
     * Supports `?status=available` and `?category=suv` filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $vehicles = Vehicle::query()
            ->when($request->string('status')->toString() ?: null, fn ($q, $s) => $q->where('status', $s))
            ->when($request->string('category')->toString() ?: null, fn ($q, $c) => $q->where('category', $c))
            ->orderBy('make')
            ->get();

        return VehicleResource::collection($vehicles);
    }

    public function show(Vehicle $vehicle): VehicleResource
    {
        return new VehicleResource($vehicle);
    }
}
