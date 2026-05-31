<?php

namespace App\Http\Controllers;

use App\Enums\VehicleCategory;
use App\Enums\VehicleStatus;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Repositories\VehicleRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VehicleController extends Controller
{
    public function __construct(private readonly VehicleRepository $vehicles) {}

    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->string('search')->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
            'category' => $request->string('category')->toString() ?: null,
        ];

        return Inertia::render('vehicles/Index', [
            'vehicles' => VehicleResource::collection($this->vehicles->paginate($filters)),
            'stats' => $this->vehicles->stats(),
            'filters' => $filters,
            'categoryOptions' => $this->categoryOptions(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function show(Vehicle $vehicle): Response
    {
        return Inertia::render('vehicles/Show', [
            'vehicle' => new VehicleResource($vehicle),
        ]);
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        Vehicle::create($request->validated());

        return back()->with('success', 'Vehicle added to the fleet.');
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update($request->validated());

        return back()->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete();

        return to_route('vehicles.index')->with('success', 'Vehicle removed from the fleet.');
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function categoryOptions(): array
    {
        return array_map(
            fn (VehicleCategory $c) => ['value' => $c->value, 'label' => $c->label()],
            VehicleCategory::cases(),
        );
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return array_map(
            fn (VehicleStatus $s) => ['value' => $s->value, 'label' => $s->label()],
            VehicleStatus::cases(),
        );
    }
}
