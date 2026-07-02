<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates VIP subscriptions for random profiles.
     * It assigns active subscriptions to make profiles appear as VIP.
     * 
     * Usage: php artisan db:seed --class=SubscriptionSeeder
     */
    public function run(): void
    {
        $this->command->info('🌟 Seeding VIP subscriptions...');
        
        // Ensure subscription types exist
        $this->ensureSubscriptionTypesExist();
        
        // Get all subscription types
        $subscriptionTypes = SubscriptionType::where('is_active', true)->get();
        
        if ($subscriptionTypes->isEmpty()) {
            $this->command->warn('No active subscription types found. Skipping subscription seeding.');
            return;
        }
        
        // Get approved profiles without active subscriptions
        $profiles = Profile::approved()
            ->public()
            ->whereDoesntHave('subscriptions', function ($query) {
                $query->where('status', 'active')
                    ->where('ends_at', '>', now());
            })
            ->get();
        
        if ($profiles->isEmpty()) {
            $this->command->info('No eligible profiles found for VIP subscriptions.');
            return;
        }
        
        // Assign VIP to approximately 30% of profiles
        $vipCount = max(1, intval($profiles->count() * 0.3));
        $selectedProfiles = $profiles->random(min($vipCount, $profiles->count()));
        
        $this->command->info("Creating VIP subscriptions for {$selectedProfiles->count()} profiles...");
        
        $progressBar = $this->command->getOutput()->createProgressBar($selectedProfiles->count());
        
        foreach ($selectedProfiles as $profile) {
            $this->createSubscriptionForProfile($profile, $subscriptionTypes);
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("✓ Created {$selectedProfiles->count()} VIP subscriptions");
    }
    
    /**
     * Ensure subscription types exist by calling the SubscriptionTypeSeeder
     */
    private function ensureSubscriptionTypesExist(): void
    {
        if (SubscriptionType::count() === 0) {
            $this->call(SubscriptionTypeSeeder::class);
        }
    }
    
    /**
     * Create a subscription for a profile
     */
    private function createSubscriptionForProfile(Profile $profile, $subscriptionTypes): void
    {
        // Randomly select a subscription type
        $subscriptionType = $subscriptionTypes->random();
        
        // Randomize subscription start - some started recently, some a while ago
        $daysAgo = fake()->numberBetween(0, 20);
        $startsAt = now()->subDays($daysAgo);
        
        // Calculate end date based on subscription type duration
        $endsAt = $startsAt->copy()->addDays($subscriptionType->duration_days);
        
        // Only create if subscription would still be active
        if ($endsAt->isFuture()) {
            Subscription::create([
                'profile_id' => $profile->id,
                'subscription_type_id' => $subscriptionType->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => Subscription::STATUS_ACTIVE,
                'auto_renew' => fake()->boolean(30), // 30% have auto-renew
                'notes' => fake()->boolean(20) ? fake()->sentence() : null,
            ]);
        }
    }
    
    /**
     * Create VIP subscriptions for specific number of profiles
     * Can be called from other seeders with custom count
     */
    public function seedForProfiles(int $count): void
    {
        $subscriptionTypes = SubscriptionType::where('is_active', true)->get();
        
        if ($subscriptionTypes->isEmpty()) {
            $this->ensureSubscriptionTypesExist();
            $subscriptionTypes = SubscriptionType::where('is_active', true)->get();
        }
        
        $profiles = Profile::approved()
            ->public()
            ->whereDoesntHave('subscriptions', function ($query) {
                $query->where('status', 'active')
                    ->where('ends_at', '>', now());
            })
            ->inRandomOrder()
            ->limit($count)
            ->get();
        
        foreach ($profiles as $profile) {
            $this->createSubscriptionForProfile($profile, $subscriptionTypes);
        }
    }
}
