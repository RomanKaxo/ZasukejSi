<?php

namespace Database\Factories;

use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as FakerFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ensure Faker is available (important for production)
        if (!function_exists('fake')) {
            $faker = FakerFactory::create();
        } else {
            $faker = fake();
        }
        
        $firstName = $faker->firstName('female');
        $lastName = $faker->lastName();
        
        // Generate realistic cities from Czech Republic and nearby regions
        $cities = [
            'Praha', 'Brno', 'Ostrava', 'Plzeň', 'Liberec', 'Olomouc', 
            'České Budějovice', 'Hradec Králové', 'Ústí nad Labem', 'Pardubice',
            'Zlín', 'Havířov', 'Kladno', 'Most', 'Opava', 'Frýdek-Místek',
            'Jihlava', 'Teplice', 'Karlovy Vary', 'Děčín'
        ];
        
        // Random availability hours
        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $availabilityHours = [];
        foreach ($weekdays as $day) {
            if ($faker->boolean(70)) { // 70% chance of being available
                $startHour = $faker->numberBetween(8, 14);
                $endHour = $faker->numberBetween($startHour + 4, 23);
                $availabilityHours[$day] = sprintf('%02d:00-%02d:00', $startHour, $endHour);
            }
        }
        
        // Generate price arrays
        $localPrices = [];
        $priceOptions = ['30min', '1hour', '2hours', 'overnight'];
        foreach ($priceOptions as $option) {
            if ($faker->boolean(80)) {
                $basePrice = $faker->numberBetween(1000, 5000);
                $localPrices[$option] = $basePrice;
            }
        }
        
        $globalPrices = [];
        foreach ($priceOptions as $option) {
            if ($faker->boolean(70)) {
                $basePrice = $faker->numberBetween(50, 500);
                $globalPrices[$option] = $basePrice;
            }
        }
        
        // Generate rich content for Filament blocks
        $content = $this->generateBlockContent($faker);

        return [
            'display_name' => [
                'en' => $firstName . ' ' . $lastName,
                'cs' => $firstName . ' ' . $lastName,
            ],
            'age' => $faker->numberBetween(18, 55),
            'city' => $faker->randomElement($cities),
            'address' => $faker->streetAddress(),
            'country_code' => $faker->randomElement(['CZ', 'SK', 'PL', 'AT', 'DE']),
            'about' => [
                'en' => $this->generateAboutText('en', $faker),
                'cs' => $this->generateAboutText('cs', $faker),
            ],
            'incall' => $faker->boolean(80),
            'outcall' => $faker->boolean(60),
            'content' => $content,
            'availability_hours' => $availabilityHours,
            'local_prices' => $localPrices,
            'global_prices' => $globalPrices,
            'status' => $faker->randomElement(['draft', 'pending', 'approved', 'rejected']),
            'is_public' => $faker->boolean(80),
            'verified_at' => $faker->boolean(60) ? now()->subDays($faker->numberBetween(1, 30)) : null,
        ];
    }
    
    /**
     * Generate realistic about text
     */
    private function generateAboutText(string $locale, $faker): string
    {
        if ($locale === 'cs') {
            $intros = [
                'Ahoj! Jsem profesionální společnice s mnohaletými zkušenostmi.',
                'Vítejte na mém profilu. Poskytuju luxusní společenské služby.',
                'Dobrý den, nabízím diskrétní a profesionální služby.',
                'Zdravím Vás, jsem elegantní společnice pro náročné klienty.',
            ];
            
            $qualities = [
                'Mám rád diskrétnost a profesionalitu.',
                'Zajišťuji příjemné a uvolněné prostředí.',
                'Jsem vždy čistý/á, voňavý/á a dobře oblečený/á.',
                'Mám smysl pro humor a ráda bavím.',
                'Jsem komunikativní a otevřený/á.',
            ];
            
            $services = [
                'Nabízím širokou škálu služeb podle Vašich přání.',
                'Rád/a splním Vaše fantazie v rámci mých možností.',
                'Můžeme se sejít u mě nebo přijedu k Vám.',
                'Vždy dodržuji dohodnuté časy a podmínky.',
            ];
        } else {
            $intros = [
                'Hello! I am a professional companion with many years of experience.',
                'Welcome to my profile. I provide luxury companionship services.',
                'Good day, I offer discreet and professional services.',
                'Greetings, I am an elegant companion for discerning clients.',
            ];
            
            $qualities = [
                'I value discretion and professionalism.',
                'I ensure a pleasant and relaxed environment.',
                'I am always clean, fragrant, and well-dressed.',
                'I have a sense of humor and love to entertain.',
                'I am communicative and open-minded.',
            ];
            
            $services = [
                'I offer a wide range of services according to your wishes.',
                'I am happy to fulfill your fantasies within my capabilities.',
                'We can meet at my place or I can come to you.',
                'I always respect agreed times and conditions.',
            ];
        }
        
        $text = $faker->randomElement($intros) . ' ';
        $text .= $faker->randomElement($qualities) . ' ';
        $text .= $faker->randomElement($qualities) . ' ';
        $text .= $faker->randomElement($services) . ' ';
        
        if ($faker->boolean(50)) {
            $text .= $faker->sentence();
        }
        
        return $text;
    }
    
    /**
     * Generate block content for Filament blocks
     */
    private function generateBlockContent($faker): array
    {
        $blocks = [];
        
        // Add some random blocks
        if ($faker->boolean(60)) {
            $blocks[] = [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                'data' => [
                    'content' => $faker->paragraphs(2, true),
                ],
            ];
        }
        
        if ($faker->boolean(40)) {
            $blocks[] = [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Heading',
                'data' => [
                    'content' => $faker->sentence(3),
                    'level' => 'h2',
                ],
            ];
        }
        
        if ($faker->boolean(50)) {
            $blocks[] = [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                'data' => [
                    'content' => '• ' . implode("\n• ", [
                        $faker->sentence(),
                        $faker->sentence(),
                        $faker->sentence(),
                    ]),
                ],
            ];
        }
        
        return $blocks;
    }

    /**
     * Indicate that the profile is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Indicate that the profile is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    /**
     * Indicate that the profile is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }
    
    /**
     * Indicate that the profile is VIP (verified).
     */
    public function vip(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }
    
    /**
     * Indicate that the profile is female.
     */
    public function female(): static
    {
        return $this->state(function (array $attributes) {
            $firstName = fake()->firstName('female');
            $lastName = fake()->lastName();
            return [
                'gender' => 'female',
                'display_name' => [
                    'en' => $firstName . ' ' . $lastName,
                    'cs' => $firstName . ' ' . $lastName,
                ],
            ];
        });
    }
    
    /**
     * Indicate that the profile is male.
     */
    public function male(): static
    {
        return $this->state(function (array $attributes) {
            $firstName = fake()->firstName('male');
            $lastName = fake()->lastName();
            return [
                'gender' => 'male',
                'display_name' => [
                    'en' => $firstName . ' ' . $lastName,
                    'cs' => $firstName . ' ' . $lastName,
                ],
            ];
        });
    }
    
    /**
     * Create a complete profile ready for public display.
     */
    public function complete(): static
    {
        return $this->approved()->public()->verified();
    }
    
    /**
     * Indicate the profile is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
    
    /**
     * Indicate the profile is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'is_public' => false,
        ]);
    }
}
