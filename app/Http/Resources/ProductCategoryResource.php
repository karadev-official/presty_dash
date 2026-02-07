<?php

namespace App\Http\Resources;

use App\Models\ProductCategory;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

/** @mixin ProductCategory */class ProductCategoryResource extends JsonResource{
    public function toArray(Request $request)
    {
        return [
'id' => $this->id,
'name' => $this->name,
'slug' => $this->slug,
'position' => $this->position,
'created_at' => $this->created_at,
'updated_at' => $this->updated_at,

'user_id' => $this->user_id,//
        ];
    }
}
