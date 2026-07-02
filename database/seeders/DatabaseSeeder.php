<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\ShowcaseProfilesSeeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $vipRole = Role::firstOrCreate(['name' => 'vip']);

        // Create permissions if they don't exist
        $permissions = [
            'view_user',
            'view_any_user',
            'create_user',
            'update_user',
            'delete_user',
            'restore_user',
            'force_delete_user',
            'view_profile',
            'view_any_profile',
            'create_profile',
            'update_profile',
            'delete_profile',
            'restore_profile',
            'force_delete_profile',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign all permissions to admin
        $adminRole->givePermissionTo(Permission::all());

        // User permissions
        $userRole->givePermissionTo([
            'view_profile',
            'create_profile',
            'update_profile',
            'delete_profile',
        ]);

        $vipRole->givePermissionTo([
            'view_profile',
        ]);

        // Seed cities for autocomplete (must run before profiles are created)
        $this->call(CitySeeder::class);
        $this->call(SubscriptionTypeSeeder::class);

        // Create admin user
        $admin = User::firstOrCreate([
            'email' => 'test@example.com'
        ], [
            'name' => 'Admin User',
            'password' => Hash::make('admin123'),
            'gender' => 'male',
            'email_verified_at' => now(),
        ]);
        $admin->syncRoles(['super_admin', 'admin']);



        // Create a woman user with profile (female users can have profiles)
        $woman = User::firstOrCreate([
            'email' => 'woman@example.com'
        ], [
            'name' => 'Jane Doe',
            'password' => Hash::make('password'),
            'phone' => '+1234567890',
            'gender' => 'female',
            'email_verified_at' => now(),
        ]);
        $woman->syncRoles(['user']);

        if (!$woman->profile) {
            Profile::create([
                'user_id' => $woman->id,
                'display_name' => 'Jane Professional Massage',
                'age' => 28,
                'city' => 'New York',
                'address' => '123 Wellness Street',
                'about' => 'Professional massage therapist with 5+ years of experience. Specializing in relaxation and therapeutic massage.',
                'availability_hours' => [
                    'Monday' => '9:00-17:00',
                    'Tuesday' => '9:00-17:00',
                    'Wednesday' => '9:00-17:00',
                    'Thursday' => '9:00-17:00',
                    'Friday' => '9:00-17:00',
                ],
                'status' => 'approved',
                'is_public' => true,
                'verified_at' => now(),
                'country_code' => 'us',
            ]);
        }

        // Create a man user (male users cannot have profiles, they are members)
        $man = User::firstOrCreate([
            'email' => 'user@example.com'
        ], [
            'name' => 'John Smith',
            'password' => Hash::make('password'),
            'phone' => '+0987654321',
            'gender' => 'male',
            'email_verified_at' => now(),
        ]);
        $man->syncRoles(['user']);

        // Dedicated premium test accounts
        $premiumMale = User::updateOrCreate(
            ['email' => 'premium-muz@example.com'],
            [
                'name' => 'Premium Muz',
                'password' => Hash::make('password'),
                'phone' => '+420777000111',
                'gender' => 'male',
                'email_verified_at' => now(),
            ]
        );
        $premiumMale->syncRoles(['user', 'vip']);

        $premiumFemale = User::updateOrCreate(
            ['email' => 'premium-zena@example.com'],
            [
                'name' => 'Premium Zena',
                'password' => Hash::make('password'),
                'phone' => '+420777000222',
                'gender' => 'female',
                'email_verified_at' => now(),
            ]
        );
        $premiumFemale->syncRoles(['user']);

        $premiumFemaleProfile = Profile::updateOrCreate(
            ['user_id' => $premiumFemale->id],
            [
                'display_name' => 'Premium Zena Praha',
                'age' => 27,
                'city' => 'Prague',
                'address' => 'Praha 1',
                'about' => 'Testovaci premium profil pro kontrolu VIP zobrazeni a filtrovani.',
                'availability_hours' => [
                    'Monday' => '10:00-18:00',
                    'Tuesday' => '10:00-18:00',
                    'Wednesday' => '10:00-18:00',
                ],
                'status' => 'approved',
                'is_public' => true,
                'verified_at' => now(),
                'country_code' => 'cz',
            ]
        );

        $eliteType = SubscriptionType::firstWhere('slug', 'elite');

        if ($eliteType) {
            Subscription::updateOrCreate(
                [
                    'profile_id' => $premiumFemaleProfile->id,
                    'subscription_type_id' => $eliteType->id,
                    'status' => Subscription::STATUS_ACTIVE,
                ],
                [
                    'starts_at' => now()->subDay(),
                    'ends_at' => now()->addDays($eliteType->duration_days),
                    'auto_renew' => true,
                    'notes' => 'Premium test account with active elite subscription.',
                ]
            );
        }

        // Create demo users - females with profiles, males as members
        $cities = ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix'];
        $profileData = [
            ['name' => 'Sarah Johnson', 'gender' => 'female'],
            ['name' => 'Emma Wilson', 'gender' => 'female'],
            ['name' => 'Lisa Brown', 'gender' => 'female'],
            ['name' => 'Michael Garcia', 'gender' => 'male'],
            ['name' => 'David Davis', 'gender' => 'male'],
        ];

        foreach ($profileData as $index => $data) {
            $email = 'demo' . ($index + 1) . '@example.com';
            $demoUser = User::firstOrCreate([
                'email' => $email
            ], [
                'name' => $data['name'],
                'password' => Hash::make('password'),
                'gender' => $data['gender'],
                'email_verified_at' => now(),
            ]);
            $demoUser->syncRoles(['user']);

            // Only female users can have profiles
            if ($data['gender'] === 'female' && !$demoUser->profile) {
                $city = $cities[array_rand($cities)];
                Profile::create([
                    'user_id' => $demoUser->id,
                    'display_name' => $data['name'] . ' Massage Therapy',
                    'age' => rand(23, 45),
                    'city' => $city,
                    'address' => (rand(100, 999)) . ' Health Ave',
                    'about' => 'Experienced massage therapist offering relaxing and therapeutic treatments.',
                    'availability_hours' => [
                        'Monday' => '10:00-18:00',
                        'Wednesday' => '10:00-18:00',
                        'Friday' => '10:00-18:00',
                    ],
                    'status' => 'approved',
                    'is_public' => true,
                    'verified_at' => rand(0, 1) ? now() : null,
                    'country_code' => 'us',
                ]);
            }
        }

        // Czech demo users - females with profiles, males as members
        $czechCities = ['Prague', 'Brno', 'Ostrava', 'Plzen', 'Liberec'];
        $czechProfileData = [
            ['name' => 'Petra Nováková', 'gender' => 'female'],
            ['name' => 'Jana Svobodová', 'gender' => 'female'],
            ['name' => 'Lucie Dvořáková', 'gender' => 'female'],
            ['name' => 'Tomáš Novák', 'gender' => 'male'],
            ['name' => 'Martin Černý', 'gender' => 'male'],
        ];

        foreach ($czechProfileData as $index => $data) {
            $email = 'czdemo' . ($index + 1) . '@example.com';
            $demoUser = User::firstOrCreate([
                'email' => $email
            ], [
                'name' => $data['name'],
                'password' => Hash::make('password'),
                'gender' => $data['gender'],
                'email_verified_at' => now(),
            ]);
            $demoUser->syncRoles(['user']);

            // Only female users can have profiles
            if ($data['gender'] === 'female' && !$demoUser->profile) {
                $city = $czechCities[array_rand($czechCities)];
                Profile::create([
                    'user_id' => $demoUser->id,
                    'display_name' => $data['name'] . ' Masáže',
                    'age' => rand(23, 45),
                    'city' => $city,
                    'address' => (rand(100, 999)) . ' Relaxační ulice',
                    'about' => 'Zkušený masér/masérka nabízející relaxační a terapeutické masáže.',
                    'availability_hours' => [
                        'Pondělí' => '9:00-17:00',
                        'Středa' => '9:00-17:00',
                        'Pátek' => '9:00-17:00',
                    ],
                    'status' => 'approved',
                    'is_public' => true,
                    'verified_at' => rand(0, 1) ? now() : null,
                    'country_code' => 'cz',
                ]);
            }
        }

        // Create Services
        $services = [
            ['name' => ['cs' => 'Pozice 69', 'en' => 'Position 69'], 'sort_order' => 1],
            ['name' => ['cs' => 'Vaginální sex', 'en' => 'Vaginal sex'], 'sort_order' => 2],
            ['name' => ['cs' => 'Výstřik na obličej', 'en' => 'Facial'], 'sort_order' => 3],
            ['name' => ['cs' => 'Výstřik do pusy', 'en' => 'Cum in mouth'], 'sort_order' => 4],
            ['name' => ['cs' => 'Výstřik na tělo', 'en' => 'Cum on body'], 'sort_order' => 5],
            ['name' => ['cs' => 'Lízání', 'en' => 'Licking'], 'sort_order' => 6],
            ['name' => ['cs' => 'Nadávání', 'en' => 'Dirty talk'], 'sort_order' => 7],
            ['name' => ['cs' => 'Erotická masáž', 'en' => 'Erotic massage'], 'sort_order' => 8],
            ['name' => ['cs' => 'Facesitting', 'en' => 'Facesitting'], 'sort_order' => 9],
            ['name' => ['cs' => 'Prstění', 'en' => 'Fingering'], 'sort_order' => 10],
        ];

        foreach ($services as $serviceData) {
            Service::firstOrCreate(
                ['name' => $serviceData['name']],
                [
                    'description' => ['cs' => '', 'en' => ''],
                    'sort_order' => $serviceData['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        // Create some test ratings for profiles
        $profiles = Profile::where('is_public', true)->get();
        if ($profiles->count() > 0 && $admin) {
            // Add rating from admin to first profile
            $firstProfile = $profiles->first();
            \App\Models\Rating::firstOrCreate([
                'profile_id' => $firstProfile->id,
                'user_id' => $admin->id,
            ], [
                'rating' => 5,
            ]);

            // If Jane's profile exists, add some ratings to it
            if ($woman->profile) {
                // Admin rates Jane
                \App\Models\Rating::firstOrCreate([
                    'profile_id' => $woman->profile->id,
                    'user_id' => $admin->id,
                ], [
                    'rating' => 5,
                ]);

                // Create a couple test users (male members) to rate profiles
                for ($i = 1; $i <= 3; $i++) {
                    $testUser = User::firstOrCreate([
                        'email' => "user{$i}@example.com"
                    ], [
                        'name' => "Test User {$i}",
                        'password' => Hash::make('password'),
                        'gender' => 'male',
                        'email_verified_at' => now(),
                    ]);
                    $testUser->assignRole('user');

                    // Each test user rates Jane's profile
                    \App\Models\Rating::firstOrCreate([
                        'profile_id' => $woman->profile->id,
                        'user_id' => $testUser->id,
                    ], [
                        'rating' => rand(4, 5),
                    ]);
                }
            }
        }

        $this->call(ShowcaseProfilesSeeder::class);
    }
}
