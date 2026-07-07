<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Profile;

$showcaseQuery = Profile::with(['user:id,name', 'media'])
    ->where('content->is_showcase', true)
    ->get();

// Simulate the logic in ProfileList.php
$result = collect();
while ($result->count() < 10) {
    $result = $result->concat($showcaseQuery);
}

// Get the 6th item (which is a repeat)
$profile = $result->get(5);

echo "Checking profile: " . $profile->display_name . " (ID: " . $profile->id . ")\n";
echo "Media count: " . $profile->getMedia('profile-images')->count() . "\n";
foreach ($profile->getMedia('profile-images') as $m) {
    echo " - " . $m->file_name . "\n";
}
