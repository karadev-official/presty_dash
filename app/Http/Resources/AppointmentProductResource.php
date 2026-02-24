<?php

namespace App\Http\Resources;

use App\Models\AppointmentProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AppointmentProduct */
class AppointmentProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'total' => $this->total,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'appointment_id' => $this->appointment_id,
            'product_id' => $this->product_id,

            'appointment' => new AppointmentResource($this->whenLoaded('appointment')),
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
