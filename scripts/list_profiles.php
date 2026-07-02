<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Profile;

$profiles = Profile::with('user')->orderBy('created_at', 'desc')->get();
echo "Profiles count: " . $profiles->count() . PHP_EOL;
foreach ($profiles as $p) {
    $display = '';
    if (is_array($p->display_name)) {
        $display = $p->display_name['cs'] ?? $p->display_name['en'] ?? reset($p->display_name);
    } else {
        $display = $p->display_name;
    }

    $isShowcase = '0';
    if (is_array($p->content) && array_key_exists('is_showcase', $p->content)) {
        $isShowcase = $p->content['is_showcase'] ? '1' : '0';
    }

    $email = $p->user?->email ?? 'no-user';
    echo sprintf("%d | %s | %s | showcase=%s\n", $p->id, $display, $email, $isShowcase);
}
