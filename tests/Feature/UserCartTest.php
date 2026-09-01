<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertJsonPath('item.productId', 'axiom-midnight');

        $cartId = $response->json('cartId');

        $this->assertDatabaseHas('carts', [
            'id' => $cartId,
            'user_id' => $user->id,
        ]);

        $this->getJson('/api/cart')
            ->assertOk()
            ->assertJsonPath('id', $cartId)
            ->assertJsonCount(1, 'items');
    }

    public function test_guest_cart_is_separate_from_user_cart(): void
    {
        $guestResponse = $this->postJson('/api/cart/items', [
            'productId' => 'axiom-midnight',
            'colorId' => 'black',
            'quantity' => 1,
        ]);

        $guestCartId = $guestResponse->json('cartId');

        $user = User::factory()->customer()->create();
        Sanctum::actingAs($user);

        $userResponse = $this->postJson('/api/cart/items', [
            'productId' => 'axiom-midnight',
            'colorId' => 'steel',
            'quantity' => 1,
        ]);

        $userCartId = $userResponse->json('cartId');

        $this->assertNotSame($guestCartId, $userCartId);

        $this->getJson('/api/cart/'.$guestCartId)
            ->assertOk()
            ->assertJsonCount(1, 'items');

        $this->getJson('/api/cart')
            ->assertOk()
            ->assertJsonPath('id', $userCartId)
            ->assertJsonCount(1, 'items');
    }

    public function test_user_cannot_access_another_users_cart(): void
    {
        $owner = User::factory()->customer()->create();
        $cart = Cart::query()->create(['user_id' => $owner->id]);

        $other = User::factory()->customer()->create();
        Sanctum::actingAs($other);

        $this->getJson('/api/cart/'.$cart->id)
            ->assertForbidden();
    }

    public function test_authenticated_checkout_uses_user_cart_without_cart_id(): void
    {
        $user = User::factory()->customer()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/cart/items', [
            'productId' => 'axiom-midnight',
            'colorId' => 'black',
            'quantity' => 1,
        ])->assertCreated();

        $this->postJson('/api/checkout/pay', [])
            ->assertAccepted()
            ->assertJsonPath('order.itemCount', 1);
    }
}
