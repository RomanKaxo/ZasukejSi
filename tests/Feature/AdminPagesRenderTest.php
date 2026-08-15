<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every admin listing has to render. These pages were only ever exercised by
 * hand, so a resource that referenced a missing column or relation showed up
 * as a 500 in production rather than a failing test.
 */
class AdminPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create([
            'gender' => 'male',
            'email_verified_at' => now(),
        ]);
        $user->syncRoles(['super_admin', 'admin']);

        return $user->fresh();
    }

    public static function adminPages(): array
    {
        return [
            'profily' => ['/admin/profiles'],
            'uzivatele' => ['/admin/users'],
            'hodnoceni' => ['/admin/ratings'],
            'predplatna' => ['/admin/subscriptions'],
            'clenska-predplatna' => ['/admin/member-subscriptions'],
            'typy-predplatneho' => ['/admin/subscription-types'],
            'zeme' => ['/admin/countries'],
            'preklady' => ['/admin/translations'],
            'stranky' => ['/admin/pages'],
            'sluzby' => ['/admin/services'],
            'segmenty' => ['/admin/segments'],
            'scraper-zdroje' => ['/admin/scrape-sources'],
            'scraper-polozky' => ['/admin/scrape-items'],
            'scraper-behy' => ['/admin/scrape-runs'],
            'nastaveni' => ['/admin/manage-settings'],
        ];
    }

    /**
     * @dataProvider adminPages
     */
    public function test_admin_page_renders(string $url): void
    {
        $admin = $this->admin();

        // A row of real data, so a column referenced by the table is exercised
        // rather than the empty state.
        Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
        ]);

        $this->actingAs($admin)->get($url)->assertSuccessful();
    }
}
