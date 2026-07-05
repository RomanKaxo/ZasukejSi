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
        
        foreach ($profiles as $index => $profile) {
            // Pick a random image
            $imageFile = $imageFiles[array_rand($imageFiles)];
            
            // Add media if not already exists
            if ($profile->getMedia('profile-images')->isEmpty()) {
                $profile->addMedia($imageFile->getPathname())
                    ->preservingOriginal()
                    ->toMediaCollection('profile-images');
            }
        }
    }
}
