<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\Service;
use App\Models\Rating;
use Database\Seeders\ShowcaseProfilesSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class StagingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates comprehensive test data for staging environments:
     * - Multiple users with profiles
     * - Profile images from placeholder services
     * - Attached services
     * - Ratings and reviews
     * 
     * Usage: php artisan db:seed --class=StagingSeeder
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting staging data seeding...');
        
        // Run DatabaseSeeder first (roles, permissions, basic data)
        $this->call(DatabaseSeeder::class);
        
        // Seed cities for autocomplete
        $this->call(CitySeeder::class);
        
        // Get count from container or use default
        $count = app()->bound('staging.user.count') ? app('staging.user.count') : 20;
        
        // Create regular users with profiles
        $this->createUsersWithProfiles($count);
        
        // Seed VIP subscriptions for some profiles
        $this->call(SubscriptionSeeder::class);

        // Re-map the first showcase cards to deterministic names and metadata.
        $this->call(ShowcaseProfilesSeeder::class);
        
        // Seed pages (blog posts, FAQ, etc.)
        $this->seedPages();
        
        $this->command->info('✅ Staging data seeding completed!');
    }
    
    /**
     * Seed pages using PageSeeder
     */
    private function seedPages(): void
    {
        if (\App\Models\Page::count() === 0) {
            $this->call(PageSeeder::class);
        } else {
            $this->command->info('✓ Pages already exist, skipping');
        }
    }
    
    /**
     * Create regular users with profiles (female) and member users (male)
     */
    private function createUsersWithProfiles(int $count): void
    {
        $this->command->info("Creating {$count} female users with profiles...");
        $progressBar = $this->command->getOutput()->createProgressBar($count);
        
        $services = Service::where('is_active', true)->pluck('id')->toArray();
        
        for ($i = 0; $i < $count; $i++) {
            // Create female user (profile owners)
            $user = User::factory()->create([
                'gender' => 'female',
            ]);
            $user->assignRole('user');
            
            // Create profile for female user
            $profile = Profile::factory()
                ->for($user)
                ->create();
            
            // Randomly set some profiles as approved and public (for frontend testing)
            if (fake()->boolean(70)) {
                $profile->status = 'approved';
                $profile->is_public = true;
                $profile->save();
            }
            
            // Attach random services (3-10 services per profile)
            $randomServices = fake()->randomElements($services, fake()->numberBetween(3, min(10, \count($services))));
            $profile->services()->attach($randomServices);
            
            // Add profile images (2-6 images per profile)
            $this->addProfileImages($profile, fake()->numberBetween(2, 6));
            
            // Add some ratings (for approved profiles)
            if ($profile->status === 'approved') {
                $this->addRatings($profile, fake()->numberBetween(0, 8));
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("✓ Created {$count} female users with profiles");
        
        // Create some male member users (no profiles)
        $maleCount = intval($count / 2);
        $this->command->info("Creating {$maleCount} male member users...");
        
        for ($i = 0; $i < $maleCount; $i++) {
            $maleUser = User::factory()->create([
                'gender' => 'male',
            ]);
            $maleUser->assignRole('user');
        }
        
        $this->command->info("✓ Created {$maleCount} male member users");
    }
    
    /**
     * Add profile images from the local model image pool.
     * Uses local files (no network dependency) so every profile reliably gets photos.
     */
    private function addProfileImages(Profile $profile, int $count): void
    {
        $imageFiles = collect(File::files(public_path('images/models')))
            ->filter(fn ($f) => str_starts_with($f->getFilename(), 'model'))
            ->values();

        if ($imageFiles->isEmpty()) {
            $this->command->warn("  ⚠️  No local model images found for profile {$profile->id}");
            return;
        }

        $picks = $imageFiles->shuffle()->take($count);

        foreach ($picks as $imageFile) {
            try {
                $profile->addMedia($imageFile->getPathname())
                    ->preservingOriginal()
                    ->toMediaCollection('profile-images');
            } catch (\Exception $e) {
                $this->command->warn("  ⚠️  Failed to attach image to profile {$profile->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Add ratings to profile
     */
    private function addRatings(Profile $profile, int $count): void
    {
        if ($count === 0) {
            return;
        }
        
        // Get some random users to rate this profile
        $ratingUsers = User::where('id', '!=', $profile->user_id)
            ->inRandomOrder()
            ->limit($count)
            ->get();
        
        foreach ($ratingUsers as $ratingUser) {
            Rating::create([
                'profile_id' => $profile->id,
                'user_id' => $ratingUser->id,
                'rating' => fake()->numberBetween(3, 5), // Mostly positive ratings (3-5 stars)
            ]);
        }
    }
}
