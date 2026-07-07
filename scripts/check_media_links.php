<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Profile;

$profiles = Profile::where('content->is_showcase', true)->get();

echo "Checking showcase profiles for media:\n";
foreach ($profiles as $profile) {
    echo "Profile: " . $profile->display_name . " (ID: " . $profile->id . ")\n";
    $media = $profile->getMedia('profile-images');
    echo "Media count: " . $media->count() . "\n";
    foreach ($media as $m) {
        echo " - " . $m->file_name . "\n";
    }
    echo "\n";
}
