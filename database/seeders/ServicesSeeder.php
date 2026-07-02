<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => ['cs' => 'Pozice 69', 'en' => 'Position 69'],
                'description' => ['cs' => 'Klasická pozice 69', 'en' => 'Classic 69 position'],
                'sort_order' => 1
            ],
            [
                'name' => ['cs' => 'Vaginální sex', 'en' => 'Vaginal Sex'],
                'description' => null,
                'sort_order' => 2
            ],
            [
                'name' => ['cs' => 'Výstřik na obličej', 'en' => 'Facial'],
                'description' => null,
                'sort_order' => 3
            ],
            [
                'name' => ['cs' => 'Výstřik do pusy', 'en' => 'Cumshot in Mouth'],
                'description' => null,
                'sort_order' => 4
            ],
            [
                'name' => ['cs' => 'Výstřik na tělo', 'en' => 'Body Cumshot'],
                'description' => null,
                'sort_order' => 5
            ],
            [
                'name' => ['cs' => 'Lízání', 'en' => 'Oral'],
                'description' => null,
                'sort_order' => 6
            ],
            [
                'name' => ['cs' => 'Nadávání', 'en' => 'Dirty Talk'],
                'description' => null,
                'sort_order' => 7
            ],
            [
                'name' => ['cs' => 'Erotická masáž', 'en' => 'Erotic Massage'],
                'description' => null,
                'sort_order' => 8
            ],
            [
                'name' => ['cs' => 'Facesitting', 'en' => 'Facesitting'],
                'description' => null,
                'sort_order' => 9
            ],
            [
                'name' => ['cs' => 'Prstění', 'en' => 'Fingering'],
                'description' => null,
                'sort_order' => 10
            ],
        ];

        foreach ($services as $service) {
            Service::create([
                'name' => $service['name'],
                'description' => $service['description'],
                'sort_order' => $service['sort_order'],
                'is_active' => true,
            ]);
        }
    }
}

