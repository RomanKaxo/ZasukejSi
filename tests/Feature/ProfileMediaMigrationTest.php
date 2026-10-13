<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Page;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ProfileMediaMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('profile-media');
    }

    private function legacy(Profile|Page|null $model = null): Media
    {
        $model ??= Profile::factory()->create(['user_id' => User::factory()->create()->id]);
        $media = Media::create([
            'model_type' => $model->getMorphClass(), 'model_id' => $model->id,
            'collection_name' => $model instanceof Profile ? 'profile-images' : 'header-image',
            'name' => 'Original name', 'file_name' => 'original.jpg', 'mime_type' => 'image/jpeg',
            'disk' => 'public', 'conversions_disk' => 'public', 'size' => 14,
            'manipulations' => [], 'custom_properties' => ['is_main' => true, 'verification_status' => 'pending'],
            'generated_conversions' => $model instanceof Profile ? ['thumb' => true] : [],
            'responsive_images' => [], 'order_column' => 7,
        ]);
        Storage::disk('public')->put($media->getPathRelativeToRoot(), 'original-bytes');
        if ($model instanceof Profile) {
            Storage::disk('public')->put($media->getPathRelativeToRoot('thumb'), 'thumb-bytes');
            Storage::disk('public')->put("{$media->id}/responsive-images/retained.jpg", 'responsive-bytes');
        }

        return $media;
    }

    public function test_migration_preserves_metadata_and_bytes_then_removes_raw_public_files_idempotently(): void
    {
        $media = $this->legacy();
        $before = $media->getAttributes();
        $files = Storage::disk('public')->allFiles((string) $media->id);
        $contents = array_map(fn ($file) => Storage::disk('public')->get($file), $files);
        $this->artisan('media:privatize-profiles')->assertSuccessful();
        $after = $media->fresh()->getAttributes();
        foreach (['id', 'uuid', 'name', 'file_name', 'order_column', 'custom_properties', 'generated_conversions', 'responsive_images', 'created_at', 'updated_at'] as $key) {
            $this->assertSame($before[$key], $after[$key], $key);
        }
        $this->assertSame('profile-media', $after['disk']);
        $this->assertSame('profile-media', $after['conversions_disk']);
        foreach ($files as $index => $file) {
            Storage::disk('public')->assertMissing($file);
            $this->assertSame($contents[$index], Storage::disk('profile-media')->get($file));
        }
        $this->artisan('media:privatize-profiles')->assertSuccessful();
        foreach ($files as $index => $file) {
            $this->assertSame($contents[$index], Storage::disk('profile-media')->get($file));
        }
    }

    public function test_stage_supports_database_copies_but_explicitly_reports_public_exposure(): void
    {
        $media = $this->legacy();
        $this->artisan('media:privatize-profiles', ['--stage' => true])
            ->expectsOutputToContain('public files remain exposed')
            ->assertSuccessful();
        $this->assertSame('profile-media', $media->fresh()->disk);
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot());
        Storage::disk('profile-media')->assertExists($media->getPathRelativeToRoot());
        $this->artisan('media:privatize-profiles')->assertSuccessful();
        Storage::disk('public')->assertMissing($media->getPathRelativeToRoot());
    }

    public function test_deleted_profile_media_is_migrated_too_and_id_limit_does_not_touch_other_records(): void
    {
        $media = $this->legacy();
        $other = $this->legacy();
        $media->model->delete();
        $this->artisan('media:privatize-profiles', ['--id' => [$media->id]])->assertSuccessful();
        $this->assertSame('profile-media', $media->fresh()->disk);
        Storage::disk('profile-media')->assertExists($media->getPathRelativeToRoot('thumb'));
        Storage::disk('public')->assertMissing($media->getPathRelativeToRoot('thumb'));
        $this->assertSame('public', $other->fresh()->disk);
        Storage::disk('public')->assertExists($other->getPathRelativeToRoot());
    }

    public function test_dry_run_does_not_change_rows_or_files_and_cms_assets_are_untouched(): void
    {
        $media = $this->legacy();
        $page = Page::create(['title' => ['cs' => 'CMS'], 'slug' => 'migration-cms', 'type' => 'page']);
        $cms = $this->legacy($page);
        $this->artisan('media:privatize-profiles', ['--dry-run' => true])->assertSuccessful();
        $this->assertSame('public', $media->fresh()->disk);
        Storage::disk('profile-media')->assertMissing($media->getPathRelativeToRoot());
        $this->artisan('media:privatize-profiles')->assertSuccessful();
        $this->assertSame('public', $cms->fresh()->disk);
        Storage::disk('public')->assertExists($cms->getPathRelativeToRoot());
    }

    public function test_missing_original_stops_migration_and_preserves_existing_conversions(): void
    {
        $media = $this->legacy();
        Storage::disk('public')->delete($media->getPathRelativeToRoot());
        $this->artisan('media:privatize-profiles')->assertFailed();
        $this->assertSame('public', $media->fresh()->disk);
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot('thumb'));
    }

    public function test_conflicting_destination_is_not_overwritten_and_source_is_retained(): void
    {
        $media = $this->legacy();
        Storage::disk('profile-media')->put($media->getPathRelativeToRoot(), 'conflicting-data');
        $this->artisan('media:privatize-profiles')->assertFailed();
        $this->assertSame('public', $media->fresh()->disk);
        $this->assertSame('original-bytes', Storage::disk('public')->get($media->getPathRelativeToRoot()));
        $this->assertSame('conflicting-data', Storage::disk('profile-media')->get($media->getPathRelativeToRoot()));
    }

    public function test_copy_failure_retains_source_and_original_database_reference(): void
    {
        $media = $this->legacy();
        $public = Storage::disk('public');
        $broken = Mockery::mock(FilesystemAdapter::class);
        $broken->shouldReceive('exists')->andReturn(false);
        $broken->shouldReceive('put')->andReturn(false);
        $broken->shouldReceive('delete')->andReturn(true);
        Storage::shouldReceive('disk')->with('public')->andReturn($public);
        Storage::shouldReceive('disk')->with('profile-media')->andReturn($broken);
        $this->artisan('media:privatize-profiles')->assertFailed();
        $this->assertSame('public', $media->fresh()->disk);
        $this->assertSame('original-bytes', $public->get($media->getPathRelativeToRoot()));
    }

    public function test_failed_public_cleanup_is_reported_and_verified_private_copy_remains_recoverable(): void
    {
        $media = $this->legacy();
        $private = Storage::disk('profile-media');
        $public = Storage::disk('public');
        $blockedCleanup = Mockery::mock($public);
        $blockedCleanup->shouldReceive('delete')->andReturn(false);
        Storage::shouldReceive('disk')->with('public')->andReturn($blockedCleanup);
        Storage::shouldReceive('disk')->with('profile-media')->andReturn($private);
        $this->artisan('media:privatize-profiles')->expectsOutputToContain('Public cleanup incomplete')->assertFailed();
        $this->assertSame('profile-media', $media->fresh()->disk);
        $this->assertSame('original-bytes', $private->get($media->getPathRelativeToRoot()));
        $this->assertSame('original-bytes', $public->get($media->getPathRelativeToRoot()));
    }
}
