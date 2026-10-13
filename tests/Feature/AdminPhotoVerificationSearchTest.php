<?php

use App\Filament\Resources\PhotoVerificationResource\Pages\ListPhotoVerifications;
use App\Models\Profile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class AdminPhotoVerificationSearchTest extends TestCase
{
    use RefreshDatabase;

    private function pendingPhoto(): Media
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->syncRoles(['admin', 'super_admin']);
        $this->actingAs($admin->fresh());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $owner = User::factory()->create([
            'gender' => 'female',
            'email' => 'audit6-photo-owner@example.test',
        ]);
        $profile = Profile::factory()->create([
            'user_id' => $owner->id,
            'display_name' => ['cs' => 'Audit6 Photo Profile', 'en' => 'Audit6 Photo Profile'],
        ]);

        // Only metadata: no file creation/deletion or conversions in shared storage.
        return Media::create([
            'model_type' => Profile::class,
            'model_id' => $profile->id,
            'collection_name' => 'profile-images',
            'name' => 'audit6-photo',
            'file_name' => 'audit6-photo.jpg',
            'mime_type' => 'image/jpeg',
            'disk' => 'public',
            'conversions_disk' => 'public',
            'size' => 1,
            'manipulations' => [],
            'custom_properties' => ['verification_status' => 'pending'],
            'generated_conversions' => [],
            'responsive_images' => [],
            'order_column' => 1,
        ]);
    }

    public function test_search_matches_the_profile_owner_email_without_error(): void
    {
        $photo = $this->pendingPhoto();
        Livewire::test(ListPhotoVerifications::class)
            ->assertCanSeeTableRecords([$photo])
            ->searchTable('audit6-photo-owner@example.test')
            ->assertCanSeeTableRecords([$photo]);
    }

    public function test_nonmatching_search_returns_an_empty_table_without_error(): void
    {
        $photo = $this->pendingPhoto();
        Livewire::test(ListPhotoVerifications::class)
            ->assertCanSeeTableRecords([$photo])
            ->searchTable('Audit6NoSuchOwner348972')
            ->assertCanNotSeeTableRecords([$photo]);
    }
}
