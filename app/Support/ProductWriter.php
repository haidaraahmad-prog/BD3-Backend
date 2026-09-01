<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductWriter
{
    /** @var array<string, array<string, string>> */
    private const SWATCH = [
        'aviator' => [
            'black' => '/images/products/swatch-aviator-black.webp',
            'steel' => '/images/products/swatch-aviator-steel.webp',
            'powder' => '/images/products/swatch-aviator-powder.webp',
            'bronze' => '/images/products/swatch-aviator-bronze.webp',
        ],
        'geometric' => [
            'black' => '/images/products/swatch-geometric-black.webp',
            'graphite' => '/images/products/swatch-geometric-graphite.webp',
            'silver' => '/images/products/swatch-geometric-silver.webp',
            'powder' => '/images/products/swatch-geometric-powder.webp',
            'steel' => '/images/products/swatch-geometric-steel.webp',
        ],
        'shield' => [
            'black' => '/images/products/swatch-shield-black.webp',
            'steel' => '/images/products/swatch-shield-steel.webp',
            'powder' => '/images/products/swatch-shield-powder.webp',
            'silver' => '/images/products/swatch-shield-silver.webp',
            'graphite' => '/images/products/swatch-shield-graphite.webp',
        ],
    ];

    /** @return array<string, mixed> */
    public static function validateWeb(array $input, ?string $existingSlug = null): array
    {
        return validator($input, [
            'slug' => [
                'required', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/',
                Rule::unique('products', 'slug')->ignore($existingSlug, 'slug'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'shape' => ['required', Rule::in(['aviator', 'geometric', 'shield'])],
            'series' => ['required', Rule::in(['axiom', 'vector', 'apex'])],
            'lens' => ['required', Rule::in(['polarized', 'gradient', 'mirror'])],
            'image' => ['required', 'string', 'max:500'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'reviews_count' => ['nullable', 'integer', 'min:0'],
            'is_new' => ['nullable', 'boolean'],
            'released_at' => ['required', 'date'],
            'color_ids' => ['required', 'array', 'min:1'],
            'color_ids.*' => ['string', 'exists:colors,id'],
        ])->validate();
    }

    public static function create(array $validated): Product
    {
        return DB::transaction(function () use ($validated) {
            $product = Product::query()->create([
                'slug' => $validated['slug'],
                'name' => $validated['name'],
                'price' => $validated['price'],
                'shape' => $validated['shape'],
                'series' => $validated['series'],
                'lens' => $validated['lens'],
                'image' => $validated['image'],
                'rating' => $validated['rating'] ?? 0,
                'reviews_count' => $validated['reviews_count'] ?? 0,
                'is_new' => $validated['is_new'] ?? false,
                'released_at' => $validated['released_at'],
            ]);

            self::syncColorIds($product, $validated['color_ids']);

            return $product;
        });
    }

    public static function update(Product $product, array $validated): Product
    {
        return DB::transaction(function () use ($product, $validated) {
            $oldSlug = $product->slug;
            $newSlug = $validated['slug'];

            $product->update([
                'slug' => $newSlug,
                'name' => $validated['name'],
                'price' => $validated['price'],
                'shape' => $validated['shape'],
                'series' => $validated['series'],
                'lens' => $validated['lens'],
                'image' => $validated['image'],
                'rating' => $validated['rating'] ?? 0,
                'reviews_count' => $validated['reviews_count'] ?? 0,
                'is_new' => $validated['is_new'] ?? false,
                'released_at' => $validated['released_at'],
            ]);

            if ($oldSlug !== $newSlug) {
                DB::table('cart_items')->where('product_slug', $oldSlug)->update(['product_slug' => $newSlug]);
            }

            self::syncColorIds($product, $validated['color_ids']);

            return $product;
        });
    }

    /** @param list<string> $colorIds */
    public static function syncColorIds(Product $product, array $colorIds): void
    {
        DB::table('product_color')->where('product_slug', $product->slug)->delete();

        foreach ($colorIds as $index => $colorId) {
            $swatch = self::SWATCH[$product->shape][$colorId] ?? null;

            if (! $swatch) {
                throw new \InvalidArgumentException("No swatch for {$product->shape}/{$colorId}");
            }

            DB::table('product_color')->insert([
                'product_slug' => $product->slug,
                'color_id' => $colorId,
                'swatch_image' => $swatch,
                'sort_order' => $index,
            ]);
        }
    }
}
