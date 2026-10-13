<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Profile;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\PageSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServicesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Seeders have to survive a second run.
 *
 * PageSeeder used Page::create(), so re-running it died on
 * pages_slug_unique. The services block keyed firstOrCreate on the
 * translatable `name` column, which compares the encoded JSON as a string and
 * therefore never matched — every run appended another copy of the list.
 */
class SeederIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_seeder_can_run_twice(): void
    {
        $this->seed(PageSeeder::class);
        $afterFirst = Page::count();

        $this->assertGreaterThan(0, $afterFirst);

        // Would previously throw a QueryException on the unique slug.
        $this->seed(PageSeeder::class);

        $this->assertSame($afterFirst, Page::count());
    }

    public function test_page_seeder_does_not_duplicate_slugs(): void
    {
        $this->seed(PageSeeder::class);
        $this->seed(PageSeeder::class);

        $slugs = Page::pluck('slug');

        $this->assertSame($slugs->count(), $slugs->unique()->count());
    }

    public function test_role_seeder_can_run_twice(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->assertSame(count(RoleSeeder::ROLES), Role::count());
        // Seznam oprávnění se čte z politik, ne z konstanty — dvě sady, které
        // se musí shodovat, byly původní příčinou toho, že se rozešly.
        $this->assertSame(count(RoleSeeder::permissions()), Permission::count());
    }

    public function test_services_are_matched_on_the_translated_name(): void
    {
        Service::create([
            'name' => ['cs' => 'Lízání', 'en' => 'Licking'],
            'description' => ['cs' => '', 'en' => ''],
            'sort_order' => 6,
            'is_active' => true,
        ]);

        // Matching on the JSON path finds the existing row; comparing the whole
        // encoded value does not.
        $this->assertTrue(
            Service::query()->where('name->cs', 'Lízání')->exists()
        );
        $this->assertSame(1, Service::count());
    }

    public function test_services_seeder_fills_a_partial_catalog_without_changing_existing_selections(): void
    {
        $existing = Service::create([
            'name' => ['cs' => 'Lízání', 'en' => 'Custom translation'],
            'description' => ['cs' => 'Existing description'],
            'sort_order' => 99,
            'is_active' => false,
        ]);
        $custom = Service::create(['name' => ['cs' => 'Vlastní služba'], 'is_active' => true]);
        $profile = Profile::factory()->for(User::factory())->create();
        $profile->services()->attach([$existing->id, $custom->id]);

        $this->seed(ServicesSeeder::class);
        $this->assertSame(11, Service::count());
        $ids = Service::orderBy('id')->pluck('id')->all();

        $this->seed(ServicesSeeder::class);
        $this->assertSame($ids, Service::orderBy('id')->pluck('id')->all());
        $this->assertEqualsCanonicalizing([$existing->id, $custom->id], $profile->fresh()->services->modelKeys());
        $this->assertSame('Custom translation', $existing->fresh()->getTranslation('name', 'en'));
        $this->assertSame('Existing description', $existing->fresh()->getTranslation('description', 'cs'));
        $this->assertFalse($existing->fresh()->is_active);
        $this->assertSame(99, $existing->fresh()->sort_order);
    }

    public function test_services_are_rendered_once_per_real_service(): void
    {
        $owner = User::factory()->create(['gender' => 'female']);
        Profile::factory()->for($owner)->create();
        $first = Service::create(['name' => ['cs' => 'Masáž'], 'is_active' => true]);
        $second = Service::create(['name' => ['cs' => 'Doprovod'], 'is_active' => true]);

        $html = $this->actingAs($owner)->get('/account/services')->assertOk()->getContent();
        foreach ([$first, $second] as $service) {
            $this->assertSame(1, substr_count($html, 'wire:click="toggleService('.$service->id.')"'));
        }
    }

    public function test_services_seeder_can_fill_an_empty_catalog_and_run_again(): void
    {
        $this->seed(ServicesSeeder::class);
        $this->assertSame(10, Service::count());
        $ids = Service::orderBy('id')->pluck('id')->all();
        $this->seed(ServicesSeeder::class);
        $this->assertSame($ids, Service::orderBy('id')->pluck('id')->all());
    }

    public function test_services_seeder_reuses_an_existing_english_only_entry(): void
    {
        $existing = Service::create(['name' => ['en' => 'Position 69'], 'is_active' => true]);
        $this->seed(ServicesSeeder::class);
        $this->assertSame(10, Service::count());
        $this->assertSame($existing->id, Service::where('name->en', 'Position 69')->sole()->id);
    }
}
