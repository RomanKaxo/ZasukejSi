<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$p = \App\Models\Profile::select(['id', 'display_name'])->first();
echo 'Content: ' . ($p->content ? 'set' : 'null') . PHP_EOL;
$p2 = \App\Models\Profile::first();
echo 'Content full: ' . ($p2->content ? 'set' : 'null') . PHP_EOL;
