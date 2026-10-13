<?php

namespace Tests\Feature;

use App\Livewire\ProfileForm;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AccountEmailSettingsTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Local-QA-Only-2026!';

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->seed(\Database\Seeders\CurrencySeeder::class);
    }

    public static function accounts(): array
    {
        return [['member'], ['provider']];
    }

    private function account(string $kind): User
    {
        $user = User::factory()->create([
            'gender' => $kind === 'member' ? 'male' : 'female',
            'email' => $kind.'-original@example.test',
            'phone' => '+420777111222',
            'password' => self::PASSWORD,
            'email_verified_at' => now(),
        ]);
        if ($kind === 'provider') {
            Profile::factory()->for($user)->create([
                'age' => 25, 'country_code' => 'CZ', 'city' => null,
                'content' => [], 'local_prices' => [], 'global_prices' => [],
                'availability_hours' => null, 'is_public' => false,
            ]);
        }

        return $user;
    }

    #[DataProvider('accounts')]
    public function test_main_settings_expose_a_real_email_change_control(string $kind): void
    {
        $user = $this->account($kind);
        $html = $this->actingAs($user)->get($kind === 'member' ? '/account/member' : '/account')->assertOk()->getContent();
        $dom = new \DOMDocument;
        @$dom->loadHTML($html);
        $input = $dom->getElementById('new_email');
        $this->assertNotNull($input, 'The visible Save button has no editable email-change field.');
        $this->assertFalse($input->hasAttribute('disabled'));
        $this->assertFalse($input->hasAttribute('readonly'));
        $this->assertNotNull($dom->getElementById('email_change_password'));
    }

    public static function invalidChanges(): array
    {
        $rows = [];
        foreach (['member', 'provider'] as $kind) {
            foreach (['format', 'duplicate', 'wrong-password', 'missing-password'] as $case) {
                $rows[] = [$kind, $case];
            }
        }

        return $rows;
    }

    #[DataProvider('invalidChanges')]
    public function test_invalid_change_preserves_the_existing_account(string $kind, string $case): void
    {
        $user = $this->account($kind);
        $before = $user->only(['name', 'email', 'phone', 'password', 'email_verified_at']);
        $target = 'changed@example.test';
        $password = self::PASSWORD;
        if ($case === 'format') {
            $target = 'invalid-address';
        }
        if ($case === 'duplicate') {
            User::factory()->create(['email' => $target]);
        }
        if ($case === 'wrong-password') {
            $password = 'wrong';
        }
        if ($case === 'missing-password') {
            $password = '';
        }
        $field = in_array($case, ['format', 'duplicate']) ? 'new_email' : 'email_change_password';

        if ($kind === 'member') {
            $this->actingAs($user)->patch(route('account.member.settings.update'), [
                'name' => 'Changed nickname', 'new_email' => $target, 'email_change_password' => $password,
            ])->assertSessionHasErrors($field);
        } else {
            Livewire::actingAs($user)->test(ProfileForm::class)
                ->set('new_email', $target)->set('email_change_password', $password)
                ->call('save')->assertHasErrors($field);
        }

        $this->assertEquals($before, $user->fresh()->only(array_keys($before)));
        Notification::assertNothingSent();
    }

    #[DataProvider('accounts')]
    public function test_changed_address_is_verified_and_becomes_the_login_address(string $kind): void
    {
        $user = $this->account($kind);
        $beforeHash = $user->password;
        $oldLink = URL::temporarySignedRoute('verification.verify', now()->addMinutes(30), [
            'id' => $user->id, 'hash' => sha1($user->email),
        ]);
        $newAddress = $kind.'-changed@example.test';

        if ($kind === 'member') {
            $this->actingAs($user)->patch(route('account.member.settings.update'), [
                'name' => $user->name, 'new_email' => $newAddress, 'email_change_password' => self::PASSWORD,
            ])->assertRedirect(route('verification.notice'));
        } else {
            Livewire::actingAs($user)->test(ProfileForm::class)
                ->set('new_email', $newAddress)->set('email_change_password', self::PASSWORD)
                ->call('save')->assertHasNoErrors()->assertRedirect(route('verification.notice'));
        }

        $fresh = $user->fresh();
        $this->assertSame($newAddress, $fresh->email);
        $this->assertNull($fresh->email_verified_at);
        $this->assertSame('+420777111222', $fresh->phone);
        $this->assertSame($beforeHash, $fresh->password);
        $this->actingAs($fresh)->get($kind === 'member' ? '/account/member' : '/account')->assertRedirect(route('verification.notice'));
        $this->get($oldLink)->assertForbidden();
        $link = null;
        Notification::assertSentTo($fresh, VerifyEmail::class, function ($notification, $channels, $notifiable) use (&$link, $newAddress) {
            $this->assertSame($newAddress, $notifiable->getEmailForVerification());
            $link = $notification->toMail($notifiable)->actionUrl;

            return true;
        });
        $this->get($link)->assertRedirect();
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->post('/logout');
        $this->post('/login', ['email' => $kind.'-original@example.test', 'password' => self::PASSWORD])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $newAddress, 'password' => self::PASSWORD])->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user->fresh());
    }

    #[DataProvider('accounts')]
    public function test_unchanged_email_does_not_require_password_or_reverification(string $kind): void
    {
        $user = $this->account($kind);
        if ($kind === 'member') {
            $this->actingAs($user)->patch(route('account.member.settings.update'), [
                'name' => $user->name, 'new_email' => $user->email,
            ])->assertSessionHasNoErrors();
        } else {
            Livewire::actingAs($user)->test(ProfileForm::class)->set('new_email', $user->email)
                ->call('save')->assertHasNoErrors();
        }
        $this->assertNotNull($user->fresh()->email_verified_at);
        Notification::assertNothingSent();
    }

    public function test_member_nickname_save_preserves_a_phone_not_present_in_the_form(): void
    {
        $user = $this->account('member');
        $this->actingAs($user)->patch(route('account.member.settings.update'), ['name' => 'Renamed member'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Renamed member', $user->fresh()->name);
        $this->assertSame('+420777111222', $user->fresh()->phone);
    }

    public function test_confirmation_password_is_not_stored_in_validation_old_input(): void
    {
        $user = $this->account('member');
        User::factory()->create(['email' => 'occupied@example.test']);
        $this->actingAs($user)->patch(route('account.member.settings.update'), [
            'name' => $user->name, 'new_email' => 'occupied@example.test',
            'email_change_password' => self::PASSWORD,
        ])->assertSessionHasErrors('new_email')
            ->assertSessionMissing('_old_input.email_change_password');
    }

    #[DataProvider('accounts')]
    public function test_legacy_account_form_cannot_bypass_email_password_confirmation(string $kind): void
    {
        $user = $this->account($kind);
        $this->actingAs($user)->patch(route('account.update'), [
            'name' => $user->name, 'email' => 'legacy-changed@example.test', 'phone' => $user->phone,
        ])->assertSessionHasErrors('email_change_password');
        $this->assertSame($kind.'-original@example.test', $user->fresh()->email);
        Notification::assertNothingSent();
    }

    #[DataProvider('accounts')]
    public function test_legacy_account_form_sends_verification_for_a_confirmed_change(string $kind): void
    {
        $user = $this->account($kind);
        $this->actingAs($user)->get(route('account.edit'))->assertOk()
            ->assertSee('id="email_change_password"', false);
        $this->patch(route('account.update'), [
            'name' => $user->name, 'email' => 'legacy-changed@example.test', 'phone' => $user->phone,
            'email_change_password' => self::PASSWORD,
        ])->assertSessionHasNoErrors()->assertRedirect(route('verification.notice'));
        $this->assertSame('legacy-changed@example.test', $user->fresh()->email);
        $this->assertNull($user->fresh()->email_verified_at);
        Notification::assertSentTo($user->fresh(), VerifyEmail::class);
    }
}
