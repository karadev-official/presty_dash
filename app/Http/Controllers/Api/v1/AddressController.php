<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', 'address');
        return AddressResource::collection(Address::all());
    }

    public function store(AddressRequest $request)
    {
        return new AddressResource(Address::create($request->validated()));
    }

    public function show(Request $request,Address $address)
    {
        return new AddressResource($address);
    }

    public function update(AddressRequest $request, Address $address)
    {
        Gate::authorize('update', $address);
        $address->update($request->validated());
        return new AddressResource($address);
    }

    public function destroy(Address $address)
    {
        Gate::authorize('delete', $address);
        $address->delete();
        return response()->json();
    }
}
