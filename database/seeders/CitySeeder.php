<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeds cities from the worldcities.csv file for autocomplete functionality.
     * CSV file location: database/data/worldcities.csv
     * 
     * Usage: php artisan db:seed --class=CitySeeder
     */
    public function run(): void
    {
        $csvPath = database_path('data/worldcities.csv');
        
        if (!file_exists($csvPath)) {
            $this->command->error("CSV file not found at: {$csvPath}");
            $this->command->info("Please place the worldcities.csv file in database/data/");
            return;
        }
        
        $this->command->info('🌍 Seeding cities from CSV...');
        
        // Check if cities already exist
        if (City::count() > 0) {
            $this->command->info('✓ Cities already seeded, skipping');
            return;
        }
        
        $handle = fopen($csvPath, 'r');
        
        if ($handle === false) {
            $this->command->error("Could not open CSV file");
            return;
        }
        
        // Skip header row
        $header = fgetcsv($handle);
        
        $count = 0;
        $batch = [];
        $batchSize = 1000;
        
        while (($row = fgetcsv($handle)) !== false) {
            // CSV format: "city","city_ascii","lat","lng","country","iso2","iso3","admin_name","capital","population","id"
            // Indices:      0       1           2     3      4          5      6       7            8          9          10
            
            $batch[] = [
                'name' => $row[0] ?? '',
                'name_ascii' => $row[1] ?? '',
                'lat' => !empty($row[2]) ? (float) $row[2] : null,
                'lng' => !empty($row[3]) ? (float) $row[3] : null,
                'country_code' => strtoupper($row[5] ?? ''),
                'admin_name' => !empty($row[7]) ? trim($row[7]) : null,
                'population' => !empty($row[9]) ? (int) $row[9] : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $count++;
            
            // Insert in batches for better performance
            if (count($batch) >= $batchSize) {
                City::insert($batch);
                $batch = [];
                $this->command->info("  Inserted {$count} cities...");
            }
        }
        
        // Insert remaining records
        if (!empty($batch)) {
            City::insert($batch);
        }
        
        fclose($handle);
        
        $this->command->info("✅ Seeded {$count} cities successfully!");
    }
}
