<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Segment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileResourceSegmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_segments_to_a_profile_via_edit_form(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'super_admin']);
        // ProfileForm's admin-only fields (status, verified_at, segments) are
        // gated by a hardcoded "$user->email === 'test@example.com'" check
        // (see the "Temporary admin check" comment in ProfileForm::configure),
        // not by role/permission — so the acting user's email must match that
        // literal value for the segments field to even render.
        $admin = User::factory()->create(['email' => 'test@example.com']);
        // ProfileResource itself is guarded by ProfilePolicy (Filament
        // Shield-generated, ability names like "Update:Profile"). The app's
        // real admin user is synced to both 'admin' and 'super_admin' roles
        // (see DatabaseSeeder), and 'super_admin' is configured as a
        // Gate::before bypass role in config/filament-shield.php. Mirror that
        // here so this test exercises the same authorization path as
        // production admins.
        $admin->syncRoles(['admin', 'super_admin']);

        // country_code / local_prices / global_prices are overridden to avoid
        // pre-existing, unrelated flakiness in ProfileForm validation: the
        // factory's uppercase country codes ("CZ") don't match the form's
        // lowercase Select options, and Repeater rows can randomly get a
        // null outcall_price despite the field being ->required(). Neither
        // issue is related to segments; this keeps the test deterministic.
        $profile = Profile::factory()->for(User::factory())->create([
            'country_code' => null,
            'local_prices' => [],
            'global_prices' => [],
        ]);
        $segment = Segment::factory()->create(['slug' => 'nova']);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\Profiles\Pages\EditProfile::class, ['record' => $profile->getRouteKey()])
            ->fillForm(['segments' => [$segment->id]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($profile->fresh()->segments->contains('id', $segment->id));
    }
}
