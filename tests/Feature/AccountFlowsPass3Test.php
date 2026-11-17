<?php

namespace Tests\Feature;

use App\Livewire\RegisterModal;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Functional pass 3 (accounts and auth, 17. 11. 2026): defects found by
 * clicking through registration, verification and password reset.
 */
class AccountFlowsPass3Test extends TestCase
{
    use RefreshDatabase;

    /**
     * A taken e-mail only turned the field red: the message was an Alpine
     * x-show evaluated once at render, so it never appeared after the server
     * answered, and it would have said "wrong format" anyway.
     */
    public function test_register_modal_says_the_email_is_already_registered(): void
    {
        App::setLocale('cs');
        User::factory()->create(['email' => 'taken@example.com']);

        Livewire::test(RegisterModal::class)
            ->set('gender', 'female')
            ->set('name', 'Nova')
            ->set('email', 'taken@example.com')
            ->set('password', 'short')
            ->set('password_confirmation', 'other')
            ->call('register')
            ->assertHasErrors(['email', 'password', 'password_confirmation'])
            ->assertSee('E-mail je už registrovaný')
            ->assertSee('Alespoň 8 znaků')
            ->assertSee('Hesla se neshodují')
            ->assertDontSee('Pole heslo musí mít alespoň 8 znaků');
    }

    /** The modal's back button had the raw key auth.register.back as its label. */
    public function test_register_modal_back_button_is_translated(): void
    {
        App::setLocale('cs');

        Livewire::test(RegisterModal::class)
            ->set('gender', 'female')
            ->assertDontSee('auth.register.back')
            ->assertSee('aria-label="Zpět"', false);
    }

    /** The verification e-mail was entirely in English for a Czech visitor. */
    public function test_verification_email_is_czech(): void
    {
        App::setLocale('cs');
        $user = User::factory()->unverified()->create();

        $mail = (new VerifyEmail)->toMail($user);
        $html = (string) $mail->render();

        $this->assertSame('Ověřit e-mailovou adresu', $mail->subject);
        $this->assertStringContainsString('Klikněte prosím na tlačítko', $html);
        $this->assertStringNotContainsString('Please click the button', $html);
        $this->assertStringNotContainsString('trouble clicking', $html);
    }

    /** The password reset e-mail was entirely in English for a Czech visitor. */
    public function test_password_reset_email_is_czech(): void
    {
        App::setLocale('cs');
        $user = User::factory()->create();

        $mail = (new ResetPassword('token'))->toMail($user);
        $html = (string) $mail->render();

        $this->assertSame('Obnovení hesla', $mail->subject);
        $this->assertStringContainsString('žádost o obnovení hesla', $html);
        $this->assertStringNotContainsString('You are receiving this email', $html);
    }

    /**
     * services-manager started with a <style> block: Livewire took the style as
     * the component root, so no toggle (services, languages) and no save of the
     * opening hours reached the server. A Livewire view must open with its one
     * root element.
     */
    public function test_livewire_views_do_not_start_with_a_style_or_script_block(): void
    {
        $offenders = [];

        foreach (glob(resource_path('views/livewire/*.blade.php')) as $file) {
            $source = file_get_contents($file);
            $source = preg_replace('/\{\{--.*?--\}\}/s', '', $source);
            $source = preg_replace('/@php\b.*?@endphp/s', '', $source);
            $source = preg_replace('/@(props|php)\s*\(.*?\)\s*$/m', '', $source);

            if (preg_match('/^\s*<(style|script|link)\b/i', $source)) {
                $offenders[] = basename($file);
            }
        }

        $this->assertSame([], $offenders);
    }

    /** Toggling a service and a language in the account is stored. */
    public function test_services_and_languages_toggles_are_saved(): void
    {
        $user = User::factory()->create(['gender' => 'female']);
        $profile = \App\Models\Profile::factory()->create(['user_id' => $user->id]);
        $service = \App\Models\Service::create(['name' => 'Masáž', 'slug' => 'masaz-p3', 'is_active' => true, 'sort_order' => 1]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\ServicesManager::class)
            ->call('toggleService', $service->id)
            ->call('toggleLanguage', 'Čeština');

        $profile->refresh();
        $this->assertTrue($profile->services->contains($service));
        $this->assertStringContainsString('Čeština', (string) $profile->languages);
    }

    /** The country select in the provider form listed English names on the Czech site. */
    public function test_profile_form_country_names_follow_the_locale(): void
    {
        App::setLocale('cs');
        $user = User::factory()->create(['gender' => 'female']);

        $countries = Livewire::actingAs($user)
            ->test(\App\Livewire\ProfileForm::class)
            ->get('countries');

        $names = array_column($countries, 'name');
        $this->assertContains('Česko', $names);
        $this->assertNotContains('Czechia', $names);
    }

    /**
     * Approving in the (English) admin sent the Czech provider an English
     * notification: the text was rendered in the approver's language.
     */
    public function test_approval_notification_is_shown_in_the_readers_language(): void
    {
        $user = User::factory()->create(['gender' => 'female']);
        $profile = \App\Models\Profile::factory()->create(['user_id' => $user->id, 'status' => 'pending', 'is_public' => false]);

        App::setLocale('en');
        $profile->update(['status' => 'approved']);

        $notification = \App\Models\Notification::where('user_id', $user->id)->latest('id')->firstOrFail();

        App::setLocale('cs');
        $this->assertSame('Profil schválen', $notification->title);
        $this->assertStringContainsString('Gratulujeme', $notification->message);

        App::setLocale('en');
        $this->assertSame('Profile Approved', $notification->title);
        $this->assertTrue($profile->fresh()->is_public);
    }

    /** Parameters survive the round trip (message notification names the sender). */
    public function test_translated_notification_keeps_its_parameters(): void
    {
        $user = User::factory()->create();
        $n = \App\Models\Notification::createTranslatedForUser($user->id, 'notifications.message.received_title', 'notifications.message.received_message', ['name' => 'Petr']);

        App::setLocale('cs');
        $this->assertStringContainsString('Petr', $n->fresh()->message);
        $this->assertStringNotContainsString('notifications.', $n->fresh()->message);
    }

    /** The default 404 page was English on the Czech site. */
    public function test_not_found_page_is_czech(): void
    {
        $this->get('/profiles/999999?locale=cs')->assertNotFound()->assertSee('Stránka nenalezena');
    }

    /**
     * The reset link carries ?email=, but the form ignored it: the e-mail field
     * was empty and the required attribute silently blocked the submit.
     */
    public function test_reset_password_form_is_prefilled_with_the_email_from_the_link(): void
    {
        $this->get('/reset-password/some-token?email=jana%40example.com')
            ->assertOk()
            ->assertSee('value="jana@example.com"', false);
    }

    /** The standalone login page (target of the verification and reset redirects) had no way to reset the password. */
    public function test_login_page_links_to_password_reset(): void
    {
        $this->get('/login?locale=cs')
            ->assertOk()
            ->assertSee(route('password.request'), false)
            ->assertSee('Zapomenuté heslo');
    }
}

