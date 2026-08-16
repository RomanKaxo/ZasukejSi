<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Profile;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A contact message is only useful if the admin sees everything that came with
 * it — including who was signed in when it was sent.
 */
class ContactMessageAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);
        $this->seed(CurrencySeeder::class);

        $user = User::factory()->create([
            'gender' => 'male',
            'email_verified_at' => now(),
        ]);
        $user->syncRoles(['super_admin', 'admin']);

        return $user->fresh();
    }

    private function message(array $overrides = []): ContactMessage
    {
        return ContactMessage::create(array_merge([
            'first_name' => 'Jan',
            'last_name' => 'Novák',
            'phone' => '+420 777 123 456',
            'email' => 'jan@example.com',
            'message' => 'Dobrý den, mám dotaz k inzerci.',
            'locale' => 'cs',
            'ip_address' => '127.0.0.1',
        ], $overrides));
    }

    public function test_the_listing_shows_a_message(): void
    {
        $this->actingAs($this->admin());
        $this->message();

        $response = $this->get('/admin/contact-messages');

        $response->assertSuccessful();
        $response->assertSee('jan@example.com');
    }

    public function test_the_detail_shows_the_sender_and_their_profile(): void
    {
        $admin = $this->admin();

        $sender = User::factory()->create([
            'name' => 'Anna Svobodová',
            'email' => 'anna@example.com',
            'gender' => 'female',
        ]);
        $profile = Profile::factory()->create([
            'user_id' => $sender->id,
            'display_name' => ['cs' => 'Anna', 'en' => 'Anna'],
        ]);

        $message = $this->message([
            'user_id' => $sender->id,
            'profile_id' => $profile->id,
        ]);

        $response = $this->actingAs($admin)->get("/admin/contact-messages/{$message->id}");

        $response->assertSuccessful();
        $response->assertSee('anna@example.com');
        $response->assertSee('Anna');
    }

    public function test_opening_the_detail_marks_the_message_read(): void
    {
        $admin = $this->admin();
        $message = $this->message();

        $this->assertNull($message->read_at);

        $this->actingAs($admin)->get("/admin/contact-messages/{$message->id}")->assertSuccessful();

        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_the_badge_counts_only_unread_messages(): void
    {
        $this->admin();

        $this->message();
        $this->message(['email' => 'druhy@example.com'])->markAsRead();

        $this->assertSame(
            '1',
            \App\Filament\Resources\ContactMessages\ContactMessageResource::getNavigationBadge()
        );
    }

    public function test_an_unknown_status_does_not_break_the_listing(): void
    {
        $this->actingAs($this->admin());
        $message = $this->message();

        // Status is a plain column, so the set can grow without a deploy.
        \Illuminate\Support\Facades\DB::table('contact_messages')
            ->where('id', $message->id)
            ->update(['status' => 'eskalovano']);

        $this->get('/admin/contact-messages')->assertSuccessful();
    }
}
