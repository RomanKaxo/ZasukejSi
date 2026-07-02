<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Livewire\ProfileList;

$component = new ProfileList();
$html = $component->render()->render();

$marker = '<!-- Pagination -->';
$pos = strpos($html, $marker);
if ($pos === false) {
    echo "Pagination marker not found in rendered HTML\n";
    exit(1);
}

$snippet = substr($html, $pos, 4000);

echo $snippet;
