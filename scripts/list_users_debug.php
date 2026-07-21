<?php

require __DIR__ . '/../bootstrap/app.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = \App\Models\User::all();

foreach ($users as $user) {
    echo "Email: " . $user->email . PHP_EOL;
    echo "Name: " . $user->name . PHP_EOL;
    echo "Password Hash: " . $user->password . PHP_EOL;
    echo "Roles: " . implode(', ', $user->getRoleNames()->toArray()) . PHP_EOL;
    echo "-------------------" . PHP_EOL;
}
