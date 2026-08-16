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
            'kontaktni-zpravy' => ['/admin/contact-messages'],
            'menu-paticky' => ['/admin/footer-menu-items'],
            'texty-paticky' => ['/admin/manage-footer'],
            'nastaveni' => ['/admin/manage-settings'],
        ];
    }

    /**
     * The status badge used a match with no default, so a profile carrying any
     * status the list did not name took the whole listing down with a 500
     * instead of showing one unfamiliar row.
     */
    public function test_profile_listing_survives_an_unexpected_status(): void
    {
        $admin = $this->admin();

        $profile = Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
        ]);

        // Written straight to the column: the point is a value the resource
        // has never heard of.
        \Illuminate\Support\Facades\DB::table('profiles')
            ->where('id', $profile->id)
            ->update(['status' => 'blocked']);

        $this->actingAs($admin)->get('/admin/profiles')->assertSuccessful();
    }

    public function test_profile_listing_survives_an_empty_status(): void
    {
        $admin = $this->admin();

        $profile = Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
        ]);

        \Illuminate\Support\Facades\DB::table('profiles')
            ->where('id', $profile->id)
            ->update(['status' => '']);

        $this->actingAs($admin)->get('/admin/profiles')->assertSuccessful();
    }

    /**
     * availability_hours has had several shapes written to it over time. The
     * admin field has to render every one of them — a nested value used to
     * raise "Array to string conversion" and take the edit screen down.
     *
     * @dataProvider availabilityShapes
     */
    public function test_profile_edit_renders_any_availability_shape(mixed $availability): void
    {
        $admin = $this->admin();

        $profile = Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
        ]);

        $profile->forceFill(['availability_hours' => $availability])->save();

        $this->actingAs($admin)
            ->get("/admin/profiles/{$profile->id}/edit")
            ->assertSuccessful();
    }

    public static function availabilityShapes(): array
    {
        return [
            'mapa den => hodiny' => [['Monday' => '9:00-17:00']],
            'seznam textu' => [['Pondělí 9:00-17:00', 'Úterý 10:00-18:00']],
            'vnorene od/do' => [['Monday' => ['from' => '9:00', 'to' => '17:00']]],
            'dvakrat vnorene' => [['Monday' => [['from' => '9:00', 'to' => '12:00'], ['from' => '14:00', 'to' => '20:00']]]],
            'hodnoty ruznych typu' => [['Monday' => [true, null, 9, '17:00']]],
            'prazdne' => [[]],
            'null' => [null],
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
