<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BackendPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public static function listings(): array
    {
        return [
            'homepage' => [\App\Livewire\ProfileList::class],
            'countries' => [\App\Livewire\CountryProfiles::class],
            'slider' => [\App\Livewire\ProfileSlider::class],
        ];
    }

    private function profile(): Profile
    {
        $profile = Profile::factory()->for(User::factory()->state(['gender' => 'female']))->create([
            'status' => 'approved', 'is_public' => true, 'verified_at' => now(),
        ]);
        $profile->media()->create([
            'collection_name' => 'profile-images', 'name' => 'test', 'file_name' => 'test.jpg',
            'mime_type' => 'image/jpeg', 'disk' => 'profile-media', 'size' => 1,
            'manipulations' => [], 'custom_properties' => [], 'generated_conversions' => [], 'responsive_images' => [],
        ]);

        return $profile;
    }

    private function renderQueryCount(string $component): int
    {
        DB::enableQueryLog();
        DB::flushQueryLog();
        try {
            Livewire::test($component)->assertOk();

            return count(DB::getQueryLog());
        } finally {
            DB::disableQueryLog();
        }
    }

    #[DataProvider('listings')]
    public function test_listing_queries_do_not_grow_per_profile(string $component): void
    {
        $this->profile();
        // Warm translation/settings caches before comparing render queries.
        $this->renderQueryCount($component);
        $one = $this->renderQueryCount($component);
        foreach (range(1, 8) as $ignored) {
            $this->profile();
        }
        $this->renderQueryCount($component);
        $nine = $this->renderQueryCount($component);

        $this->assertSame($one, $nine, 'Rendering eight more cards must not add SQL queries.');
    }

    public function test_image_order_cache_tracks_media_mutations_and_returns_independent_collections(): void
    {
        $profile = $this->profile()->load('media');
        $first = $profile->media->first();
        $second = $first->replicate();
        $second->id = 999;
        $second->order_column = 2;
        $profile->media->push($second);
        $this->assertSame($first, $profile->getAllImages()->first());
        $profile->getAllImages()->pop();
        $this->assertCount(2, $profile->getAllImages());
        $second->setCustomProperty('is_main', true);
        $this->assertSame($second, $profile->getAllImages()->first());
        $profile->media->pop();
        $this->assertSame($first, $profile->getAllImages()->first());
        $profile->unsetRelation('media');
        $this->assertCount(1, $profile->getAllImages());
        $profile->setRelation('media', collect());
        $this->assertCount(0, $profile->getAllImages());
    }

    public function test_rating_aggregates_are_invalidated_after_rating_on_same_instance(): void
    {
        $profile = $this->profile();
        $loaded = Profile::withCount('ratings')->withAvg('ratings', 'percentage')->findOrFail($profile->id);
        $this->assertSame(0, $loaded->getTotalRatings());
        $this->assertSame(Profile::RATE_OK, $loaded->rateByPercentage(User::factory()->create(['gender' => 'male']), 70));
        $this->assertSame(1, $loaded->getTotalRatings());
        $this->assertSame(70.0, $loaded->getAveragePercentage());
    }

    public function test_loaded_active_subscription_does_not_query_for_each_vip_check(): void
    {
        $profile = $this->profile()->load('activeSubscription');
        DB::enableQueryLog();
        DB::flushQueryLog();
        try {
            $this->assertFalse($profile->isVip());
            $this->assertFalse($profile->isVip());
            $this->assertCount(0, DB::getQueryLog());
        } finally {
            DB::disableQueryLog();
        }
    }

    public function test_locale_is_only_written_when_it_changes(): void
    {
        $session = \Mockery::mock(\Illuminate\Session\Store::class);
        $session->shouldReceive('get')->with('locale')->andReturn('cs');
        $session->shouldNotReceive('put');
        $this->app->instance('session', $session);
        $request = Request::create('/?locale=cs');
        (new SetLocale)->handle($request, fn () => new \Symfony\Component\HttpFoundation\Response('ok'));
        $this->assertSame('cs', app()->getLocale());
    }

    public function test_locale_change_is_still_persisted(): void
    {
        $session = \Mockery::mock(\Illuminate\Session\Store::class);
        $session->shouldReceive('get')->with('locale')->andReturn('cs');
        $session->shouldReceive('put')->once()->with('locale', 'en');
        $this->app->instance('session', $session);
        (new SetLocale)->handle(Request::create('/?locale=en'), fn () => new \Symfony\Component\HttpFoundation\Response('ok'));
        $this->assertSame('en', app()->getLocale());
    }

    public function test_filter_indexes_migrate_and_roll_back_on_sqlite(): void
    {
        $migration = require database_path('migrations/2026_11_17_000001_add_public_profile_filter_indexes.php');
        $schema = DB::connection()->getSchemaBuilder();
        $name = 'profiles_public_country_created_idx';
        $this->assertTrue($schema->hasIndex('profiles', $name));
        $migration->down();
        $this->assertFalse($schema->hasIndex('profiles', $name));
        $migration->up();
        $this->assertTrue($schema->hasIndex('profiles', $name));
    }

    public function test_filter_migration_compiles_for_mysql_without_driver_specific_sql(): void
    {
        $migration = require database_path('migrations/2026_11_17_000001_add_public_profile_filter_indexes.php');
        config(['database.connections.performance_mysql' => ['driver' => 'mysql', 'host' => '127.0.0.1', 'database' => 'unused', 'username' => 'unused', 'password' => '', 'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'prefix' => '']]);
        $connection = DB::connection('performance_mysql');
        $previous = DB::getDefaultConnection();
        DB::setDefaultConnection('performance_mysql');
        \Illuminate\Support\Facades\Schema::clearResolvedInstance('db.schema');
        try {
            $up = $connection->pretend(fn () => $migration->up());
            $down = $connection->pretend(fn () => $migration->down());
            $this->assertCount(4, $up);
            $this->assertCount(4, $down);
            foreach ($up as $query) {
                $this->assertStringContainsString('alter table `profiles` add index', $query['query']);
            }
        } finally {
            DB::setDefaultConnection($previous);
            \Illuminate\Support\Facades\Schema::clearResolvedInstance('db.schema');
            DB::purge('performance_mysql');
        }
    }
}
