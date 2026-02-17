<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfessionalWorkplaceRequest;
use App\Http\Resources\ProfessionalWorkplaceResource;
use App\Models\ProfessionalWorkplace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProfessionalWorkplaceController extends Controller
{
    public function index(Request $request)
    {
        $professional_profile_id = $request->user()->ProfessionalProfile->id;
        return ProfessionalWorkplaceResource::collection(ProfessionalWorkplace::where("professional_profile_id", $professional_profile_id)->get());
    }

    public function store(ProfessionalWorkplaceRequest $request)
    {
        $data = $request->validated();
        $data['professional_profile_id'] = $request->user()->ProfessionalProfile->id;
        return new ProfessionalWorkplaceResource(ProfessionalWorkplace::create($data));
    }

    public function show(ProfessionalWorkplace $workplace)
    {
        Gate::authorize('view', $workplace);
        return new ProfessionalWorkplaceResource($workplace);
    }

    public function update(ProfessionalWorkplaceRequest $request, ProfessionalWorkplace $workplace)
    {
        Gate::authorize('update', $workplace);
        $validated = $request->validated();
        if (isset($validated['is_primary']) && $validated['is_primary'] === true) {
            ProfessionalWorkplace::where('professional_profile_id', $workplace->professional_profile_id)
                ->where('id', '!=', $workplace->id)
                ->update(['is_primary' => false]);
        }

        $workplace->update($validated);

        return new ProfessionalWorkplaceResource($workplace);
    }

    public function destroy(ProfessionalWorkplace $professionalWorkplace)
    {
        Gate::authorize('delete', $professionalWorkplace);
        $professionalWorkplace->delete();
        return response()->json();
    }
}
