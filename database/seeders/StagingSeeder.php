<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\Service;
use App\Models\Rating;
use Database\Seeders\ShowcaseProfilesSeeder;
use Illuminate\Database\Seeder;

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
     * Add profile images from placeholder services
     */
    private function addProfileImages(Profile $profile, int $count): void
    {
        $successCount = 0;
        $maxAttempts = $count * 2; // Allow retries
        $attempt = 0;
        
        while ($successCount < $count && $attempt < $maxAttempts) {
            try {
                $imageUrl = $this->getRandomPlaceholderImage($attempt);
                
                // Add timeout and user agent to avoid 403 errors
                $profile->addMediaFromUrl($imageUrl)
                    ->withResponsiveImages()
                    ->toMediaCollection('profile-images');
                    
                $successCount++;
                    
            } catch (\Exception $e) {
                // Log the error but continue
                $this->command->warn("  ⚠️  Failed to download image (attempt {$attempt}): {$e->getMessage()}");
                
                // If we've tried enough times, stop trying for this profile
                if ($attempt >= $maxAttempts - 1) {
                    $this->command->warn("  ⚠️  Skipping remaining images for profile {$profile->id}");
                    break;
                }
            }
            
            $attempt++;
            
            // Small delay to avoid rate limiting
            if ($attempt % 3 === 0) {
                usleep(500000); // 0.5 second delay every 3 attempts
            }
        }
        
        if ($successCount === 0) {
            $this->command->warn("  ⚠️  Could not add any images to profile {$profile->id}");
        }
    }
    
    /**
     * Get random placeholder image URL
     */
    private function getRandomPlaceholderImage(int $seed): string
    {
        // Use Lorem Picsum for reliable placeholder images
        // Adding random seed ensures different images each time
        $randomSeed = $seed . '-' . uniqid();
        
        return "https://picsum.photos/seed/{$randomSeed}/800/600";
    }
    
    /**
     * Add fallback image using local generation
     */
    private function addFallbackImage(Profile $profile, int $index): void
    {
        // Use picsum with different dimensions as fallback
        $randomSeed = 'fallback-' . $index . '-' . uniqid();
        $url = "https://picsum.photos/seed/{$randomSeed}/800/600";
        
        $profile->addMediaFromUrl($url)
            ->toMediaCollection('profile-images');
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
