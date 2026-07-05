<?php
$html = @file_get_contents('http://127.0.0.1:8000/?locale=cs');
if ($html === false) {
    echo "FETCH_FAILED\n";
    exit(1);
}
$markers = [
    'livewire:profile-list',
    '<livewire:profile-list',
    'home-profile-card',
    'wire:id',
    'wire:loading'
];
foreach ($markers as $marker) {
    echo $marker . ': ' . (strpos($html, $marker) !== false ? 'FOUND' : 'MISSING') . "\n";
}
echo "LENGTH=" . strlen($html) . "\n";
$marker = 'home-profile-card';
$pos = strpos($html, $marker);
if ($pos !== false) {
    $start = max(0, $pos - 400);
    $snippet = substr($html, $start, 1200);
    echo "\n--- SNIPPET AROUND first home-profile-card ---\n";
    echo $snippet;
    echo "\n--- END SNIPPET ---\n";
}
$marker = 'homepage-profiles-surface';
$pos = strpos($html, $marker);
if ($pos !== false) {
    $start = max(0, $pos - 400);
    echo "\n--- SNIPPET AROUND homepage-profiles-surface ---\n";
    echo substr($html, $start, 2000);
    echo "\n--- END SNIPPET ---\n";
}
$marker = 'profile-list-cards-grid';
$pos = strpos($html, $marker);
if ($pos !== false) {
    $start = max(0, $pos - 400);
    echo "\n--- SNIPPET AROUND profile-list-cards-grid ---\n";
    echo substr($html, $start, 1800);
    echo "\n--- END SNIPPET ---\n";
}
$pos = strpos($html, 'wire:loading');
if ($pos !== false) {
    $start = max(0, $pos - 200);
    echo "\n--- SNIPPET AROUND wire:loading ---\n";
    echo substr($html, $start, 600);
    echo "\n--- END SNIPPET ---\n";
}
