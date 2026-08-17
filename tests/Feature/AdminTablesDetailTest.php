<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Notification;
use App\Models\Page;
use App\Models\Profile;
use App\Models\Report;
use App\Models\Segment;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The remaining listings answered "what is this row called" and left the
 * question an admin actually has — is this a pattern, is anybody in it, what
 * does it say — one click away, row by row.
 */
class AdminTablesDetailTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);
        $this->seed(CurrencySeeder::class);

        $user = User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);
        $user->syncRoles(['super_admin', 'admin']);

        return $user->fresh();
    }

    private function profile(string $name = 'Jana'): Profile
    {
        return Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'display_name' => ['cs' => $name, 'en' => $name],
            'city' => 'Praha',
        ]);
    }

    /**
     * One report is an incident; several against one profile is a pattern, and
     * that was not visible anywhere.
     */
    public function test_reports_show_how_often_a_profile_was_reported(): void
    {
        $admin = $this->admin();
        $profile = $this->profile();

        foreach (range(1, 3) as $i) {
            Report::create([
                'profile_id' => $profile->id,
                'reporter_id' => User::factory()->create(['gender' => 'male'])->id,
                'reason' => "Důvod {$i}",
                'allegations' => ['jine'],
            ]);
        }

        $response = $this->actingAs($admin)->get('/admin/reports');

        $response->assertSuccessful();
        $response->assertSee('Nahlášení celkem');
        $response->assertSee('3');
    }

    public function test_the_reports_screen_opens_on_what_is_unresolved(): void
    {
        $admin = $this->admin();
        $profile = $this->profile();

        Report::create([
            'profile_id' => $profile->id,
            'reporter_id' => User::factory()->create(['gender' => 'male'])->id,
            'reason' => 'Nevyřešené nahlášení',
            'allegations' => ['jine'],
        ]);

        Report::create([
            'profile_id' => $profile->id,
            'reporter_id' => User::factory()->create(['gender' => 'male'])->id,
            'reason' => 'Vyřízené nahlášení',
            'allegations' => ['jine'],
            'blocked_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports');

        $response->assertSuccessful();
        $response->assertSee('Nevyřešené nahlášení');
        $response->assertDontSee('Vyřízené nahlášení');
    }

    public function test_notifications_show_their_message_not_only_a_headline(): void
    {
        $admin = $this->admin();

        Notification::create([
            'title' => 'Nadpis oznámení',
            'message' => 'Tělo zprávy, které bylo dosud vidět až po otevření.',
            'type' => 'info',
            'is_global' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin/notifications')
            ->assertSuccessful()
            ->assertSee('Tělo zprávy');
    }

    public function test_a_city_shows_how_many_profiles_it_holds(): void
    {
        $admin = $this->admin();

        City::create(['name' => 'Praha', 'name_ascii' => 'Praha', 'country_code' => 'CZ']);
        City::create(['name' => 'Ostrava', 'name_ascii' => 'Ostrava', 'country_code' => 'CZ']);

        $this->profile();

        $response = $this->actingAs($admin)->get('/admin/cities');

        $response->assertSuccessful();
        $response->assertSee('Profilů');
    }

    public function test_a_segment_shows_how_many_profiles_are_in_it(): void
    {
        $admin = $this->admin();

        $segment = Segment::factory()->create(['slug' => 'nova']);
        $this->profile()->segments()->attach($segment);

        $this->actingAs($admin)
            ->get('/admin/segments')
            ->assertSuccessful()
            ->assertSee(__('segments.table.profiles'));
    }

    /**
     * Publishing meant opening the post, finding a toggle and saving — three
     * steps for a yes/no decision.
     */
    public function test_a_blog_post_can_be_published_from_the_listing(): void
    {
        $admin = $this->admin();

        $post = Page::create([
            'title' => ['cs' => 'Článek', 'en' => 'Article'],
            'slug' => 'clanek',
            'type' => 'blog',
            'content' => ['cs' => [], 'en' => []],
            'is_published' => false,
        ]);

        $this->actingAs($admin)
            ->get('/admin/blogs')
            ->assertSuccessful()
            ->assertSee(__('blogs.table.publish'));

        $post->update(['is_published' => true]);

        $this->actingAs($admin)
            ->get('/admin/blogs')
            ->assertSuccessful()
            ->assertSee(__('blogs.table.unpublish'));
    }

    public function test_every_listing_survives_an_empty_database(): void
    {
        $admin = $this->admin();

        // The counts are subqueries; an empty table must not make them throw.
        foreach (['/admin/reports', '/admin/notifications', '/admin/cities', '/admin/segments', '/admin/blogs', '/admin/ratings'] as $url) {
            $this->actingAs($admin)->get($url)->assertSuccessful();
        }
    }
}
