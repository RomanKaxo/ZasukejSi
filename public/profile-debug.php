<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();
$app->instance('request', $request);
$kernel->bootstrap();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .profile { margin: 20px 0; padding: 15px; background: white; border: 2px solid #333; }
        img { border: 3px solid red; max-width: 200px; }
        pre { background: #eee; padding: 10px; overflow-x: auto; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Profile Card Debug</h1>
    
    <?php
    $names = ['Alexandra', 'Tamara', 'Jana', 'Angela', 'Lucie'];
    
    foreach ($names as $name) {
        $profile = App\Models\Profile::with('media')->where('display_name->en', $name)->first();
        
        if ($profile) {
            echo "<div class='profile'>";
            echo "<h2>{$name} (ID: {$profile->id})</h2>";
            
            $images = $profile->getAllImages();
            $count = $images->count();
            
            echo "<p><strong>getAllImages()->count():</strong> {$count}</p>";
            
            if ($count > 0) {
                $firstImage = $images->first();
                $url = $firstImage->getUrl();
                $path = $firstImage->getPath();
                $exists = file_exists($path);
                
                echo "<p><strong>File name:</strong> {$firstImage->file_name}</p>";
                echo "<p><strong>URL:</strong> <a href='{$url}' target='_blank'>{$url}</a></p>";
                echo "<p><strong>Path:</strong> {$path}</p>";
                echo "<p><strong>File exists:</strong> " . ($exists ? "<span class='success'>YES</span>" : "<span class='error'>NO</span>") . "</p>";
                
                echo "<p><strong>Rendered Image:</strong></p>";
                echo "<img src='{$url}' alt='{$name}'>";
                
                echo "<p><strong>Profile Card Logic:</strong></p>";
                echo "<pre>";
                $shouldBlur = false;
                $hasMultiple = $profile->hasMultipleImages();
                $firstUrl = $profile->getFirstImageUrl();
                
                echo "shouldBlur: " . ($shouldBlur ? 'true' : 'false') . "\n";
                echo "hasMultipleImages: " . ($hasMultiple ? 'true' : 'false') . "\n";
                echo "getFirstImageUrl: {$firstUrl}\n";
                
                if ($count > 0) {
                    if ($hasMultiple) {
                        echo "\n→ Would render SWIPER\n";
                    } else {
                        echo "\n→ Would render SINGLE IMAGE with src=\"{$firstUrl}\"\n";
                    }
                } else {
                    echo "\n→ Would render PLACEHOLDER\n";
                }
                echo "</pre>";
            } else {
                echo "<p class='error'>No images found!</p>";
            }
            
            echo "</div>";
        }
    }
    ?>
    
    <div style="margin-top: 40px; padding: 15px; background: #ffffcc; border: 2px solid orange;">
        <h3>Instructions</h3>
        <ol>
            <li>Pokud NEVIDÍŠ obrázky výše, otevři DevTools (F12) → Network tab</li>
            <li>Refresh stránku</li>
            <li>Najdi model*.png requesty a podívej se na jejich status</li>
            <li>Pokud je 404, problém je s routingem nebo symlinkem</li>
            <li>Pokud je ERR_BLOCKED, problém je s ad-blockerem</li>
            <li>Klikni na zelené linky a zkus otevřít obrázky přímo</li>
        </ol>
    </div>
</body>
</html>
