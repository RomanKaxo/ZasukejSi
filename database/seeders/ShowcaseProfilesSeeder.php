<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ShowcaseProfilesSeeder extends Seeder
{
    public function run(): void
    {
        $showcaseProfiles = [
            [
                'email' => 'showcase-alexandra@example.com',
                'name' => 'Alexandra',
                'city' => 'Ostrava',
                'card_location' => 'Moravskoslezský kraj',
                'age' => 19,
                'height_cm' => 168,
            ],
            [
                'email' => 'showcase-tamara@example.com',
                'name' => 'Tamara',
                'city' => 'Prague',
                'card_location' => 'Praha',
                'age' => 19,
                'height_cm' => 168,
            ],
            [
                'email' => 'showcase-jana@example.com',
                'name' => 'Jana',
                'city' => 'Ostrava',
                'card_location' => 'Ostrava',
                'age' => 19,
                'height_cm' => 168,
            ],
            [
                'email' => 'showcase-angela@example.com',
                'name' => 'Angela',
                'city' => 'Brno',
                'card_location' => 'Jihomoravský kraj',
                'age' => 19,
                'height_cm' => 168,
            ],
            [
                'email' => 'showcase-lucie@example.com',
                'name' => 'Lucie',
                'city' => 'Brno',
                'card_location' => 'Brno',
                'age' => 19,
                'height_cm' => 168,
            ],
        ];

        $sourceProfiles = Profile::query()
            ->with('user')
            ->where('status', 'approved')
            ->where('is_public', true)
            ->orderByDesc('created_at')
            ->limit(count($showcaseProfiles))
            ->get()
            ->values();

        $eliteType = SubscriptionType::query()->firstWhere('slug', 'elite')
            ?? SubscriptionType::query()->firstWhere('slug', 'premium')
            ?? SubscriptionType::query()->firstWhere('slug', 'vip');

        foreach ($showcaseProfiles as $index => $showcaseProfile) {
            $profile = $sourceProfiles->get($index);

            if (!$profile) {
                $user = User::updateOrCreate(
                    ['email' => $showcaseProfile['email']],
                    [
                        'name' => $showcaseProfile['name'],
                        'password' => Hash::make('password'),
                        'gender' => 'female',
                        'email_verified_at' => now(),
                    ]
                );

                $user->syncRoles(['user']);

                $profile = Profile::firstOrNew(['user_id' => $user->id]);
            } else {
                $user = $profile->user;

                if ($user) {
                    $user->forceFill([
                        'name' => $showcaseProfile['name'],
                        'gender' => 'female',
                        'email_verified_at' => now(),
                    ])->save();
                }
            }

            $content = is_array($profile->content) ? $profile->content : [];
            $content['card_location'] = $showcaseProfile['card_location'];
            $content['card_height_cm'] = $showcaseProfile['height_cm'];
            $content['is_showcase'] = true;

            $profile->forceFill([
                'display_name' => [
                    'cs' => $showcaseProfile['name'],
                    'en' => $showcaseProfile['name'],
                ],
                'age' => $showcaseProfile['age'],
                'city' => $showcaseProfile['city'],
                'country_code' => 'cz',
                'address' => $showcaseProfile['card_location'],
                'status' => 'approved',
                'is_public' => true,
                'verified_at' => now(),
                'content' => $content,
            ]);

            $profile->save();

            $profile->timestamps = false;
            $profile->created_at = now()->subMinutes($index);
            $profile->updated_at = now()->subMinutes($index);
            $profile->save();
            $profile->timestamps = true;

            if ($eliteType) {
                Subscription::updateOrCreate(
                    [
                        'profile_id' => $profile->id,
                        'subscription_type_id' => $eliteType->id,
                    ],
                    [
                        'starts_at' => now()->subDay(),
                        'ends_at' => now()->addDays($eliteType->duration_days),
                        'status' => Subscription::STATUS_ACTIVE,
                        'auto_renew' => true,
                        'notes' => 'Deterministic showcase profile sync.',
                    ]
                );
            }

            // Attach model image
            $modelImageNumber = $index + 1;
            $modelImagePath = public_path("images/models/model{$modelImageNumber}.png");
            
            if (file_exists($modelImagePath)) {
                try {
                    // Clear existing media first
                    $profile->clearMediaCollection('profile-images');
                    
                    // Add model image
                    $profile->addMedia($modelImagePath)
                        ->preservingOriginal()
                        ->toMediaCollection('profile-images');
                } catch (\Exception $e) {
                    // Silently continue if image attachment fails
                }
            }
        }
    }
}