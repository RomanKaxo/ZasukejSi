<?php

require __DIR__ . '/../bootstrap/app.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$emails = [
    'test@example.com',
    'woman@example.com',
    'user@example.com',
    'premium-muz@example.com',
    'premium-zena@example.com',
];

$password = \Illuminate\Support\Facades\Hash::make('password');

foreach ($emails as $email) {
    $user = \App\Models\User::where('email', $email)->first();
    if ($user) {
        $user->password = $password;
        $user->save();
        echo "Heslo pro $email bylo úspěšně změněno." . PHP_EOL;
    } else {
        echo "Uživatel $email nenalezen." . PHP_EOL;
    }
}
