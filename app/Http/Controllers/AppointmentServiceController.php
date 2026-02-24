<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppointmentServiceRequest;
use App\Http\Resources\AppointmentServiceResource;
use App\Models\AppointmentService;

class AppointmentServiceController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', AppointmentService::class);

        return AppointmentServiceResource::collection(AppointmentService::all());
    }

    public function store(AppointmentServiceRequest $request)
    {
        $this->authorize('create', AppointmentService::class);

        return new AppointmentServiceResource(AppointmentService::create($request->validated()));
    }

    public function show(AppointmentService $appointmentService)
    {
        $this->authorize('view', $appointmentService);

        return new AppointmentServiceResource($appointmentService);
    }

    public function update(AppointmentServiceRequest $request, AppointmentService $appointmentService)
    {
        $this->authorize('update', $appointmentService);

        $appointmentService->update($request->validated());

        return new AppointmentServiceResource($appointmentService);
    }

    public function destroy(AppointmentService $appointmentService)
    {
        $this->authorize('delete', $appointmentService);

        $appointmentService->delete();

        return response()->json();
    }
}
