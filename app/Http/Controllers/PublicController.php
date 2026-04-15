<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProfessionalProfileResource;
use App\Http\Resources\PublicProDetailResource;
use App\Http\Resources\PublicProResource;
use App\Models\ProfessionalProfile;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function pros(Request $request)
    {
        $pros = ProfessionalProfile::with([
            'pro',
            'workplaces.address',
        ])->get();

        return PublicProResource::collection($pros);
    }

    public function proById(Request $request, $id)
    {
        $pro = ProfessionalProfile::where('id', $id)
            ->firstOrFail();
        return new PublicProDetailResource($pro);
    }
}
