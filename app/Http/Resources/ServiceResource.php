<?php

namespace App\Http\Resources;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Service */
class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'position' => $this->position,
            'duration' => $this->duration,
            'price' => $this->price,
            'is_active' => $this->is_active,
            'is_online' => $this->is_online,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ],
            'option_groups' => $this->optionGroups->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'client_id' => $group->client_id,
                    'selection_type' => $group->selection_type,
                    'is_required' => $group->is_required,
                    'min_select' => $group->min_select,
                    'max_select' => $group->max_select,
                    'position' => $group->pivot->position,
                    'options' => $group->options->map(function ($option) {
                        return [
                            'id' => $option->id,
                            'client_id' => $option->client_id,
                            'duration' => $option->duration,
                            'name' => $option->name,
                            'price' => $option->price,
                            'position' => $option->position,
                            'is_active' => $option->is_active,
                            'is_online' => $option->is_online,
                            'image_id' => $option->image->id ?? null,
                            'image_url' => $option->image->url ?? null,
                        ];
                    }),
                ];
            }),
            'images' => $this->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->url,
                ];
            })
        ];
    }
}
