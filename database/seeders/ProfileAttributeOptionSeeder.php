<?php

namespace Database\Seeders;

use App\Models\ProfileAttributeOption;
use Illuminate\Database\Seeder;

/**
 * The option lists the profile form used to carry as hardcoded arrays.
 *
 * Bust size lived as `['A','B',…]` inside a Livewire component; the rest did
 * not exist at all, which is why the scraper's eye colour, hair colour and the
 * others had nowhere to go. Anything a source offers beyond these lands in the
 * "to be added" queue rather than being invented here.
 */
class ProfileAttributeOptionSeeder extends Seeder
{
    /** @var array<string, array<int, array{cs: string, en: string}>> */
    private const OPTIONS = [
        'bust_size' => [
            ['cs' => 'A', 'en' => 'A'],
            ['cs' => 'B', 'en' => 'B'],
            ['cs' => 'C', 'en' => 'C'],
            ['cs' => 'D', 'en' => 'D'],
            ['cs' => 'E', 'en' => 'E'],
            ['cs' => 'F', 'en' => 'F'],
            ['cs' => 'G', 'en' => 'G'],
            ['cs' => 'H', 'en' => 'H'],
        ],
        'bust_type' => [
            ['cs' => 'Přírodní', 'en' => 'Natural'],
            ['cs' => 'Silikon', 'en' => 'Silicone'],
        ],
        'eye_colour' => [
            ['cs' => 'Modrá', 'en' => 'Blue'],
            ['cs' => 'Zelená', 'en' => 'Green'],
            ['cs' => 'Hnědá', 'en' => 'Brown'],
            ['cs' => 'Černá', 'en' => 'Black'],
            ['cs' => 'Šedá', 'en' => 'Grey'],
        ],
        'hair_colour' => [
            ['cs' => 'Blond', 'en' => 'Blonde'],
            ['cs' => 'Hnědá', 'en' => 'Brown'],
            ['cs' => 'Černá', 'en' => 'Black'],
            ['cs' => 'Zrzavá', 'en' => 'Red'],
        ],
        'hair_length' => [
            ['cs' => 'Krátká', 'en' => 'Short'],
            ['cs' => 'Středně dlouhé', 'en' => 'Medium'],
            ['cs' => 'Dlouhá', 'en' => 'Long'],
        ],
        'pubic_hair' => [
            ['cs' => 'Oholené', 'en' => 'Shaved'],
            ['cs' => 'Zastřižené', 'en' => 'Trimmed'],
            ['cs' => 'Přírodní', 'en' => 'Natural'],
        ],
        'travels' => [
            ['cs' => 'Ne', 'en' => 'No'],
            ['cs' => 'V rámci země', 'en' => 'Within the country'],
            ['cs' => 'Evropa', 'en' => 'Europe'],
            ['cs' => 'Celý svět', 'en' => 'Worldwide'],
        ],
        // Jazyky, které se na profilech objevují nejčastěji. Zbytek doplní
        // administrátor sám nebo přijde frontou z scraperu.
        'languages' => [
            ['cs' => 'Čeština', 'en' => 'Czech'],
            ['cs' => 'Slovenština', 'en' => 'Slovak'],
            ['cs' => 'Angličtina', 'en' => 'English'],
            ['cs' => 'Němčina', 'en' => 'German'],
            ['cs' => 'Ruština', 'en' => 'Russian'],
            ['cs' => 'Ukrajinština', 'en' => 'Ukrainian'],
            ['cs' => 'Polština', 'en' => 'Polish'],
            ['cs' => 'Maďarština', 'en' => 'Hungarian'],
            ['cs' => 'Španělština', 'en' => 'Spanish'],
            ['cs' => 'Italština', 'en' => 'Italian'],
            ['cs' => 'Francouzština', 'en' => 'French'],
            ['cs' => 'Rumunština', 'en' => 'Romanian'],
            ['cs' => 'Bulharština', 'en' => 'Bulgarian'],
            ['cs' => 'Srbština', 'en' => 'Serbian'],
            ['cs' => 'Chorvatština', 'en' => 'Croatian'],
        ],
    ];

    public function run(): void
    {
        foreach (self::OPTIONS as $attribute => $options) {
            foreach ($options as $position => $label) {
                $normalized = \App\Models\ScrapeUnknownValue::normalize($label['cs']);

                // Never a second row for a value somebody already approved
                // through the queue.
                ProfileAttributeOption::firstOrCreate(
                    ['attribute' => $attribute, 'normalized' => $normalized],
                    [
                        'label' => $label,
                        'sort_order' => ($position + 1) * 10,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
