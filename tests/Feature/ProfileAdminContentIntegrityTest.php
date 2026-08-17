<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * `profiles.content` holds the profile's physical/descriptive attributes
 * (card_height_cm, weight_kg, bust_size, nationality, languages, is_showcase …)
 * written by the provider-facing App\Livewire\ProfileForm.
 *
 * The Filament admin form pointed a block-builder field at the same column, so
 * saving a profile from the admin replaced that map with a list of content
 * blocks — silently destroying every attribute the provider had entered.
 */
class ProfileAdminContentIntegrityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Mirrors ProfileResourceSegmentsTest: ProfileResource is guarded by the
     * Shield-generated ProfilePolicy (bypassed for `super_admin`), and
     * ProfileForm's admin-only fields are gated by a literal
     * `$user->email === 'test@example.com'` check rather than by role.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Bust size and the other attribute lists come from the catalogue now,
        // and an empty catalogue means an unfillable select.
        $this->seed(\Database\Seeders\ProfileAttributeOptionSeeder::class);
    }

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'super_admin']);

        $admin = User::factory()->create(['email' => 'test@example.com']);
        $admin->syncRoles(['admin', 'super_admin']);

        return $admin;
    }

    public function test_saving_a_profile_in_the_admin_preserves_its_attributes(): void
    {
        $admin = $this->admin();

        $profile = Profile::factory()->for(User::factory())->create([
            'status' => 'approved',
            'is_public' => true,
            // Neutralised for the same reasons documented in
            // ProfileResourceSegmentsTest: unrelated ProfileForm validation
            // flakiness around country codes and price repeaters.
            'country_code' => null,
            'local_prices' => [],
            'global_prices' => [],
            'content' => [
                'card_height_cm' => 172,
                'weight_kg' => 58,
                'bust_size' => 'C',
                'languages' => 'cs,en',
                'is_showcase' => true,
            ],
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\Profiles\Pages\EditProfile::class, ['record' => $profile->getRouteKey()])
            ->fillForm(['age' => 29])
            ->call('save')
            ->assertHasNoFormErrors();

        $content = $profile->fresh()->content;

        $this->assertSame(172, $content['card_height_cm'] ?? null, 'Admin save wiped card_height_cm.');
        $this->assertSame(58, $content['weight_kg'] ?? null, 'Admin save wiped weight_kg.');
        $this->assertSame('C', $content['bust_size'] ?? null, 'Admin save wiped bust_size.');
        // Languages are picked from a list now and stored back joined with
        // ", ", so the separator is normalised. What matters is that nothing
        // the profile had is dropped.
        $this->assertSame(
            ['cs', 'en'],
            array_map('trim', explode(',', (string) ($content['languages'] ?? ''))),
            'Admin save wiped languages.',
        );
        $this->assertTrue($content['is_showcase'] ?? false, 'Admin save wiped is_showcase.');
        $this->assertSame(29, $profile->fresh()->age);
    }
}
