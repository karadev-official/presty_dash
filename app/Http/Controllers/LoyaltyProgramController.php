<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoyaltyProgramRequest;
use App\Http\Resources\LoyaltyProgramResource;
use App\Models\LoyaltyProgram;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;

class LoyaltyProgramController extends Controller
{
    use ApiResponder;
    public function show(Request $request)
    {
        $professionalProfile = $request->user()->professionalProfile;
        if (!$professionalProfile) {
            return $this->errorResponse('Profil professionnel introuvable', 404);
        }

        // ✅ Créer automatiquement le programme s'il n'existe pas
        $loyaltyProgram = LoyaltyProgram::firstOrCreate(
            [
                'professional_profile_id' => $professionalProfile->id,
            ],
            [
                'name' => 'Carte Fidélité',
                'description' => 'Collectez des visites et bénéficiez de réductions',
                'min_appointment_amount' => 1000,
                'is_active' => true,
            ]
        );
        $loyaltyProgram->load(['rewards' => fn($q) => $q->where('is_active', true)->orderBy('order')]);
        return new LoyaltyProgramResource($loyaltyProgram);
    }

    public function store(LoyaltyProgramRequest $request)
    {
        $data = $request->validated();
        $professionalProfile = $request->user()->professionalProfile;
        $this->authorize('create', LoyaltyProgram::class);
        $data['professional_profile_id'] = $professionalProfile->id;
        $loyaltyProgram = LoyaltyProgram::create($data);

        return new LoyaltyProgramResource($loyaltyProgram);
    }

    public function update(LoyaltyProgramRequest $request)
    {
        $professionalProfile = $request->user()->professionalProfile;
        $program = $professionalProfile->loyaltyProgram;
        $this->authorize('update', $program);

        $program->update($request->validated());
        return new LoyaltyProgramResource($program);
    }

    public function toggleActive(Request $request)
    {
        $professionalProfile = $request->user()->professionalProfile;
        $loyaltyProgram = $professionalProfile->loyaltyProgram;
        $this->authorize('update', $loyaltyProgram);
        $loyaltyProgram->update(['is_active' => !$loyaltyProgram->is_active]);
        return new LoyaltyProgramResource($loyaltyProgram);
    }
}
