<?php

namespace Tests\Feature;

use App\Livewire\ContactForm;
use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\Profile;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The contact page told visitors to get in touch and gave them nothing to do
 * it with.
 */
class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
        RateLimiter::clear('contact-form|127.0.0.1');
    }

    private function contactPage(): Page
    {
        return Page::updateOrCreate(
            ['slug' => 'contact'],
            [
                'title' => ['cs' => 'Kontakt', 'en' => 'Contact'],
                'type' => 'page',
                'content' => ['cs' => [], 'en' => []],
                'is_published' => true,
            ]
        );
    }

    public function test_the_contact_page_renders_the_form(): void
    {
        $page = $this->contactPage();

        $response = $this->get(url('/' . $page->slug));

        $response->assertSuccessful();
        $response->assertSee(__('contact.form.title'));
        $response->assertSee(__('contact.form.submit'));
    }

    public function test_a_guest_can_send_a_message(): void
    {
        Livewire::test(ContactForm::class)
            ->set('firstName', 'Jan')
            ->set('lastName', 'Novák')
            ->set('phone', '+420 777 123 456')
            ->set('email', 'jan@example.com')
            ->set('message', 'Dobrý den, mám dotaz k inzerci.')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $message = ContactMessage::sole();

        $this->assertSame('Jan', $message->first_name);
        $this->assertSame('Novák', $message->last_name);
        $this->assertSame('+420 777 123 456', $message->phone);
        $this->assertSame('jan@example.com', $message->email);
        $this->assertNull($message->user_id);
        $this->assertNull($message->profile_id);
        $this->assertSame(ContactMessage::STATUS_NEW, $message->status);
        $this->assertNull($message->read_at);
    }

    public function test_a_signed_in_sender_is_linked_to_their_account_and_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Anna Svobodová',
            'email' => 'anna@example.com',
            'phone' => '+420 601 000 111',
            'gender' => 'female',
        ]);
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(ContactForm::class)
            // The form knows who is writing, so it fills itself in.
            ->assertSet('firstName', 'Anna')
            ->assertSet('lastName', 'Svobodová')
            ->assertSet('email', 'anna@example.com')
            ->assertSet('phone', '+420 601 000 111')
            ->set('message', 'Potřebuji upravit svůj profil.')
            ->call('submit')
            ->assertHasNoErrors();

        $message = ContactMessage::sole();

        $this->assertSame($user->id, $message->user_id);
        $this->assertSame($profile->id, $message->profile_id);
    }

    public function test_a_member_without_a_profile_is_still_linked_to_his_account(): void
    {
        $user = User::factory()->create(['name' => 'Petr', 'gender' => 'male']);

        Livewire::actingAs($user)
            ->test(ContactForm::class)
            ->set('lastName', 'Dvořák')
            ->set('message', 'Dotaz k členství, děkuji.')
            ->call('submit')
            ->assertHasNoErrors();

        $message = ContactMessage::sole();

        $this->assertSame($user->id, $message->user_id);
        $this->assertNull($message->profile_id);
    }

    public function test_the_required_fields_are_enforced(): void
    {
        Livewire::test(ContactForm::class)
            ->set('firstName', '')
            ->set('lastName', '')
            ->set('email', 'nikoliv-email')
            ->set('message', 'krátká')
            ->call('submit')
            ->assertHasErrors(['firstName', 'lastName', 'email', 'message']);

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_the_context_of_the_submission_is_recorded(): void
    {
        Livewire::test(ContactForm::class)
            ->set('firstName', 'Jan')
            ->set('lastName', 'Novák')
            ->set('email', 'jan@example.com')
            ->set('message', 'Dobrý den, mám dotaz.')
            ->call('submit');

        $message = ContactMessage::sole();

        // Answering a message needs to know what language it was written in.
        $this->assertSame(app()->getLocale(), $message->locale);
        $this->assertNotNull($message->ip_address);
    }

    public function test_sending_is_rate_limited(): void
    {
        $send = function (int $i) {
            return Livewire::test(ContactForm::class)
                ->set('firstName', 'Jan')
                ->set('lastName', 'Novák')
                ->set('email', 'jan@example.com')
                ->set('message', "Opakovaná zpráva číslo {$i}.")
                ->call('submit');
        };

        for ($i = 1; $i <= 5; $i++) {
            $send($i)->assertHasNoErrors();
        }

        $send(6)->assertHasErrors('message');

        $this->assertSame(5, ContactMessage::count());
    }

    public function test_opening_a_message_marks_it_read(): void
    {
        $message = ContactMessage::create([
            'first_name' => 'Jan',
            'last_name' => 'Novák',
            'email' => 'jan@example.com',
            'message' => 'Dobrý den.',
        ]);

        $this->assertFalse($message->isRead());

        $message->markAsRead();

        $this->assertTrue($message->fresh()->isRead());
    }
}
