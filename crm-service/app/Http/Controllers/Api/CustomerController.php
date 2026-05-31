<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $customers = Customer::query()
            ->when($request->string('status')->toString() ?: null, fn ($q, $s) => $q->where('status', $s))
            ->orderBy('name')
            ->get();

        return CustomerResource::collection($customers);
    }

    public function show(Customer $customer): CustomerResource
    {
        return new CustomerResource($customer);
    }
}
