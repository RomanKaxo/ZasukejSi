<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Profile;

$profiles = Profile::where('content->is_showcase', true)->get();

echo "Checking showcase profiles for thumb conversion:\n";
foreach ($profiles as $profile) {
    echo "Profile: " . $profile->display_name . " (ID: " . $profile->id . ")\n";
    foreach ($profile->getMedia('profile-images') as $m) {
        echo " - File: " . $m->file_name . "\n";
        echo " - Has thumb: " . ($m->hasGeneratedConversion('thumb') ? 'Yes' : 'No') . "\n";
    }
    echo "\n";
}
