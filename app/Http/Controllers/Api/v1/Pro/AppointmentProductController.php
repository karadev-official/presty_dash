<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use App\Http\Requests\AppointmentProductRequest;
use App\Http\Resources\AppointmentProductResource;
use App\Models\AppointmentProduct;

class AppointmentProductController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', AppointmentProduct::class);

        return AppointmentProductResource::collection(AppointmentProduct::all());
    }

    public function store(AppointmentProductRequest $request)
    {
        $this->authorize('create', AppointmentProduct::class);

        return new AppointmentProductResource(AppointmentProduct::create($request->validated()));
    }

    public function show(AppointmentProduct $appointmentProduct)
    {
        $this->authorize('view', $appointmentProduct);

        return new AppointmentProductResource($appointmentProduct);
    }

    public function update(AppointmentProductRequest $request, AppointmentProduct $appointmentProduct)
    {
        $this->authorize('update', $appointmentProduct);

        $appointmentProduct->update($request->validated());

        return new AppointmentProductResource($appointmentProduct);
    }

    public function destroy(AppointmentProduct $appointmentProduct)
    {
        $this->authorize('delete', $appointmentProduct);

        $appointmentProduct->delete();

        return response()->json();
    }
}
