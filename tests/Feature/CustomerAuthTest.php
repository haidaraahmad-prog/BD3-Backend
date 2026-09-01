<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Jane Shopper',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email'], 'cartId']);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'is_admin' => false,
        ]);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->customer()->create(['email' => 'jane@example.com']);

        $this->postJson('/api/register', [
            'name' => 'Jane Shopper',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertUnprocessable();
    }

    public function test_customer_can_login_and_logout(): void
    {
        User::factory()->customer()->create([
            'email' => 'jane@example.com',
            'password' => Hash::make('password123'),
        ]);

        $login = $this->postJson('/api/login', [
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        $login->assertOk()
            ->assertJsonStructure(['token', 'user', 'cartId']);

        $token = $login->json('token');

        $this->getJson('/api/me', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonPath('email', 'jane@example.com')
            ->assertJsonStructure(['cartId']);

        $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer '.$token,
        ])->assertOk();
    }
}
