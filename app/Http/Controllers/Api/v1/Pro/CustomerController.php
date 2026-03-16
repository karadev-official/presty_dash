<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\LoyaltyCard;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use ApiResponder;
    public function index(Request $request)
    {
        $professional_profile_id = $request->user()->ProfessionalProfile->id;
        return CustomerResource::collection(Customer::where('professional_profile_id', $professional_profile_id)->get());
    }

    public function store(CustomerRequest $request)
    {
        $this->authorize('create', Customer::class);
        $customer = Customer::create($request->validated());
        return new CustomerResource($customer->fresh());
    }

    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);
        $loyaltyProgram = $customer->professionalProfile->loyaltyProgram;
        if ($loyaltyProgram?->is_active) {
            LoyaltyCard::firstOrCreate([
                'customer_id' => $customer->id,
                'loyalty_program_id' => $loyaltyProgram->id,
            ], [
                'total_visits' => 0,
                'is_active' => true,
            ]);
        }
        return $this->success(new CustomerResource($customer->fresh()));
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
