<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$users = User::orderBy('created_at', 'desc')->get();
echo "Total users: " . $users->count() . PHP_EOL;
echo "ID | Name | Email | Gender" . PHP_EOL;
echo "-----------------------------------" . PHP_EOL;
foreach ($users as $u) {
    echo sprintf("%d | %s | %s | %s\n", $u->id, $u->name, $u->email, $u->gender ?? 'NULL');
}
