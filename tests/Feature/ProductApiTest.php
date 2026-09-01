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

    public function test_products_index_returns_paginated_items(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertOk()
            ->assertJsonCount(9, 'data')
            ->assertJsonPath('meta.total', 12)
            ->assertJsonPath('meta.per_page', 9)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'price', 'shape', 'series', 'lens', 'colors', 'createdAt'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total', 'from', 'to'],
            ]);
    }

    public function test_products_index_page_two_returns_remaining_items(): void
    {
        $response = $this->getJson('/api/products?page=2');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.from', 10)
            ->assertJsonPath('meta.to', 12);
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
