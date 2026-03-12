<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $professionalProfile = $request->user()->professionalProfile;
        $categoriesIds = $professionalProfile->productCategories()->pluck('id')->toArray();
        return ProductResource::collection(Product::whereIn("product_category_id", $categoriesIds)->get());
    }

    public function store(ProductRequest $request)
    {
        $this->authorize('create', ProductCategory::class);
        $data = $request->validated();
        $product = Product::create($data);
        if (array_key_exists('image_ids', $data)) {
            $product->images()->sync($data['image_ids']);
        }
        return new ProductResource($product);
    }

    public function show(Product $product)
    {
        $this->authorize('view', $product->category);
        return new ProductResource($product);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $this->authorize('update', $product->category);
        $data = $request->validated();
        $product->update($request->validated());
        if (array_key_exists('image_ids', $data)) {
            $product->images()->sync($data['image_ids']);
        }
        return new ProductResource($product);
    }

    public function destroy(Request $request, Product $product)
    {
        $this->authorize('delete', $product->category);
        $product->delete();
        return response()->json();
    }
}
