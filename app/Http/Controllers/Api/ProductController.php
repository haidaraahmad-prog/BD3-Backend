<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Support\ProductCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->with('colors')
            ->filter($request->only(['shape', 'series', 'lens', 'min', 'max']))
            ->sort($request->query('sort'))
            ->get();

        return ProductResource::collection($products);
    }

    public function show(string $slug): ProductResource|JsonResponse
    {
        $product = Product::query()->with('colors')->find($slug);

        if (! $product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return new ProductResource($product);
    }

    public function gallery(Request $request, string $slug): JsonResponse
    {
        $product = Product::query()->with('colors')->find($slug);

        if (! $product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $colorId = $request->query('color', $product->colors->first()?->id);

        if (! $colorId || ! $product->colors->contains('id', $colorId)) {
            return response()->json(['message' => 'Invalid color.'], 422);
        }

        return response()->json([
            'productId' => $product->slug,
            'colorId' => $colorId,
            'images' => ProductCatalog::galleryFor($product, $colorId),
        ]);
    }

    public function copy(string $slug): JsonResponse
    {
        $product = Product::query()->find($slug);

        if (! $product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return response()->json(ProductCatalog::copyFor($product));
    }
}
