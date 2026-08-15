<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SetUserPasswordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sets_every_password(): void
    {
        User::factory()->count(3)->create(['password' => Hash::make('something-else')]);

        $this->artisan('users:set-password')->assertSuccessful();

        foreach (User::all() as $user) {
            $this->assertTrue(Hash::check('password', $user->password));
        }
    }

    public function test_it_can_target_a_single_account(): void
    {
        $target = User::factory()->create(['password' => Hash::make('old')]);
        $other = User::factory()->create(['password' => Hash::make('old')]);

        $this->artisan('users:set-password', ['--email' => $target->email])->assertSuccessful();

        $this->assertTrue(Hash::check('password', $target->fresh()->password));
        $this->assertTrue(Hash::check('old', $other->fresh()->password));
    }

    public function test_it_accepts_a_custom_password(): void
    {
        $user = User::factory()->create();

        $this->artisan('users:set-password', ['password' => 'tajne-heslo'])->assertSuccessful();

        $this->assertTrue(Hash::check('tajne-heslo', $user->fresh()->password));
    }

    public function test_it_refuses_to_run_in_production_without_force(): void
    {
        app()['env'] = 'production';
        $user = User::factory()->create(['password' => Hash::make('old')]);

        $this->artisan('users:set-password')->assertFailed();

        $this->assertTrue(Hash::check('old', $user->fresh()->password));
    }
}
