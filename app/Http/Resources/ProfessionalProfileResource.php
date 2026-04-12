<?php

namespace App\Http\Resources;

use App\Models\ProfessionalProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProfessionalProfile */
class ProfessionalProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'specialty' => $this->specialty,
            'company_name' => $this->company_name,
            'siret' => $this->siret,
            'description' => $this->description,
            'workplaces' => WorkplaceResource::collection($this->workplaces),
            'pro_user_id' => $this->pro_user_id,
            'loyalty_program' => new LoyaltyProgramResource($this->loyaltyProgram)
        ];
    }
}
