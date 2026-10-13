<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Page;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileMediaPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('profile-media');
        Queue::fake();
    }

    private function profile(array $attributes = []): Profile
    {
        return Profile::factory()->create(array_merge([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'status' => 'pending',
            'is_public' => false,
        ], $attributes));
    }

    private function media(Profile|Page $model, string $collection = 'profile-images', string $disk = 'profile-media'): Media
    {
        $video = $collection === 'profile-video';
        $media = Media::create([
            'model_type' => $model->getMorphClass(), 'model_id' => $model->id,
            'collection_name' => $collection, 'name' => 'privacy-fixture',
            'file_name' => $video ? 'intro.mp4' : 'photo.jpg',
            'mime_type' => $video ? 'video/mp4' : 'image/jpeg',
            'disk' => $disk, 'conversions_disk' => $disk, 'size' => 13,
            'manipulations' => [], 'custom_properties' => ['verification_status' => 'pending'],
            'generated_conversions' => [], 'responsive_images' => [], 'order_column' => 3,
        ]);
        Storage::disk($disk)->put($media->getPathRelativeToRoot(), 'fixture-bytes');

        return $media;
    }

    public static function hiddenStates(): array
    {
        return [['pending', true], ['draft', true], ['rejected', true], ['approved', false]];
    }

    #[DataProvider('hiddenStates')]
    public function test_guests_cannot_read_nonpublic_profile_media_from_either_route(string $status, bool $public): void
    {
        $photo = $this->media($this->profile(['status' => $status, 'is_public' => $public]));
        $this->get($photo->getUrl())->assertNotFound();
        $this->get("/storage/{$photo->id}/{$photo->file_name}")->assertNotFound();
    }

    public function test_legacy_public_disk_media_also_requires_profile_access(): void
    {
        $photo = $this->media($this->profile(), disk: 'public');
        $this->get($photo->getUrl())->assertNotFound();
        $this->get("/storage/{$photo->id}/{$photo->file_name}")->assertNotFound();
        // This is why controller authorization alone does not close a static
        // web-server /storage symlink: the migration must remove these bytes.
        Storage::disk('public')->assertExists($photo->getPathRelativeToRoot());
    }

    public function test_owner_can_preview_image_conversion_and_video_but_another_user_cannot(): void
    {
        $profile = $this->profile();
        $photo = $this->media($profile);
        $photo->forceFill(['generated_conversions' => ['thumb' => true]])->save();
        Storage::disk('profile-media')->put($photo->getPathRelativeToRoot('thumb'), 'thumb-bytes');
        $video = $this->media($profile, 'profile-video');

        $this->actingAs($profile->user)->get($photo->getUrl())->assertOk();
        $this->get($photo->getUrl('thumb'))->assertOk();
        $this->get($video->getUrl())->assertOk();
        $this->actingAs(User::factory()->create())->get($photo->getUrl())->assertNotFound();
        $this->get($photo->getUrl('thumb'))->assertNotFound();
        $this->get($video->getUrl())->assertNotFound();
    }

    public function test_spatie_base_media_conversion_urls_also_use_the_authorized_route(): void
    {
        $profile = $this->profile();
        $photo = $this->media($profile);
        $photo->forceFill(['generated_conversions' => ['thumb' => true]])->save();
        Storage::disk('profile-media')->put($photo->getPathRelativeToRoot('thumb'), 'thumb-bytes');
        $baseMedia = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($photo->id);
        $url = $baseMedia->getUrl('thumb');
        $this->assertStringContainsString("/media/{$photo->id}/photo.jpg?conversion=thumb", $url);
        $this->get($url)->assertNotFound();
        $this->actingAs($profile->user)->get($url)->assertOk();
    }

    public function test_admin_and_super_admin_can_review_private_pending_verification_media(): void
    {
        $photo = $this->media($this->profile());
        foreach (['admin', 'super_admin'] as $name) {
            Role::findOrCreate($name, 'web');
            $admin = User::factory()->create();
            $admin->assignRole($name);
            $this->actingAs($admin)->get($photo->getUrl())->assertOk();
        }
    }

    public function test_approved_public_photos_work_from_private_storage_without_shared_caching(): void
    {
        $photo = $this->media($this->profile(['status' => 'approved', 'is_public' => true]));
        $response = $this->get($photo->getUrl())->assertOk();
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        Storage::disk('public')->assertMissing($photo->getPathRelativeToRoot());
        $photo->model->update(['is_public' => false]);
        $this->get($photo->getUrl())->assertNotFound();
    }

    public function test_deleted_profile_and_wrong_filename_are_not_served(): void
    {
        $profile = $this->profile();
        $photo = $this->media($profile);
        $this->actingAs($profile->user)->get("/media/{$photo->id}/wrong.jpg")->assertNotFound();
        $profile->delete();
        $this->get($photo->getUrl())->assertNotFound();
    }

    public function test_cms_media_keeps_its_public_disk_and_public_access(): void
    {
        $page = Page::create(['title' => ['cs' => 'CMS'], 'slug' => 'cms', 'type' => 'page']);
        $photo = $this->media($page, 'header-image', 'public');
        $this->get($photo->getUrl())->assertOk();
        $this->assertSame('public', $page->getRegisteredMediaCollections()->first()->diskName);
        Storage::disk('public')->assertExists($photo->getPathRelativeToRoot());
    }

    public function test_new_profile_uploads_and_their_conversions_use_private_disk(): void
    {
        $profile = $this->profile();
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aGCEAAAAASUVORK5CYII=');
        $photo = $profile->addMedia(UploadedFile::fake()->createWithContent('new.png', $png))
            ->toMediaCollection('profile-images');
        $this->assertSame('profile-media', $photo->disk);
        $this->assertSame('profile-media', $photo->conversions_disk);
        Storage::disk('profile-media')->assertExists($photo->getPathRelativeToRoot());
        Storage::disk('public')->assertMissing($photo->getPathRelativeToRoot());
        $collections = $profile->getRegisteredMediaCollections()->keyBy('name');
        $this->assertSame('profile-media', $collections['profile-video']->diskName);
        $this->assertSame('profile-media', $collections['profile-video']->conversionsDiskName);
        $this->assertFalse(config('filesystems.disks.profile-media.serve'));
    }
}
