<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkplaceRequest;
use App\Http\Resources\WorkplaceResource;
use App\Models\Workplace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class WorkplaceController extends Controller
{
    public function index(Request $request)
    {
        $professional_profile_id = $request->user()->ProfessionalProfile->id;
        return WorkplaceResource::collection(Workplace::where("professional_profile_id", $professional_profile_id)->get());
    }

    public function store(WorkplaceRequest $request)
    {
        $data = $request->validated();
        $data['professional_profile_id'] = $request->user()->ProfessionalProfile->id;
        return new WorkplaceResource(Workplace::create($data));
    }

    public function show(Workplace $workplace)
    {
        Gate::authorize('view', $workplace);
        return new WorkplaceResource($workplace);
    }

    public function update(WorkplaceRequest $request, Workplace $workplace)
    {
        Gate::authorize('update', $workplace);
        $validated = $request->validated();
        if (isset($validated['is_primary']) && $validated['is_primary'] === true) {
            Workplace::where('professional_profile_id', $workplace->professional_profile_id)
                ->where('id', '!=', $workplace->id)
                ->update(['is_primary' => false]);
        }

        $workplace->update($validated);

        return new WorkplaceResource($workplace);
    }

    public function destroy(Workplace $workplace)
    {
        Gate::authorize('delete', $workplace);
        $workplace->delete();
        return response()->json();
    }
}
