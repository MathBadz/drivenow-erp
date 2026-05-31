<?php

namespace App\Http\Controllers;

use App\Enums\RentalStatus;
use App\Http\Requests\StoreRentalRequest;
use App\Http\Resources\RentalResource;
use App\Models\Rental;
use App\Repositories\RentalRepository;
use App\Services\VehicleGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class RentalController extends Controller
{
    public function __construct(private readonly RentalRepository $rentals) {}

    public function index(Request $request, VehicleGateway $vehicles): Response
    {
        $filters = [
            'search' => $request->string('search')->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
        ];

        return Inertia::render('reservations/Index', [
            'rentals' => RentalResource::collection($this->rentals->paginate($filters)),
            'stats' => $this->rentals->stats(),
            'filters' => $filters,
            'availableVehicles' => $vehicles->available(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function show(Rental $rental): Response
    {
        return Inertia::render('reservations/Show', [
            'rental' => new RentalResource($rental),
        ]);
    }

    public function store(StoreRentalRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $pickup = Carbon::parse($data['pickup_date']);
        $return = Carbon::parse($data['return_date']);
        $days = max(1, $pickup->diffInDays($return));

        if ($this->rentals->hasConflict((int) $data['vehicle_id'], $data['pickup_date'], $data['return_date'])) {
            return back()
                ->withErrors(['vehicle_id' => 'This vehicle is already booked for the selected dates.'])
                ->with('error', 'Booking conflict — the vehicle is unavailable for those dates.');
        }

        $subtotal = round((float) $data['daily_rate'] * $days, 2);

        Rental::create([
            ...$data,
            'days' => $days,
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'status' => RentalStatus::Pending->value,
        ]);

        return to_route('reservations.index')->with('success', 'Reservation created successfully.');
    }

    public function approve(Rental $rental): RedirectResponse
    {
        return $this->transition(
            $rental,
            from: RentalStatus::Pending,
            to: RentalStatus::Approved,
            stamp: 'approved_at',
            message: 'Reservation approved.',
        );
    }

    public function release(Rental $rental): RedirectResponse
    {
        return $this->transition(
            $rental,
            from: RentalStatus::Approved,
            to: RentalStatus::Active,
            stamp: 'released_at',
            message: 'Vehicle released — rental is now active.',
        );
    }

    public function return(Rental $rental): RedirectResponse
    {
        return $this->transition(
            $rental,
            from: RentalStatus::Active,
            to: RentalStatus::Completed,
            stamp: 'returned_at',
            message: 'Vehicle returned — rental completed.',
        );
    }

    public function cancel(Rental $rental): RedirectResponse
    {
        if (in_array($rental->status, [RentalStatus::Completed, RentalStatus::Cancelled], true)) {
            return back()->with('error', 'This reservation can no longer be cancelled.');
        }

        $rental->update(['status' => RentalStatus::Cancelled->value, 'cancelled_at' => now()]);

        return back()->with('success', 'Reservation cancelled.');
    }

    public function extend(Request $request, Rental $rental): RedirectResponse
    {
        $validated = $request->validate([
            'return_date' => ['required', 'date', 'after:'.$rental->pickup_date->toDateString()],
        ]);

        if (in_array($rental->status, [RentalStatus::Completed, RentalStatus::Cancelled], true)) {
            return back()->with('error', 'This reservation can no longer be extended.');
        }

        $return = Carbon::parse($validated['return_date']);
        $days = max(1, $rental->pickup_date->diffInDays($return));
        $subtotal = round((float) $rental->daily_rate * $days, 2);

        $rental->update([
            'return_date' => $return->toDateString(),
            'days' => $days,
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ]);

        return back()->with('success', "Rental extended to {$return->toFormattedDateString()}.");
    }

    public function destroy(Rental $rental): RedirectResponse
    {
        $rental->delete();

        return to_route('reservations.index')->with('success', 'Reservation deleted.');
    }

    private function transition(Rental $rental, RentalStatus $from, RentalStatus $to, string $stamp, string $message): RedirectResponse
    {
        if ($rental->status !== $from) {
            return back()->with('error', "Cannot move this reservation to {$to->label()}.");
        }

        $rental->update(['status' => $to->value, $stamp => now()]);

        return back()->with('success', $message);
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return array_map(
            fn (RentalStatus $s) => ['value' => $s->value, 'label' => $s->label()],
            RentalStatus::cases(),
        );
    }
}
