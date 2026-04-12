<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicProResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Récupérer le premier workplace (ou créer une logique pour le principal)
        $mainWorkplace = $this->workplaces->first();

        return [
            'id' => $this->id,
            'name' => $this->pro->name ?? 'Professionnel',
            'profession' => $this->company_name ?? 'Non spécifié',
            'category' => $this->specialty, // Ex: 'beauty', 'hair', etc.
            'city' => $mainWorkplace->address->city ?? 'Non spécifié',
            'address' => $mainWorkplace->address ?? null,
            'lat' => $mainWorkplace->address->lat ?? null,
            'lng' => $mainWorkplace->address->lng ?? null,

            // Avatar du pro
            'avatar' => $this->pro->avatar_url ?? $this->avatar_url ?? null,

            // Note moyenne (à calculer selon votre système)
            'rating' => $this->average_rating ?? 0,
            'reviewCount' => $this->reviews_count ?? 0,

            // Informations supplémentaires
            'bio' => $this->bio ?? null,
//            'phone' => $this->phone ?? null,
            'email' => $this->pro->email ?? null,

            // Disponibilité
            'is_available' => $this->is_available ?? true,

            // Dates
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
