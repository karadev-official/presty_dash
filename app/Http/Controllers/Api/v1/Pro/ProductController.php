<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        return ProductResource::collection(Product::where("user_id", $request->user()->id)->get());
    }

    public function store(ProductRequest $request)
    {
        $data = $request->validated();
        $product = Product::create([
            "user_id" => $request->user()->id,
            ...$data
        ]);
        if (array_key_exists('image_ids', $data)) {
            $product->images()->sync($data['image_ids']);
        }
        return new ProductResource($product);
    }

    public function show(Request $request,Product $product)
    {
        abort_unless($request->user()->id == $product->user_id, 404);
        return new ProductResource($product);
    }

    public function update(ProductRequest $request, Product $product)
    {
        abort_unless($request->user()->id == $product->user_id, 404);
        $data = $request->validated();
        $product->update($request->validated());
        if (array_key_exists('image_ids', $data)) {
            $product->images()->sync($data['image_ids']);
        }
        return new ProductResource($product);
    }

    public function destroy(Request $request, Product $product)
    {
        abort_unless($request->user()->id == $product->user_id, 404);
        $product->delete();
        return response()->json();
    }
}
