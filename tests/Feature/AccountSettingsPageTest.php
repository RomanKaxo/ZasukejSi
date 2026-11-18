<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * /account/profile — „Základní nastavení“ v designu účtu.
 *
 * Dřív anglická stránka mimo design („Edit Profile“, „Profile Information“).
 * Zůstává, protože je jediným místem pro smazání účtu a jedinou stránkou
 * účtu pro uživatele bez pohlaví (odkazuje na ni předplatné i menu účtu).
 */
class AccountSettingsPageTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Settings-Page-Password-2026';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
        $this->seed(RoleSeeder::class);
    }

    private function user(?string $gender): User
    {
        return User::factory()->create([
            'gender' => $gender,
            'email_verified_at' => now(),
            'password' => bcrypt(self::PASSWORD),
            'phone' => null,
        ]);
    }

    public function test_woman_gets_the_account_layout_in_czech(): void
    {
        $this->actingAs($this->user('female'))->get('/account/profile?locale=cs')
            ->assertOk()
            ->assertSee('account-shell', false)
            ->assertSee('Základní nastavení')
            ->assertSee('Moje údaje')
            ->assertSee('Smazat účet')
            ->assertDontSee('Edit Profile')
            ->assertDontSee('Profile Information')
            ->assertDontSee('name="gender"', false);
    }

    public function test_man_gets_the_member_layout(): void
    {
        $this->actingAs($this->user('male'))->get('/account/profile?locale=cs')
            ->assertOk()
            ->assertSee('member-shell', false)
            ->assertDontSee('account-shell', false)
            ->assertSee('Základní nastavení');
    }

    public function test_english_texts(): void
    {
        $this->actingAs($this->user('female'))->get('/account/profile?locale=en')
            ->assertOk()
            ->assertSee('Basic Settings')
            ->assertSee('My Information')
            ->assertSee('Delete Account')
            ->assertSee('Include the country code');
    }

    public function test_saving_name_and_phone_shows_the_confirmation(): void
    {
        $user = $this->user('female');

        $this->actingAs($user)->from('/account/profile')->patch('/account/profile', [
            'name' => 'Nové jméno', 'email' => $user->email, 'phone' => '+420777123456',
        ])->assertRedirect('/account/profile')->assertSessionHasNoErrors();

        $this->assertSame('Nové jméno', $user->fresh()->name);
        $this->assertSame('+420777123456', $user->fresh()->phone);

        $this->get('/account/profile?locale=cs')->assertOk()
            ->assertSee('Vaše změny byly úspěšně uloženy.');
    }

    public function test_validation_errors_are_rendered_on_the_page(): void
    {
        $user = $this->user('female');
        $this->user('male')->forceFill(['phone' => '+420600000001'])->save();

        $this->actingAs($user)->from('/account/profile')->patch('/account/profile', [
            'name' => '', 'email' => $user->email, 'phone' => '+420600000001',
        ])->assertRedirect('/account/profile')->assertSessionHasErrors(['name', 'phone']);

        $errors = session('errors');
        $this->get('/account/profile')->assertOk()
            ->assertSee('aria-invalid="true"', false)
            ->assertSee($errors->first('name'))
            ->assertSee($errors->first('phone'));
    }

    public function test_account_without_gender_can_set_it_once(): void
    {
        $user = $this->user(null);

        $this->actingAs($user)->get('/account/profile')->assertOk()
            ->assertSee('account-shell', false)
            ->assertSee('name="gender"', false);

        $this->patch('/account/profile', [
            'name' => $user->name, 'email' => $user->email, 'gender' => 'nobody',
        ])->assertSessionHasErrors('gender');
        $this->assertNull($user->fresh()->gender);

        $this->patch('/account/profile', [
            'name' => $user->name, 'email' => $user->email, 'gender' => 'female',
        ])->assertSessionHasNoErrors();
        $this->assertSame('female', $user->fresh()->gender);

        $this->get('/account/profile')->assertOk()->assertDontSee('name="gender"', false);
    }

    public function test_gender_cannot_be_changed_once_set(): void
    {
        $user = $this->user('female');

        $this->actingAs($user)->patch('/account/profile', [
            'name' => $user->name, 'email' => $user->email, 'gender' => 'male',
        ])->assertSessionHasNoErrors();

        $this->assertSame('female', $user->fresh()->gender);
    }

    public function test_wrong_deletion_password_reopens_the_dialog(): void
    {
        $user = $this->user('male');

        $this->actingAs($user)->from('/account/profile')
            ->delete('/account/profile', ['password' => 'wrong-password'])
            ->assertRedirect('/account/profile')->assertSessionHasErrors('password');

        $this->get('/account/profile')->assertOk()
            ->assertSee("open: true", false);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_correct_password_deletes_the_account(): void
    {
        $user = $this->user('female');

        $this->actingAs($user)->delete('/account/profile', ['password' => self::PASSWORD])
            ->assertRedirect('/');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertGuest();
    }
}
