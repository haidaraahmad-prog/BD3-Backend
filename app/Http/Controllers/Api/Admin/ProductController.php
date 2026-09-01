<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Color;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $products = Product::query()
            ->with('colors')
            ->orderByDesc('released_at')
            ->get();

        return ProductResource::collection($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateProduct($request);

        $product = DB::transaction(function () use ($validated) {
            $product = Product::query()->create([
                'slug' => $validated['slug'],
                'name' => $validated['name'],
                'price' => $validated['price'],
                'shape' => $validated['shape'],
                'series' => $validated['series'],
                'lens' => $validated['lens'],
                'image' => $validated['image'],
                'rating' => $validated['rating'] ?? 0,
                'reviews_count' => $validated['reviews'] ?? 0,
                'is_new' => $validated['isNew'] ?? false,
                'released_at' => $validated['createdAt'],
            ]);

            $this->syncColors($product, $validated['colors']);

            return $product->load('colors');
        });

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $slug): ProductResource|JsonResponse
    {
        $product = Product::query()->with('colors')->find($slug);

        if (! $product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return new ProductResource($product);
    }

    public function update(Request $request, string $slug): ProductResource|JsonResponse
    {
        $product = Product::query()->find($slug);

        if (! $product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $validated = $this->validateProduct($request, $slug);

        $product = DB::transaction(function () use ($product, $validated, $slug) {
            $oldSlug = $product->slug;
            $newSlug = $validated['slug'];

            $product->update([
                'slug' => $validated['slug'],
                'name' => $validated['name'],
                'price' => $validated['price'],
                'shape' => $validated['shape'],
                'series' => $validated['series'],
                'lens' => $validated['lens'],
                'image' => $validated['image'],
                'rating' => $validated['rating'] ?? 0,
                'reviews_count' => $validated['reviews'] ?? 0,
                'is_new' => $validated['isNew'] ?? false,
                'released_at' => $validated['createdAt'],
            ]);

            if ($oldSlug !== $newSlug) {
                DB::table('cart_items')->where('product_slug', $oldSlug)->update(['product_slug' => $newSlug]);
            }

            $this->syncColors($product, $validated['colors']);

            return $product->load('colors');
        });

        return new ProductResource($product);
    }

    public function destroy(string $slug): JsonResponse
    {
        $product = Product::query()->find($slug);

        if (! $product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted.']);
    }

    /** @return array<string, mixed> */
    private function validateProduct(Request $request, ?string $existingSlug = null): array
    {
        return $request->validate([
            'slug' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('products', 'slug')->ignore($existingSlug, 'slug'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'shape' => ['required', Rule::in(['aviator', 'geometric', 'shield'])],
            'series' => ['required', Rule::in(['axiom', 'vector', 'apex'])],
            'lens' => ['required', Rule::in(['polarized', 'gradient', 'mirror'])],
            'image' => ['required', 'string', 'max:500'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'reviews' => ['nullable', 'integer', 'min:0'],
            'isNew' => ['nullable', 'boolean'],
            'createdAt' => ['required', 'date'],
            'colors' => ['required', 'array', 'min:1'],
            'colors.*.id' => ['required', 'string', 'exists:colors,id'],
            'colors.*.image' => ['required', 'string', 'max:500'],
        ]);
    }

    /** @param list<array{id: string, image: string}> $colors */
    private function syncColors(Product $product, array $colors): void
    {
        DB::table('product_color')->where('product_slug', $product->slug)->delete();

        foreach ($colors as $index => $color) {
            DB::table('product_color')->insert([
                'product_slug' => $product->slug,
                'color_id' => $color['id'],
                'swatch_image' => $color['image'],
                'sort_order' => $index,
            ]);
        }
    }
}
