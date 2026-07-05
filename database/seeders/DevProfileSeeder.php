<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevProfileSeeder extends Seeder
{
    public function run(): void
    {
        // Create 20 users and their profiles
        for ($i = 0; $i < 20; $i++) {
            $user = User::firstOrCreate([
                'email' => 'demo' . ($i + 1) . '@example.com',
            ], [
                'name' => 'Demo User ' . ($i + 1),
                'password' => Hash::make('password'),
                'gender' => 'female',
                'email_verified_at' => now(),
            ]);
            $user->syncRoles(['user']);

            if (!$user->profile) {
                Profile::factory()->complete()->create([
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
