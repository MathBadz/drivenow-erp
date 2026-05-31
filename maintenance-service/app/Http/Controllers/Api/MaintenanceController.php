<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MaintenanceResource;
use App\Models\MaintenanceRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MaintenanceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $records = MaintenanceRecord::query()
            ->when($request->string('status')->toString() ?: null, fn ($q, $s) => $q->where('status', $s))
            ->when($request->integer('vehicle_id') ?: null, fn ($q, $id) => $q->where('vehicle_id', $id))
            ->orderByDesc('id')
            ->get();

        return MaintenanceResource::collection($records);
    }

    public function show(MaintenanceRecord $record): MaintenanceResource
    {
        return new MaintenanceResource($record);
    }
}
