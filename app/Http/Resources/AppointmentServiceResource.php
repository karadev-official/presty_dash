<?php

namespace App\Http\Resources;

use App\Models\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AppointmentService */
class AppointmentServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'price' => $this->price,
            'name' => $this->name,
            'duration' => $this->duration,
            'quantity' => $this->quantity,
            'total' => $this->total,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
//            'updated_at' => $this->updated_at,

            'appointment_id' => $this->appointment_id,
            'service_id' => $this->service_id,
            'options' => AppointmentServiceOptionResource::collection($this->options)


//            'appointment' => new AppointmentResource($this->whenLoaded('appointment')),
        ];
    }
}
