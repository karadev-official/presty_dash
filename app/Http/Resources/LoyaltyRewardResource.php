<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoyaltyRewardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'required_visits' => $this->required_visits,
            'discount_amount' => $this->discount_amount,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'loyalty_program_id' => $this->loyalty_program_id
        ];
    }
}
