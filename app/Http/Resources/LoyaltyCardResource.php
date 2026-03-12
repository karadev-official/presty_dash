<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoyaltyCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'loyalty_program_id' => $this->loyalty_program_id,
            'total_visits' => $this->total_visits,
            'last_activity_at'=> $this->last_activity_at,
            'is_active' => $this->is_active,
            'customer_id' => $this->customer_id,
            'customer' => [
                'display_name' => $this->customer->display_name,
            ],
            'next_reward' => new LoyaltyRewardResource($this->next_reward),
        ];
    }
}
