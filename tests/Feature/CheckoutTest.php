<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogSeeder::class);
        config([
            'checkout.secret_key' => 'sk_sbox_test',
            'checkout.webhook_secret' => 'whsec_test',
            'checkout.sandbox' => true,
            'checkout.api_url' => 'https://api.sandbox.checkout.com',
        ]);
    }

    public function test_guest_cannot_checkout(): void
    {
        $this->postJson('/api/checkout/pay', [])->assertUnauthorized();
    }

    public function test_checkout_creates_order_and_returns_payment_url(): void
    {
        Http::fake([
            'api.sandbox.checkout.com/hosted-payments' => Http::response([
                'id' => 'hpp_test123',
                '_links' => [
                    'redirect' => ['href' => 'https://pay.sandbox.checkout.com/page/hpp_test123'],
                ],
            ], 201),
        ]);

        $user = User::factory()->customer()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/cart/items', [
            'productId' => 'axiom-midnight',
            'colorId' => 'black',
            'quantity' => 1,
        ])->assertCreated();

        $response = $this->postJson('/api/checkout/pay', [])
            ->assertCreated()
            ->assertJsonPath('checkoutUrl', 'https://pay.sandbox.checkout.com/page/hpp_test123')
            ->assertJsonPath('order.status', 'pending')
            ->assertJsonPath('order.currency', 'AED');

        $reference = $response->json('order.reference');
        $this->assertDatabaseHas('orders', [
            'reference' => $reference,
            'status' => 'pending',
            'user_id' => $user->id,
        ]);
    }

    public function test_webhook_marks_order_paid_and_clears_cart(): void
    {
        Http::fake([
            'api.sandbox.checkout.com/hosted-payments' => Http::response([
                'id' => 'hpp_test123',
                '_links' => [
                    'redirect' => ['href' => 'https://pay.sandbox.checkout.com/page/hpp_test123'],
                ],
            ], 201),
        ]);

        $user = User::factory()->customer()->create();
        Sanctum::actingAs($user);

        $cartResponse = $this->postJson('/api/cart/items', [
            'productId' => 'axiom-midnight',
            'colorId' => 'black',
            'quantity' => 1,
        ])->assertCreated();

        $cartId = $cartResponse->json('cartId');

        $checkout = $this->postJson('/api/checkout/pay', [])->assertCreated();
        $reference = $checkout->json('order.reference');

        $payload = json_encode([
            'type' => 'payment_approved',
            'data' => ['id' => 'pay_test', 'reference' => $reference],
        ]);

        $signature = hash_hmac('sha256', $payload, 'whsec_test');

        $this->call(
            'POST',
            '/api/webhooks/checkout',
            [],
            [],
            [],
            ['HTTP_Cko-Signature' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $payload,
        )->assertOk();

        $this->assertDatabaseHas('orders', ['reference' => $reference, 'status' => 'paid']);
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cartId]);
    }

    public function test_checkout_returns_503_when_gateway_not_configured(): void
    {
        config(['checkout.secret_key' => null]);

        $user = User::factory()->customer()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/cart/items', [
            'productId' => 'axiom-midnight',
            'colorId' => 'black',
            'quantity' => 1,
        ])->assertCreated();

        $this->postJson('/api/checkout/pay', [])->assertStatus(503);
    }

    public function test_authenticated_checkout_uses_user_cart(): void
    {
        Http::fake([
            'api.sandbox.checkout.com/hosted-payments' => Http::response([
                'id' => 'hpp_auth',
                '_links' => [
                    'redirect' => ['href' => 'https://pay.sandbox.checkout.com/page/hpp_auth'],
                ],
            ], 201),
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
            ->assertJsonStructure(['checkoutUrl', 'order']);
    }
}
