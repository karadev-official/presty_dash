<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfessionalWorkLocationRequest;
use App\Http\Resources\ProfessionalWorkLocationResource;
use App\Models\ProfessionalWorkLocation;
use Illuminate\Http\Request;

class ProfessionalWorkLocationController extends Controller
{
    public function index(Request $request)
    {
        $professional_profile_id = $request->user()->ProfessionalProfile->id;
        return ProfessionalWorkLocationResource::collection(ProfessionalWorkLocation::where("professional_profile_id", $professional_profile_id)->get());
    }

    public function store(ProfessionalWorkLocationRequest $request)
    {
        return new ProfessionalWorkLocationResource(ProfessionalWorkLocation::create($request->validated()));
    }

    public function show(ProfessionalWorkLocation $professionalWorkLocation)
    {
        return new ProfessionalWorkLocationResource($professionalWorkLocation);
    }

    public function update(ProfessionalWorkLocationRequest $request, ProfessionalWorkLocation $professionalWorkLocation)
    {
        $professionalWorkLocation->update($request->validated());

        return new ProfessionalWorkLocationResource($professionalWorkLocation);
    }

    public function destroy(ProfessionalWorkLocation $professionalWorkLocation)
    {
        $professionalWorkLocation->delete();

        return response()->json();
    }
}
