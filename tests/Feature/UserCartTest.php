<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserCartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogSeeder::class);
    }

    public function test_guest_cannot_add_to_cart(): void
    {
        $this->postJson('/api/cart/items', [
            'productId' => 'axiom-midnight',
            'colorId' => 'black',
            'quantity' => 1,
        ])->assertUnauthorized();
    }

    public function test_authenticated_user_gets_own_cart_on_add(): void
    {
        $user = User::factory()->customer()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/cart/items', [
            'productId' => 'axiom-midnight',
            'colorId' => 'black',
            'quantity' => 2,
        ]);

        $response->assertCreated()
            ->assertJsonPath('item.productId', 'axiom-midnight')
            ->assertJsonPath('itemCount', 2);

        $cartId = $response->json('cartId');

        $this->assertDatabaseHas('carts', [
            'id' => $cartId,
            'user_id' => $user->id,
        ]);

        $this->getJson('/api/cart')
            ->assertOk()
            ->assertJsonPath('id', $cartId)
            ->assertJsonPath('itemCount', 2)
            ->assertJsonCount(1, 'items');
    }

    public function test_guest_cannot_view_cart(): void
    {
        $this->getJson('/api/cart')->assertUnauthorized();
    }

    public function test_authenticated_user_can_remove_cart_item(): void
    {
        $user = User::factory()->customer()->create();
        Sanctum::actingAs($user);

        $add = $this->postJson('/api/cart/items', [
            'productId' => 'axiom-midnight',
            'colorId' => 'black',
            'quantity' => 2,
        ])->assertCreated();

        $itemId = $add->json('item.id');

        $this->deleteJson('/api/cart/items/'.$itemId)
            ->assertOk()
            ->assertJsonPath('itemCount', 0)
            ->assertJsonCount(0, 'items');

        $this->getJson('/api/cart')
            ->assertOk()
            ->assertJsonPath('itemCount', 0);
    }

    public function test_user_cannot_remove_another_users_cart_item(): void
    {
        $owner = User::factory()->customer()->create();
        Sanctum::actingAs($owner);

        $itemId = $this->postJson('/api/cart/items', [
            'productId' => 'axiom-midnight',
            'colorId' => 'black',
            'quantity' => 1,
        ])->json('item.id');

        $other = User::factory()->customer()->create();
        Sanctum::actingAs($other);

        $this->deleteJson('/api/cart/items/'.$itemId)
            ->assertNotFound();
    }

    public function test_authenticated_checkout_uses_user_cart_without_cart_id(): void
    {
        Http::fake([
            'api.sandbox.checkout.com/hosted-payments' => Http::response([
                'id' => 'hpp_stub',
                '_links' => [
                    'redirect' => ['href' => 'https://pay.sandbox.checkout.com/page/hpp_stub'],
                ],
            ], 201),
        ]);

        config([
            'checkout.secret_key' => 'sk_sbox_test',
            'checkout.api_url' => 'https://api.sandbox.checkout.com',
        ]);

        $user = User::factory()->customer()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/cart/items', [
            'productId' => 'axiom-midnight',
            'colorId' => 'black',
            'quantity' => 1,
        ])->assertCreated();

        $this->postJson('/api/checkout/pay', [])
            ->assertCreated()
            ->assertJsonPath('order.itemCount', 1)
            ->assertJsonStructure(['checkoutUrl', 'order']);
    }
}
