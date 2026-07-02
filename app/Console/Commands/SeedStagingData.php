<?php

namespace App\Console\Commands;

use Database\Seeders\StagingSeeder;
use Illuminate\Console\Command;

class SeedStagingData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'staging:seed 
                            {--count=20 : Number of users with profiles to create}
                            {--fresh : Wipe all existing data before seeding}
                            {--force : Skip confirmation prompt (for automated deployments)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with staging test data (users, profiles, images, services, ratings)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = (int) $this->option('count');
        $fresh = $this->option('fresh');
        $force = $this->option('force');
        
        $this->info('🚀 Staging Data Seeder');
        $this->info('=====================');
        
        if ($fresh) {
            if (!$force && !$this->confirm('⚠️  This will DELETE ALL existing data. Are you sure?', false)) {
                $this->info('Operation cancelled.');
                return self::SUCCESS;
            }
            
            $this->call('migrate:fresh');
            $this->newLine();
        }
        
        $this->info("Creating {$count} users with profiles...");
        $this->newLine();
        
        // Run the staging seeder with custom count
        app()->instance('staging.user.count', $count);
        
        $this->call('db:seed', [
            '--class' => StagingSeeder::class,
        ]);
        
        $this->newLine();
        $this->info('📊 Summary:');
        $this->table(
            ['Entity', 'Count'],
            [
                ['Users', \App\Models\User::count()],
                ['Profiles', \App\Models\Profile::count()],
                ['Approved Profiles', \App\Models\Profile::approved()->count()],
                ['Services', \App\Models\Service::count()],
                ['Ratings', \App\Models\Rating::count()],
            ]
        );
        
        $this->newLine();
        $this->info('✅ Staging data seeded successfully!');
        $this->info('🔐 Default password for all users: password');
        $this->info('👤 Admin login: test@example.com / admin123');
        
        return self::SUCCESS;
    }
}
