<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoyaltyProgramResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'min_appointment_amount' => $this->min_appointment_amount,
            'is_active' => $this->is_active,
            'professional_profile_id' => $this->professional_profile_id,
            'cards' => $this->cards,
            'rewards' => $this->rewards
        ];
    }
}
