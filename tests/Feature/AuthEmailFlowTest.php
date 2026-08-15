<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Registration and password reset both hang on an email actually going out.
 * Neither path had a test, so a broken mailer or a missing Registered event
 * would only show up as a user who never receives anything.
 */
class AuthEmailFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * RegisterUser assigns the 'user' role inside a transaction, so on a system
     * where the roles were never seeded the whole registration rolls back and
     * the visitor gets an account that silently does not exist.
     */
    public function test_registration_needs_the_roles_to_exist(): void
    {
        $this->post('/register', [
            'name' => 'Bez Role',
            'email' => 'bezrole@example.com',
            'password' => 'tajneheslo123',
            'password_confirmation' => 'tajneheslo123',
            'gender' => 'female',
        ]);

        $this->assertDatabaseMissing('users', ['email' => 'bezrole@example.com']);
    }

    public function test_registration_fires_the_verification_event(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        Event::fake([Registered::class]);

        $response = $this->post('/register', [
            'name' => 'Nová Uživatelka',
            'email' => 'nova@example.com',
            'password' => 'tajneheslo123',
            'password_confirmation' => 'tajneheslo123',
            'gender' => 'female',
            'terms' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'nova@example.com']);
        Event::assertDispatched(Registered::class);
    }

    public function test_a_registered_user_is_sent_a_verification_email(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        Notification::fake();

        $this->post('/register', [
            'name' => 'Nová Uživatelka',
            'email' => 'nova2@example.com',
            'password' => 'tajneheslo123',
            'password_confirmation' => 'tajneheslo123',
            'gender' => 'female',
            'terms' => '1',
        ]);

        $user = User::where('email', 'nova2@example.com')->firstOrFail();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_forgotten_password_sends_a_reset_link(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'zapomnel@example.com']);

        $response = $this->post('/forgot-password', ['email' => 'zapomnel@example.com']);

        $response->assertSessionHasNoErrors();
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_an_unknown_address_does_not_error(): void
    {
        Notification::fake();

        $this->post('/forgot-password', ['email' => 'neexistuje@example.com'])
            ->assertRedirect();

        Notification::assertNothingSent();
    }

    public function test_the_reset_link_actually_changes_the_password(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'reset@example.com']);

        $this->post('/forgot-password', ['email' => 'reset@example.com']);

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $this->post('/reset-password', [
            'token' => $token,
            'email' => 'reset@example.com',
            'password' => 'noveheslo123',
            'password_confirmation' => 'noveheslo123',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(
            \Illuminate\Support\Facades\Hash::check('noveheslo123', $user->fresh()->password)
        );
    }
}
