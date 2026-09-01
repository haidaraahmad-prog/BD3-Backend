<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_dashboard_to_login(): void
    {
        $this->get('/admin')
            ->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_sign_in_and_view_dashboard(): void
    {
        User::factory()->create([
            'email' => 'admin@bd3.ae',
            'password' => Hash::make('secret-pass'),
            'is_admin' => true,
        ]);

        $this->post('/admin/login', [
            'email' => 'admin@bd3.ae',
            'password' => 'secret-pass',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_admin_api_login_returns_token(): void
    {
        User::factory()->create([
            'email' => 'admin@bd3.ae',
            'password' => Hash::make('secret-pass'),
            'is_admin' => true,
        ]);

        $this->postJson('/api/admin/login', [
            'email' => 'admin@bd3.ae',
            'password' => 'secret-pass',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
    }
}
