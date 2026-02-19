<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $professional_profile_id = $request->user()->ProfessionalProfile->id;
        return CustomerResource::collection(Customer::where('professional_profile_id', $professional_profile_id)->get());
    }

    public function store(CustomerRequest $request)
    {
        $this->authorize('create', Customer::class);

        return new CustomerResource(Customer::create($request->validated()));
    }

    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);

        return new CustomerResource($customer);
    }

    public function update(CustomerRequest $request, Customer $customer)
    {
        $this->authorize('update', $customer);

        $customer->update($request->validated());

        return new CustomerResource($customer);
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('delete', $customer);

        $customer->delete();

        return response()->json();
    }

    public function toggleBlock(Customer $customer)
    {
        $this->authorize('update', $customer);
        $customer->is_blocked = !$customer->is_blocked;
        $customer->save();

        return response()->json();
    }

    public function toggleFavorite(Customer $customer)
    {
        $this->authorize('update', $customer);
        $customer->is_favorite = !$customer->is_favorite;
        $customer->save();

        return response()->json();
    }
}
