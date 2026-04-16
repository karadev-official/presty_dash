<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicProDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->company_name ?? $this->pro->name ?? 'Professionnel',
            'avatar_url' => $this->pro?->avatar_url ?? null,
            'profession' => $this->specialty ?? 'Non spécifié',
            'bio' => $this->description ?? null,
            'service_categories' => ServiceCategoryResource::collection($this->serviceCategories),
            'product_categories' => ProductCategoryResource::collection($this->productCategories),
        ];
    }
}
