<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\CatalogSeeder::class);
    }

    public function test_products_index_returns_twelve_items(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertOk()
            ->assertJsonCount(12, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'price', 'shape', 'series', 'lens', 'colors', 'createdAt'],
                ],
            ]);
    }

    public function test_product_show_returns_matching_frontend_shape(): void
    {
        $response = $this->getJson('/api/products/axiom-midnight');

        $response->assertOk()
            ->assertJsonPath('data.id', 'axiom-midnight')
            ->assertJsonPath('data.isNew', true)
            ->assertJsonPath('data.reviews', 42);
    }
}
