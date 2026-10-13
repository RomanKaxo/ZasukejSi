<?php

namespace Tests\Feature;

use App\Livewire\ProfileForm;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * OutCall-only fixed slots are valid because the UI exposes both nullable
 * amounts independently. Saving must keep the entered OutCall amount.
 * An additional row may not duplicate a fixed slot and silently replace it.
 */
class ProfilePricePersistenceTest extends TestCase
{
    use RefreshDatabase;

    private function provider(string $kind): User
    {
        $user = User::factory()->create(['gender' => 'female', 'email_verified_at' => now(), 'phone' => null]);
        Profile::factory()->for($user)->create([
            'country_code' => 'CZ', 'city' => null, 'age' => 25,
            'availability_hours' => null, 'content' => [],
            'incall' => true, 'outcall' => true,
            'local_prices' => $kind === 'local' ? [['time_hours' => 0.5, 'incall_price' => 1234, 'outcall_price' => 2345]] : [],
            'global_prices' => $kind === 'global' ? [['time_hours' => 0.5, 'incall_price' => 1234, 'outcall_price' => 2345]] : [],
        ]);

        return $user->fresh();
    }

    private function assertOutcallOnlyIsKept(string $kind): void
    {
        $user = $this->provider($kind);
        Livewire::actingAs($user)->test(ProfileForm::class)
            ->set($kind.'_price_30_incall', '')
            ->set($kind.'_price_30_outcall', '8765')
            ->call('save')
            ->assertHasNoErrors();

        $rows = $user->profile->fresh()->{$kind.'_prices'};
        $this->assertNotEmpty($rows, 'The entered OutCall-only fixed slot was dropped.');
        $this->assertCount(1, $rows);
        $this->assertSame(0.5, (float) $rows[0]['time_hours']);
        $this->assertSame(8765.0, (float) $rows[0]['outcall_price']);
        $this->assertNull($rows[0]['incall_price']);
    }

    private function assertDuplicateFixedSlotIsRejected(string $kind): void
    {
        $user = $this->provider($kind);
        $before = $user->profile->{$kind.'_prices'};
        Livewire::actingAs($user)->test(ProfileForm::class)
            ->set($kind.'_prices', [['time_hours' => 0.5, 'incall_price' => 9999, 'outcall_price' => null]])
            ->call('save')
            ->assertHasErrors([$kind.'_prices.0.time_hours']);

        $this->assertSame($before, $user->profile->fresh()->{$kind.'_prices'}, 'An invalid duplicate duration changed the saved tariff.');
    }

    public function test_local_outcall_only_fixed_slot_persists(): void
    {
        $this->assertOutcallOnlyIsKept('local');
    }

    public function test_global_outcall_only_fixed_slot_persists(): void
    {
        $this->assertOutcallOnlyIsKept('global');
    }

    public function test_local_custom_row_cannot_duplicate_fixed_thirty_minutes(): void
    {
        $this->assertDuplicateFixedSlotIsRejected('local');
    }

    public function test_global_custom_row_cannot_duplicate_fixed_thirty_minutes(): void
    {
        $this->assertDuplicateFixedSlotIsRejected('global');
    }

    private function assertPublicDurationIsPreserved(int $hours, string $locale): void
    {
        $user = $this->provider('local');
        $row = ['time_hours' => $hours, 'incall_price' => 1010, 'outcall_price' => 2020];
        $profile = $user->profile;
        $profile->forceFill([
            'status' => 'approved', 'is_public' => true,
            'local_prices' => [$row], 'global_prices' => [$row],
        ])->save();

        // An anonymous HTTP request uses the public table, including the
        // locale-specific choice between local and international prices.
        $response = $this->get('/profiles/'.$profile->id.'?locale='.$locale)->assertOk();
        preg_match_all('/<td>([\d.]+h)<\/td>/', $response->getContent(), $matches);
        $this->assertContains($hours.'h', $matches[1]);
        $this->assertNotContains(($hours / 10).'h', $matches[1]);
    }

    public function test_czech_public_ten_hour_duration_stays_ten(): void
    {
        $this->assertPublicDurationIsPreserved(10, 'cs');
    }

    public function test_czech_public_twenty_hour_duration_stays_twenty(): void
    {
        $this->assertPublicDurationIsPreserved(20, 'cs');
    }

    public function test_english_public_ten_hour_duration_stays_ten(): void
    {
        $this->assertPublicDurationIsPreserved(10, 'en');
    }

    public function test_english_public_twenty_hour_duration_stays_twenty(): void
    {
        $this->assertPublicDurationIsPreserved(20, 'en');
    }
}
