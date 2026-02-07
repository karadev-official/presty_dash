<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

/** @mixin Product */class ProductResource extends JsonResource{
    public function toArray(Request $request)
    {
        return [
'id' => $this->id,
'name' => $this->name,
'slug' => $this->slug,
'description' => $this->description,
'position' => $this->position,
'price' => $this->price,
'quantity' => $this->quantity,
'is_active' => $this->is_active,
'is_online' => $this->is_online,
'created_at' => $this->created_at,
'updated_at' => $this->updated_at,

'user_id' => $this->user_id,
'product_category_id' => $this->product_category_id,

'productCategory' => new ProductCategoryResource($this->whenLoaded('productCategory')),//
        ];
    }
}
