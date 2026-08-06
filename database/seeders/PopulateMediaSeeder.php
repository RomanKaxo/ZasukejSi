<?php

namespace Database\Seeders;

use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PopulateMediaSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = Profile::all();
        $imageFiles = File::files(public_path('images/models'));
        $imageFiles = array_values(array_filter($imageFiles, fn ($f) => str_starts_with($f->getFilename(), 'model')));

        foreach ($profiles as $profile) {
            if ($profile->getMedia('profile-images')->count() >= 2) {
                continue;
            }

            $profile->clearMediaCollection('profile-images');

            $picks = collect($imageFiles)->shuffle()->take(random_int(3, 5));

            foreach ($picks as $imageFile) {
                try {
                    $profile->addMedia($imageFile->getPathname())
                        ->preservingOriginal()
                        ->toMediaCollection('profile-images');
                } catch (\Exception $e) {
                    // Skip on failure, continue with remaining images
                }
            }
        }
    }
}
