<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@bd3.ae'],
            [
                'name' => 'BD3 Admin',
                'password' => Hash::make('bd3-admin-2026'),
                'is_admin' => true,
            ],
        );
    }
}
