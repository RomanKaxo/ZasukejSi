<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The pink bar that says what is still missing from a profile.
 *
 * It was a fixed 1134px wide with no ceiling, so from 768px up to roughly
 * 1150px it was wider than the page: it started flush at the left edge, its
 * right half hung off the screen, and the content centred inside it therefore
 * did not look centred at all.
 */
class AccountCompletionAlertTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
    }

    /** A profile with nothing filled in, so the bar has something to report. */
    private function providerWithGaps(): User
    {
        $user = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);

        Profile::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'is_public' => true,
            'about' => null,
            'local_prices' => [],
        ]);

        return $user->fresh();
    }

    private function alertMarkup(User $user): ?string
    {
        $html = $this->actingAs($user)->get('/account/photos')->getContent();

        preg_match('/<div x-data="\{ show: true \}" x-show="show" class="([^"]*sticky[^"]*)"/', $html, $m);

        return $m[1] ?? null;
    }

    public function test_the_bar_never_grows_wider_than_the_page(): void
    {
        $classes = $this->alertMarkup($this->providerWithGaps());

        $this->assertNotNull($classes, 'Pruh se nevykreslil.');
        $this->assertStringContainsString('max-w-full', $classes);
    }

    public function test_the_bar_reports_what_is_actually_missing(): void
    {
        $response = $this->actingAs($this->providerWithGaps())->get('/account/photos');

        $response->assertSee(__('front.account.completion.prompt'));
        $response->assertSee(__('front.account.completion.photos'));
        $response->assertSee(__('front.account.completion.prices'));
    }

    /** Nothing to report, nothing rendered — not an empty bar. */
    public function test_a_user_without_a_profile_gets_no_bar(): void
    {
        $admin = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);

        $this->assertNull($this->alertMarkup($admin->fresh()));
    }
}
