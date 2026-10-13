<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditFeedbackAndLabelsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RoleSeeder::class, CurrencySeeder::class]);
    }

    public function test_provider_checkout_error_is_visible_after_redirect(): void
    {
        $user = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);
        Profile::factory()->for($user)->create();
        $this->actingAs($user)->withSession(['error' => 'Audit unavailable checkout explanation'])
            ->get('/account/subscription')->assertOk()->assertSee('Audit unavailable checkout explanation');
    }

    public function test_member_checkout_error_is_visible_after_redirect(): void
    {
        $user = User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);
        $this->actingAs($user)->withSession(['error' => 'Audit unavailable checkout explanation'])
            ->get('/account/member/membership')->assertOk()->assertSee('Audit unavailable checkout explanation');
    }

    public function test_member_subscription_fields_have_readable_labels(): void
    {
        $admin = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);
        $admin->assignRole(['super_admin', 'admin']);
        $this->actingAs($admin)->get('/admin/member-subscriptions/create')->assertOk()
            ->assertDontSee('subscriptions.fields.');
    }

    public function test_invalid_deletion_password_error_is_rendered_on_return(): void
    {
        $user = User::factory()->create(['gender' => 'female', 'email_verified_at' => now(), 'password' => bcrypt('Valid-Audit-Password-2026')]);
        $this->actingAs($user)->from('/account/profile')->delete('/account/profile', ['password' => 'Incorrect-Audit-Password'])
            ->assertRedirect('/account/profile')->assertSessionHasErrors('password');
        $message = session('errors')->first('password');
        $this->get('/account/profile')->assertOk()->assertSee($message);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
