<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The sidebar has to start level with the rule under the page's heading.
 *
 * Its offset is calibrated per page, and the default used to be the
 * dashboard's — much taller than every other heading. A page nobody had listed
 * (archived notifications) therefore sat 170px too low. The default is the
 * ordinary heading now, so a page added later lines up without being listed.
 */
class AccountSidebarAlignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
    }

    private function provider(): User
    {
        $user = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);

        Profile::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'is_public' => true,
        ]);

        return $user->fresh();
    }

    /** The offset the sidebar renders with on this page. */
    private function offset(string $url): ?string
    {
        $html = $this->actingAs($this->provider())->get($url)->getContent();

        preg_match('/md:mt-\[(\d+)px\]/', $html, $m);

        return $m[1] ?? null;
    }

    public function test_archived_notifications_use_the_ordinary_offset(): void
    {
        $this->assertSame('88', $this->offset('/notifications/archived'));
    }

    public function test_the_ordinary_pages_share_one_offset(): void
    {
        foreach (['/account/photos', '/account/services', '/account/statistics'] as $url) {
            $this->assertSame('88', $this->offset($url), "Jiné odsazení na {$url}.");
        }
    }

    /** The dashboard's heading carries the stats row, so it stays the outlier. */
    public function test_the_dashboard_keeps_its_taller_offset(): void
    {
        $this->assertSame('258', $this->offset('/account'));
    }
}
