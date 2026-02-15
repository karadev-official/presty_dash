<?php

namespace App\Http\Resources;

use App\Models\ProfessionalWorkLocation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProfessionalWorkLocation */
class ProfessionalWorkLocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'location_name' => $this->location_name,
            'is_primary' => $this->is_primary,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'professional_profile_id' => $this->professional_profile_id,
            'address_id' => $this->address_id,

//            'professional_profile' => new ProfessionalProfileResource($this->professionalProfile),
            'address' => new AddressResource($this->address),
        ];
    }
}
