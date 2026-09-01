<?php

namespace Database\Seeders;

use App\Models\Color;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    /** @var array<string, array{label: string, hex: string}> */
    private const COLORS = [
        'black' => ['label' => 'Black', 'hex' => '#1A1A1A'],
        'steel' => ['label' => 'Steel', 'hex' => '#8C8C8C'],
        'powder' => ['label' => 'Powder', 'hex' => '#A9C4D6'],
        'graphite' => ['label' => 'Graphite', 'hex' => '#4A4A4A'],
        'silver' => ['label' => 'Silver', 'hex' => '#C8C8C8'],
        'bronze' => ['label' => 'Bronze', 'hex' => '#6B5A4A'],
    ];

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

    /** @var list<array<string, mixed>> */
    private const PRODUCTS = [
        [
            'slug' => 'axiom-midnight',
            'name' => 'Axiom Midnight',
            'price' => 890,
            'shape' => 'aviator',
            'series' => 'axiom',
            'lens' => 'polarized',
            'image' => '/images/products/swatch-aviator-black.webp',
            'colors' => ['black', 'steel', 'powder'],
            'rating' => 4.9,
            'reviews_count' => 42,
            'is_new' => true,
            'released_at' => '2026-06-01',
        ],
        [
            'slug' => 'vector-angular',
            'name' => 'Vector Angular',
            'price' => 920,
            'shape' => 'geometric',
            'series' => 'vector',
            'lens' => 'polarized',
            'image' => '/images/products/swatch-geometric-black.webp',
            'colors' => ['black', 'graphite', 'silver'],
            'rating' => 4.8,
            'reviews_count' => 36,
            'is_new' => true,
            'released_at' => '2026-05-20',
        ],
        [
            'slug' => 'apex-shield',
            'name' => 'Apex Shield',
            'price' => 980,
            'shape' => 'shield',
            'series' => 'apex',
            'lens' => 'mirror',
            'image' => '/images/products/swatch-shield-black.webp',
            'colors' => ['black', 'steel'],
            'rating' => 4.7,
            'reviews_count' => 28,
            'is_new' => false,
            'released_at' => '2026-04-12',
        ],
        [
            'slug' => 'axiom-pilot',
            'name' => 'Axiom Pilot',
            'price' => 845,
            'shape' => 'aviator',
            'series' => 'axiom',
            'lens' => 'gradient',
            'image' => '/images/products/swatch-aviator-black.webp',
            'colors' => ['black', 'bronze', 'powder'],
            'rating' => 4.8,
            'reviews_count' => 51,
            'is_new' => false,
            'released_at' => '2026-03-08',
        ],
        [
            'slug' => 'vector-clarity',
            'name' => 'Vector Clarity',
            'price' => 875,
            'shape' => 'geometric',
            'series' => 'vector',
            'lens' => 'gradient',
            'image' => '/images/products/swatch-geometric-black.webp',
            'colors' => ['black', 'silver'],
            'rating' => 4.6,
            'reviews_count' => 19,
            'is_new' => false,
            'released_at' => '2026-02-18',
        ],
        [
            'slug' => 'apex-wrap',
            'name' => 'Apex Wrap',
            'price' => 1010,
            'shape' => 'shield',
            'series' => 'apex',
            'lens' => 'polarized',
            'image' => '/images/products/swatch-shield-black.webp',
            'colors' => ['black', 'powder', 'steel'],
            'rating' => 4.9,
            'reviews_count' => 33,
            'is_new' => true,
            'released_at' => '2026-06-15',
        ],
        [
            'slug' => 'axiom-horizon',
            'name' => 'Axiom Horizon',
            'price' => 860,
            'shape' => 'aviator',
            'series' => 'axiom',
            'lens' => 'mirror',
            'image' => '/images/products/swatch-aviator-steel.webp',
            'colors' => ['steel', 'black'],
            'rating' => 4.5,
            'reviews_count' => 22,
            'is_new' => false,
            'released_at' => '2026-01-22',
        ],
        [
            'slug' => 'vector-edge',
            'name' => 'Vector Edge',
            'price' => 940,
            'shape' => 'geometric',
            'series' => 'vector',
            'lens' => 'mirror',
            'image' => '/images/products/swatch-geometric-black.webp',
            'colors' => ['black', 'graphite', 'powder'],
            'rating' => 4.7,
            'reviews_count' => 40,
            'is_new' => false,
            'released_at' => '2026-05-02',
        ],
        [
            'slug' => 'apex-line',
            'name' => 'Apex Line',
            'price' => 995,
            'shape' => 'shield',
            'series' => 'apex',
            'lens' => 'gradient',
            'image' => '/images/products/swatch-shield-black.webp',
            'colors' => ['black', 'silver'],
            'rating' => 4.6,
            'reviews_count' => 15,
            'is_new' => false,
            'released_at' => '2026-03-28',
        ],
        [
            'slug' => 'axiom-steel',
            'name' => 'Axiom Steel',
            'price' => 910,
            'shape' => 'aviator',
            'series' => 'axiom',
            'lens' => 'polarized',
            'image' => '/images/products/swatch-aviator-steel.webp',
            'colors' => ['steel', 'powder'],
            'rating' => 4.8,
            'reviews_count' => 47,
            'is_new' => false,
            'released_at' => '2026-04-30',
        ],
        [
            'slug' => 'vector-frame',
            'name' => 'Vector Frame',
            'price' => 885,
            'shape' => 'geometric',
            'series' => 'vector',
            'lens' => 'polarized',
            'image' => '/images/products/swatch-geometric-black.webp',
            'colors' => ['black', 'silver', 'steel'],
            'rating' => 4.9,
            'reviews_count' => 38,
            'is_new' => false,
            'released_at' => '2026-06-08',
        ],
        [
            'slug' => 'apex-void',
            'name' => 'Apex Void',
            'price' => 1050,
            'shape' => 'shield',
            'series' => 'apex',
            'lens' => 'mirror',
            'image' => '/images/products/swatch-shield-black.webp',
            'colors' => ['black', 'graphite'],
            'rating' => 5.0,
            'reviews_count' => 12,
            'is_new' => true,
            'released_at' => '2026-07-01',
        ],
    ];

    public function run(): void
    {
        foreach (self::COLORS as $id => $meta) {
            Color::query()->updateOrCreate(
                ['id' => $id],
                ['label' => $meta['label'], 'hex' => $meta['hex']],
            );
        }

        foreach (self::PRODUCTS as $data) {
            $colorIds = $data['colors'];
            unset($data['colors']);

            Product::query()->updateOrCreate(
                ['slug' => $data['slug']],
                $data,
            );

            DB::table('product_color')->where('product_slug', $data['slug'])->delete();

            foreach ($colorIds as $index => $colorId) {
                $shape = $data['shape'];
                $swatch = self::SWATCH[$shape][$colorId] ?? null;

                if (! $swatch) {
                    throw new \RuntimeException("Missing swatch for {$shape}/{$colorId}");
                }

                DB::table('product_color')->insert([
                    'product_slug' => $data['slug'],
                    'color_id' => $colorId,
                    'swatch_image' => $swatch,
                    'sort_order' => $index,
                ]);
            }
        }
    }
}
