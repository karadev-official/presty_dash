<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'is_active' => $this->is_active,
            'is_online' => $this->is_online,
            'position' => $this->position,
            'agenda_color' => $this->agenda_color,
            'services' => ServiceResource::collection($this->services)

        ];
    }
}
