#!/bin/bash

# Quick script to test a page URL and see the actual error

echo "Testing page access..."
echo "====================="

# Try to access a page via artisan
php artisan tinker --execute="
try {
    \$request = Illuminate\Http\Request::create('/admin', 'GET');
    \$response = app()->handle(\$request);
    echo 'Status: ' . \$response->getStatusCode() . PHP_EOL;
} catch (Exception \$e) {
    echo 'ERROR: ' . \$e->getMessage() . PHP_EOL;
    echo 'File: ' . \$e->getFile() . ':' . \$e->getLine() . PHP_EOL;
    echo PHP_EOL;
    echo 'Stack trace (first 5 lines):' . PHP_EOL;
    \$trace = explode(PHP_EOL, \$e->getTraceAsString());
    foreach (array_slice(\$trace, 0, 5) as \$line) {
        echo \$line . PHP_EOL;
    }
}
"

echo ""
echo "Checking last 20 lines of production log..."
tail -50 storage/logs/laravel.log | grep -A 10 "production.ERROR" || echo "No recent production errors"
