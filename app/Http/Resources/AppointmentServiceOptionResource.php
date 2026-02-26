<?php

namespace App\Http\Resources;

use App\Models\AppointmentServiceOption;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AppointmentServiceOption */
class AppointmentServiceOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'option_name' => $this->option_name,
            'group_name' => $this->group_name,
            'price' => $this->price,
            'duration' => $this->duration,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'appointment_service_id' => $this->appointment_service_id,
            'service_option_id' => $this->service_option_id,
            'service_option_group_id' => $this->service_option_group_id,

            'appointmentService' => new AppointmentServiceResource($this->whenLoaded('appointmentService')),
        ];
    }
}
