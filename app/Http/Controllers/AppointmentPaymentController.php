<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppointmentPaymentRequest;
use App\Http\Resources\AppointmentPaymentResource;
use App\Models\AppointmentPayment;

class AppointmentPaymentController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', AppointmentPayment::class);

        return AppointmentPaymentResource::collection(AppointmentPayment::all());
    }

    public function store(AppointmentPaymentRequest $request)
    {
        $this->authorize('create', AppointmentPayment::class);

        return new AppointmentPaymentResource(AppointmentPayment::create($request->validated()));
    }

    public function show(AppointmentPayment $appointmentPayment)
    {
        $this->authorize('view', $appointmentPayment);

        return new AppointmentPaymentResource($appointmentPayment);
    }

    public function update(AppointmentPaymentRequest $request, AppointmentPayment $appointmentPayment)
    {
        $this->authorize('update', $appointmentPayment);

        $appointmentPayment->update($request->validated());

        return new AppointmentPaymentResource($appointmentPayment);
    }

    public function destroy(AppointmentPayment $appointmentPayment)
    {
        $this->authorize('delete', $appointmentPayment);

        $appointmentPayment->delete();

        return response()->json();
    }
}
