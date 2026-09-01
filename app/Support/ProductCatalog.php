<?php

namespace App\Support;

use App\Models\Product;

class ProductCatalog
{
    /** @var array<string, list<string>> */
    private const SHAPE_GALLERY = [
        'aviator' => ['/images/featured-aviator.webp', '/images/category-aviator.webp'],
        'geometric' => ['/images/featured-geometric.webp', '/images/category-geometric.webp'],
        'shield' => ['/images/category-shield.webp'],
    ];

    private const SHAPE_LABEL = [
        'aviator' => 'Aviator',
        'geometric' => 'Geometric',
        'shield' => 'Shield',
    ];

    private const SERIES_LABEL = [
        'axiom' => 'Axiom',
        'vector' => 'Vector',
        'apex' => 'Apex',
    ];

    private const LENS_LABEL = [
        'polarized' => 'Polarized',
        'gradient' => 'Gradient',
        'mirror' => 'Mirror',
    ];

    /** @return list<string> */
    public static function galleryFor(Product $product, string $colorId): array
    {
        $product->loadMissing('colors');

        $color = $product->colors->firstWhere('id', $colorId);
        $primary = $color?->pivot->swatch_image ?? $product->image;
        $extras = array_filter(
            self::SHAPE_GALLERY[$product->shape] ?? [],
            fn (string $src) => $src !== $primary,
        );

        $images = array_values(array_unique([$primary, ...$extras]));

        return $images;
    }

    /** @return array{tagline: string, description: string, specs: list<array{label: string, value: string}>} */
    public static function copyFor(Product $product): array
    {
        $shape = self::SHAPE_LABEL[$product->shape] ?? ucfirst($product->shape);
        $series = self::SERIES_LABEL[$product->series] ?? ucfirst($product->series);
        $lens = self::LENS_LABEL[$product->lens] ?? ucfirst($product->lens);

        return [
            'tagline' => "{$series} · {$shape}",
            'description' => sprintf(
                'Precision-milled titanium %s from the %s series. %s lenses cut glare without dulling contrast — engineered for heat, light, and long wear across the Gulf.',
                strtolower($shape),
                $series,
                $lens,
            ),
            'specs' => [
                ['label' => 'Series', 'value' => $series],
                ['label' => 'Shape', 'value' => $shape],
                ['label' => 'Lens', 'value' => $lens],
                ['label' => 'Frame', 'value' => 'Grade 5 titanium'],
                ['label' => 'Weight', 'value' => '28 g'],
                ['label' => 'UV', 'value' => '100% UVA / UVB'],
                ['label' => 'Fit', 'value' => 'Medium–wide'],
                ['label' => 'Origin', 'value' => 'Assembled UAE'],
            ],
        ];
    }

    /** @return array{shapes: list<array{value: string, label: string}>, series: list<array{value: string, label: string}>, lenses: list<array{value: string, label: string}>} */
    public static function filterOptions(): array
    {
        return [
            'shapes' => [
                ['value' => 'aviator', 'label' => 'Aviator'],
                ['value' => 'geometric', 'label' => 'Geometric'],
                ['value' => 'shield', 'label' => 'Shield'],
            ],
            'series' => [
                ['value' => 'axiom', 'label' => 'Axiom'],
                ['value' => 'vector', 'label' => 'Vector'],
                ['value' => 'apex', 'label' => 'Apex'],
            ],
            'lenses' => [
                ['value' => 'polarized', 'label' => 'Polarized'],
                ['value' => 'gradient', 'label' => 'Gradient'],
                ['value' => 'mirror', 'label' => 'Mirror'],
            ],
        ];
    }
}
