<?php

namespace App\Http\Resources;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Customer */
class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'display_name' => $this->display_name,
            'notes' => $this->notes,
            'custom_phone' => $this->custom_phone,
            'custom_email' => $this->custom_email,
            'tags' => $this->tags,
            'preferences' => $this->preferences,
            'is_favorite' => $this->is_favorite,
            'is_blocked' => $this->is_blocked,
            'first_visit_at' => $this->first_visit_at,
            'last_visit_at' => $this->last_visit_at,
            'total_appointments' => $this->total_appointments,
            'total_spent' => $this->total_spent,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'initials' => $this->initials,
//            'professional_profile_id' => $this->professional_profile_id,
//            'user_id' => $this->user_id,
            'avatar' => $this->avatar,
//            'professionalProfile' => new ProfessionalProfileResource($this->professionalProfile),
//            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
