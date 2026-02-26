<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppointmentServiceOptionRequest;
use App\Http\Resources\AppointmentServiceOptionResource;
use App\Models\AppointmentServiceOption;

class AppointmentServiceOptionController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', AppointmentServiceOption::class);

        return AppointmentServiceOptionResource::collection(AppointmentServiceOption::all());
    }

    public function store(AppointmentServiceOptionRequest $request)
    {
        $this->authorize('create', AppointmentServiceOption::class);

        return new AppointmentServiceOptionResource(AppointmentServiceOption::create($request->validated()));
    }

    public function show(AppointmentServiceOption $appointmentServiceOption)
    {
        $this->authorize('view', $appointmentServiceOption);

        return new AppointmentServiceOptionResource($appointmentServiceOption);
    }

    public function update(AppointmentServiceOptionRequest $request, AppointmentServiceOption $appointmentServiceOption)
    {
        $this->authorize('update', $appointmentServiceOption);

        $appointmentServiceOption->update($request->validated());

        return new AppointmentServiceOptionResource($appointmentServiceOption);
    }

    public function destroy(AppointmentServiceOption $appointmentServiceOption)
    {
        $this->authorize('delete', $appointmentServiceOption);

        $appointmentServiceOption->delete();

        return response()->json();
    }
}
