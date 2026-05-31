<?php

namespace App\Http\Controllers;

use App\Enums\CustomerStatus;
use App\Enums\CustomerTier;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Repositories\CustomerRepository;
use App\Services\RentalGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerRepository $customers) {}

    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->string('search')->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
            'tier' => $request->string('tier')->toString() ?: null,
        ];

        return Inertia::render('customers/Index', [
            'customers' => CustomerResource::collection($this->customers->paginate($filters)),
            'stats' => $this->customers->stats(),
            'filters' => $filters,
            'tierOptions' => $this->tierOptions(),
        ]);
    }

    public function show(Customer $customer, RentalGateway $rentals): Response
    {
        $customer->load(['activities', 'feedback']);

        return Inertia::render('customers/Show', [
            'customer' => new CustomerResource($customer),
            'rentalHistory' => $rentals->historyFor($customer->email),
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = Customer::create([
            ...$request->validated(),
            'status' => CustomerStatus::Active->value,
            'joined_at' => now()->toDateString(),
        ]);
        $customer->log('account', 'Customer account created.');

        return back()->with('success', 'Customer created successfully.');
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());
        $customer->log('update', 'Customer profile updated.');

        return back()->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return to_route('customers.index')->with('success', 'Customer deleted.');
    }

    public function blacklist(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $customer->update([
            'status' => CustomerStatus::Blacklisted->value,
            'blacklist_reason' => $validated['reason'],
        ]);
        $customer->log('blacklist', 'Customer blacklisted: '.$validated['reason']);

        return back()->with('success', 'Customer blacklisted.');
    }

    public function unblacklist(Customer $customer): RedirectResponse
    {
        $customer->update([
            'status' => CustomerStatus::Active->value,
            'blacklist_reason' => null,
        ]);
        $customer->log('blacklist', 'Customer removed from blacklist.');

        return back()->with('success', 'Customer restored to active.');
    }

    public function addFeedback(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $customer->feedback()->create($validated);
        $customer->log('feedback', "New feedback received ({$validated['rating']}★).");

        return back()->with('success', 'Feedback recorded.');
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function tierOptions(): array
    {
        return array_map(
            fn (CustomerTier $t) => ['value' => $t->value, 'label' => $t->label()],
            CustomerTier::cases(),
        );
    }
}
