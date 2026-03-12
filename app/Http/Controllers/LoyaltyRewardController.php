<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoyaltyRewardRequest;
use App\Http\Resources\LoyaltyRewardResource;
use App\Models\LoyaltyReward;
use App\Models\ProfessionalProfile;
use Illuminate\Http\Request;

class LoyaltyRewardController extends Controller
{
    public function index(Request $request)
    {
        $professionalProfile = $request->user()->professionalProfile;
        $program = $professionalProfile->loyaltyProgram;
        $this->authorize('view', $program);
        return LoyaltyRewardResource::collection($program->rewards);
    }


    public function store(LoyaltyRewardRequest $request)
    {
        $professionalProfile = $request->user()->professionalProfile;
        $loyaltyProgram = $professionalProfile->loyaltyProgram;
        $this->authorize('update', $loyaltyProgram);
        $data = $request->validated();
        $data['loyalty_program_id'] = $loyaltyProgram->id;
        if (!isset($data['order'])) {
            $data['order'] = $loyaltyProgram->rewards()->max('order') + 1;
        }

        $loyaltyReward = LoyaltyReward::create($data);
        return new LoyaltyRewardResource($loyaltyReward);
    }


    public function update(LoyaltyRewardRequest $request, LoyaltyReward $loyaltyReward)
    {
        $professionalProfile = $request->user()->professionalProfile;
        $program = $professionalProfile->loyaltyProgram;
        $this->authorize('update', $program);
        $loyaltyReward->update($request->validated());
        return new LoyaltyRewardResource($loyaltyReward);
    }

    public function destroy(LoyaltyReward $loyaltyReward)
    {
        $this->authorize('delete', $loyaltyReward);
        $loyaltyReward->delete();
        return response()->json(['message' => 'ok']);
    }
}
