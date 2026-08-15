<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Service;
use Database\Seeders\PageSeeder;
use Database\Seeders\RoleSeeder;
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
        $this->assertSame(count(RoleSeeder::PERMISSIONS), Permission::count());
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
}
