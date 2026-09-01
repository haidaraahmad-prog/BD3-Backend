<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FavouriteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogSeeder::class);
    }

    public function test_favourites_require_authentication(): void
    {
        $this->getJson('/api/favourites')->assertUnauthorized();
        $this->postJson('/api/favourites', ['productId' => 'axiom-midnight'])->assertUnauthorized();
    }

    public function test_user_can_add_list_and_remove_favourites(): void
    {
        $user = User::factory()->customer()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/favourites', [
            'productId' => 'axiom-midnight',
        ])->assertCreated();

        $this->postJson('/api/favourites', [
            'productId' => 'axiom-midnight',
        ])->assertCreated();

        $this->getJson('/api/favourites')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 'axiom-midnight');

        $this->deleteJson('/api/favourites/axiom-midnight')
            ->assertOk();

        $this->getJson('/api/favourites')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
