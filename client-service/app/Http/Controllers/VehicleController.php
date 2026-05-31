<?php

namespace App\Http\Controllers;

use App\Services\VehicleGateway;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VehicleController extends Controller
{
    public function index(Request $request, VehicleGateway $vehicles): Response
    {
        $category = $request->string('category')->toString() ?: null;
        $search = $request->string('search')->toString() ?: null;

        $list = collect($vehicles->available())
            ->when($category, fn ($c) => $c->where('category', $category))
            ->when($search, fn ($c) => $c->filter(
                fn ($v) => str_contains(strtolower($v['name'] ?? ''), strtolower($search))
            ))
            ->values()
            ->all();

        return Inertia::render('vehicles/Index', [
            'vehicles' => $list,
            'filters' => ['category' => $category, 'search' => $search],
        ]);
    }

    public function show(int $vehicle, VehicleGateway $vehicles): Response
    {
        $found = $vehicles->find($vehicle);

        abort_if($found === null, 404);

        return Inertia::render('vehicles/Show', [
            'vehicle' => $found,
            'related' => collect($vehicles->available())
                ->where('id', '!=', $vehicle)
                ->take(3)
                ->values()
                ->all(),
        ]);
    }
}
