<?php

namespace App\Http\Controllers;

use App\Http\Resources\PublicProResource;
use App\Models\ProfessionalProfile;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function pros(Request $request)
    {
        $pros = ProfessionalProfile::with([
            'pro',
            'workplaces',
        ])->get();

        return PublicProResource::collection($pros);
    }
}
