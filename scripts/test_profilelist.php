<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Livewire\ProfileList;

$component = new ProfileList();
// ensure default perPage matches what UI used (e.g., 8)
// $component->perPage = 8;
$paginator = $component->profiles();
echo "Returned items: " . count($paginator->items()) . "\n";
echo "Current page: " . $paginator->currentPage() . "\n";
echo "Last page: " . $paginator->lastPage() . "\n";
echo "Total items (virtual): " . $paginator->total() . "\n";
foreach ($paginator->items() as $item) {
    $display = is_array($item->display_name) ? ($item->display_name['cs'] ?? $item->display_name['en'] ?? reset($item->display_name)) : $item->display_name;
    echo sprintf("%d | %s | user:%s\n", $item->id, $display, $item->user_id);
}
