<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfessionalProfileRequest;
use App\Http\Resources\ProfessionalProfileResource;
use App\Models\ProfessionalProfile;

class ProfessionalProfileController extends Controller
{
    public function index()
    {
        return ProfessionalProfileResource::collection(ProfessionalProfile::all());
    }

    public function store(ProfessionalProfileRequest $request)
    {
        return new ProfessionalProfileResource(ProfessionalProfile::create($request->validated()));
    }

    public function show(ProfessionalProfile $professionalProfile)
    {
        return new ProfessionalProfileResource($professionalProfile);
    }

    public function update(ProfessionalProfileRequest $request, ProfessionalProfile $professionalProfile)
    {
        $professionalProfile->update($request->validated());

        return new ProfessionalProfileResource($professionalProfile);
    }

    public function destroy(ProfessionalProfile $professionalProfile)
    {
        $professionalProfile->delete();

        return response()->json();
    }
}
