<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Services\VehicleGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(): Response
    {
        $bookings = Booking::query()
            ->where('user_id', auth()->id())
            ->latest('id')
            ->get();

        return Inertia::render('Dashboard', [
            'bookings' => $bookings,
            'stats' => [
                'total' => $bookings->count(),
                'active' => $bookings->whereIn('status', [BookingStatus::Confirmed, BookingStatus::Active])->count(),
                'completed' => $bookings->where('status', BookingStatus::Completed)->count(),
                'spent' => (float) $bookings->whereNotIn('status', [BookingStatus::Cancelled])->sum('total'),
            ],
        ]);
    }

    public function store(StoreBookingRequest $request, VehicleGateway $vehicles): RedirectResponse
    {
        $vehicle = $vehicles->find((int) $request->integer('vehicle_id'));

        if ($vehicle === null) {
            return back()->with('error', 'That vehicle is no longer available.');
        }

        $pickup = Carbon::parse($request->date('pickup_date'));
        $return = Carbon::parse($request->date('return_date'));
        $days = max(1, $pickup->diffInDays($return));
        $rate = (float) ($vehicle['daily_rate'] ?? 0);

        $booking = Booking::create([
            'user_id' => auth()->id(),
            'vehicle_id' => $vehicle['id'],
            'vehicle_name' => $vehicle['name'],
            'vehicle_category' => $vehicle['category'] ?? null,
            'vehicle_plate' => $vehicle['plate_number'] ?? null,
            'daily_rate' => $rate,
            'pickup_date' => $pickup->toDateString(),
            'return_date' => $return->toDateString(),
            'days' => $days,
            'total' => $rate * $days,
            'status' => BookingStatus::Pending->value,
            'notes' => $request->string('notes')->toString() ?: null,
        ]);

        return to_route('bookings.show', $booking)->with('success', 'Booking request submitted! We will confirm shortly.');
    }

    public function show(Booking $booking): Response
    {
        abort_unless($booking->user_id === auth()->id(), 403);

        return Inertia::render('bookings/Show', [
            'booking' => $booking,
        ]);
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === auth()->id(), 403);

        if (in_array($booking->status, [BookingStatus::Completed, BookingStatus::Cancelled, BookingStatus::Active], true)) {
            return back()->with('error', 'This booking can no longer be cancelled.');
        }

        $booking->update(['status' => BookingStatus::Cancelled->value]);

        return back()->with('success', 'Booking cancelled.');
    }
}
