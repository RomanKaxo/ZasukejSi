<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$cities = DB::table('cities')
    ->where('country_code', 'cz')
    ->limit(20)
    ->get();

echo "ID | Name | Admin Name (Region)" . PHP_EOL;
echo "-----------------------------------" . PHP_EOL;
foreach ($cities as $city) {
    echo sprintf("%d | %s | %s\n", $city->id, $city->name, $city->admin_name ?? 'NULL');
}
