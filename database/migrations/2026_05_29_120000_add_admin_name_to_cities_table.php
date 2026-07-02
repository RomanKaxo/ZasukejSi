<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->string('admin_name')->nullable()->after('country_code');
            $table->index(['country_code', 'admin_name']);
        });

        $this->backfillAdminNames();
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropIndex(['country_code', 'admin_name']);
            $table->dropColumn('admin_name');
        });
    }

    private function backfillAdminNames(): void
    {
        $csvPath = database_path('data/worldcities.csv');

        if (!is_file($csvPath)) {
            return;
        }

        $handle = fopen($csvPath, 'r');

        if ($handle === false) {
            return;
        }

        fgetcsv($handle);

        $regionMap = [];
        $asciiRegionMap = [];

        while (($row = fgetcsv($handle)) !== false) {
            $countryCode = strtoupper($row[5] ?? '');
            $name = trim($row[0] ?? '');
            $nameAscii = trim($row[1] ?? '');
            $adminName = trim($row[7] ?? '');

            if ($countryCode === '' || $name === '' || $adminName === '') {
                continue;
            }

            $regionMap[$countryCode . '|' . mb_strtolower($name)] = $adminName;

            if ($nameAscii !== '') {
                $asciiRegionMap[$countryCode . '|' . mb_strtolower($nameAscii)] = $adminName;
            }
        }

        fclose($handle);

        DB::table('cities')
            ->select(['id', 'country_code', 'name', 'name_ascii'])
            ->orderBy('id')
            ->chunkById(1000, function ($cities) use ($regionMap, $asciiRegionMap) {
                $updates = [];

                foreach ($cities as $city) {
                    $nameKey = $city->country_code . '|' . mb_strtolower($city->name);
                    $asciiKey = $city->country_code . '|' . mb_strtolower($city->name_ascii);
                    $adminName = $regionMap[$nameKey] ?? $asciiRegionMap[$asciiKey] ?? null;

                    if ($adminName === null) {
                        continue;
                    }

                    $updates[] = [
                        'id' => $city->id,
                        'admin_name' => $adminName,
                    ];
                }

                if ($updates !== []) {
                    DB::table('cities')->upsert($updates, ['id'], ['admin_name']);
                }
            });
    }
};