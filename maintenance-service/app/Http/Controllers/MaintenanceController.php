<?php

namespace App\Http\Controllers;

use App\Enums\MaintenanceSeverity;
use App\Enums\MaintenanceStatus;
use App\Enums\MaintenanceType;
use App\Http\Requests\StoreMaintenanceRequest;
use App\Http\Requests\UpdateMaintenanceRequest;
use App\Http\Resources\MaintenanceResource;
use App\Models\MaintenanceRecord;
use App\Repositories\MaintenanceRepository;
use App\Services\VehicleGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MaintenanceController extends Controller
{
    public function __construct(private readonly MaintenanceRepository $records) {}

    public function index(Request $request, VehicleGateway $vehicles): Response
    {
        $filters = [
            'search' => $request->string('search')->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
            'type' => $request->string('type')->toString() ?: null,
        ];

        return Inertia::render('maintenance/Index', [
            'records' => MaintenanceResource::collection($this->records->paginate($filters)),
            'stats' => $this->records->stats(),
            'filters' => $filters,
            'vehicles' => $vehicles->all(),
            'typeOptions' => $this->options(MaintenanceType::cases()),
            'severityOptions' => $this->options(MaintenanceSeverity::cases()),
        ]);
    }

    public function show(MaintenanceRecord $record): Response
    {
        return Inertia::render('maintenance/Show', [
            'record' => new MaintenanceResource($record),
        ]);
    }

    public function store(StoreMaintenanceRequest $request): RedirectResponse
    {
        MaintenanceRecord::create([
            ...$request->validated(),
            'status' => MaintenanceStatus::Scheduled->value,
        ]);

        return back()->with('success', 'Maintenance record scheduled.');
    }

    public function update(UpdateMaintenanceRequest $request, MaintenanceRecord $record): RedirectResponse
    {
        $record->update($request->validated());

        return back()->with('success', 'Maintenance record updated.');
    }

    public function destroy(MaintenanceRecord $record): RedirectResponse
    {
        $record->delete();

        return to_route('maintenance.index')->with('success', 'Maintenance record deleted.');
    }

    public function start(MaintenanceRecord $record): RedirectResponse
    {
        if ($record->status !== MaintenanceStatus::Scheduled) {
            return back()->with('error', 'Only scheduled records can be started.');
        }

        $record->update(['status' => MaintenanceStatus::InProgress->value]);

        return back()->with('success', 'Work started on this record.');
    }

    public function complete(MaintenanceRecord $record): RedirectResponse
    {
        if ($record->status !== MaintenanceStatus::InProgress) {
            return back()->with('error', 'Only in-progress records can be completed.');
        }

        $record->update([
            'status' => MaintenanceStatus::Completed->value,
            'completed_date' => now()->toDateString(),
        ]);

        return back()->with('success', 'Maintenance completed — vehicle available.');
    }

    public function cancel(MaintenanceRecord $record): RedirectResponse
    {
        if (in_array($record->status, [MaintenanceStatus::Completed, MaintenanceStatus::Cancelled], true)) {
            return back()->with('error', 'This record can no longer be cancelled.');
        }

        $record->update(['status' => MaintenanceStatus::Cancelled->value]);

        return back()->with('success', 'Maintenance record cancelled.');
    }

    /**
     * @param  array<int, MaintenanceType|MaintenanceSeverity>  $cases
     * @return array<int, array{value: string, label: string}>
     */
    private function options(array $cases): array
    {
        return array_map(fn ($c) => ['value' => $c->value, 'label' => $c->label()], $cases);
    }
}
