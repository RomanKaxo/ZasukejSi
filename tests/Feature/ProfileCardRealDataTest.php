<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The profile card used to show "168 cm" for everyone. The cause was not a
 * hardcoded string but a missing column: the listing queries selected an
 * explicit column list that omitted `content`, so the card's
 * `$cardContent['card_height_cm'] ?? 168` fallback fired for every row —
 * including profiles that had a real height stored.
 *
 * These tests lock in both halves of the fix: the column reaches the card, and
 * a profile without a height renders an empty placeholder rather than a
 * plausible-looking number.
 */
class ProfileCardRealDataTest extends TestCase
{
    use RefreshDatabase;

    private function publicProfile(array $attributes = []): Profile
    {
        return Profile::factory()->for(User::factory())->create(array_merge([
            'status' => 'approved',
            'is_public' => true,
        ], $attributes));
    }

    public function test_card_shows_the_real_height_when_the_profile_has_one(): void
    {
        $profile = $this->publicProfile(['content' => ['card_height_cm' => 172]]);

        $html = (string) $this->blade(
            '<x-profile-card :profile="$profile" />',
            ['profile' => $profile->fresh()->load('segments')]
        );

        $this->assertStringContainsString('172 cm', $html);
        $this->assertStringNotContainsString('168 cm', $html);
    }

    public function test_card_shows_an_empty_placeholder_instead_of_inventing_a_height(): void
    {
        $profile = $this->publicProfile(['content' => []]);

        $html = (string) $this->blade(
            '<x-profile-card :profile="$profile" />',
            ['profile' => $profile->fresh()->load('segments')]
        );

        $this->assertStringNotContainsString('168 cm', $html);
        $this->assertStringContainsString('empty-value', $html);
    }

    public function test_card_never_falls_back_to_stock_model_photos(): void
    {
        $profile = $this->publicProfile();

        $html = (string) $this->blade(
            '<x-profile-card :profile="$profile" />',
            ['profile' => $profile->fresh()->load('segments')]
        );

        $this->assertStringNotContainsString('images/models/model', $html);
    }

    /**
     * The regression that caused this in the first place: a listing query whose
     * explicit column list drops `content`.
     */
    public function test_profile_list_query_delivers_the_content_column_to_the_card(): void
    {
        $this->publicProfile(['content' => ['card_height_cm' => 181]]);

        Livewire::test(\App\Livewire\ProfileList::class)
            ->assertSee('181 cm')
            ->assertDontSee('168 cm');
    }

    /**
     * The slider's own template only renders profiles that have at least one
     * image, so this asserts on the query rather than the markup: what matters
     * is that `content` survives the explicit column list and reaches the card.
     */
    public function test_profile_slider_query_delivers_the_content_column_to_the_card(): void
    {
        $this->publicProfile(['content' => ['card_height_cm' => 159]]);

        $profile = Livewire::test(\App\Livewire\ProfileSlider::class)
            ->instance()
            ->profiles()
            ->first();

        $this->assertNotNull($profile);
        $this->assertSame(159, $profile->content['card_height_cm'] ?? null);
    }

    /**
     * allSegments() is called for every card, so the relation must be
     * eager-loaded by the query rather than lazily per row.
     */
    public function test_profile_slider_eager_loads_segments(): void
    {
        $this->publicProfile();

        $profile = Livewire::test(\App\Livewire\ProfileSlider::class)
            ->instance()
            ->profiles()
            ->first();

        $this->assertTrue($profile->relationLoaded('segments'));
    }
}
