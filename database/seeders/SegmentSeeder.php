<?php

namespace Database\Seeders;

use App\Models\Segment;
use Illuminate\Database\Seeder;

class SegmentSeeder extends Seeder
{
    public function run(): void
    {
        $segments = [
            ['slug' => 'nova', 'name' => ['cs' => 'Nová', 'en' => 'New'], 'color' => '#5C2D62', 'sort_order' => 1],
            ['slug' => 'overena', 'name' => ['cs' => 'Ověřená', 'en' => 'Verified'], 'color' => '#00B80F', 'sort_order' => 2],
            ['slug' => 'top-lokalita', 'name' => ['cs' => 'Top lokalita', 'en' => 'Top location'], 'color' => '#DD3888', 'sort_order' => 3],
        ];

        foreach ($segments as $segment) {
            Segment::firstOrCreate(
                ['slug' => $segment['slug']],
                [
                    'name' => $segment['name'],
                    'color' => $segment['color'],
                    'sort_order' => $segment['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
