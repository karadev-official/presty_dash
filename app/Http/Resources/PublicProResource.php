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

            'name' => $this->company_name ?? $this->pro->name ?? 'Professionnel',

            'profession' => $this->specialty ?? 'Non spécifié',

            'city' => $mainWorkplace?->address?->city ?? 'Non spécifié',

            // Coordonnées GPS pour le calcul de distance et la carte
            'lat' => $mainWorkplace?->address?->lat ?? null,
            'lng' => $mainWorkplace?->address?->lng ?? null,

            // Avatar du pro
            'avatarUrl' => $this->pro?->avatar_url ?? null,

            // Informations supplémentaires
            'bio' => $this->description ?? null,

            // Prestations vides pour l'instant (requis pour la recherche)
            // Le front cherche dans pro.prestations?.some()
            'prestations' => [],

            // Category vide pour l'instant (requis pour le filtrage)
            // Le front filtre par category
            'category' => null,
        ];
    }
}
