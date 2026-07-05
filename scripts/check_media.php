<?php

use Spatie\MediaLibrary\MediaCollections\Models\Media;

foreach (Media::all() as $m) {
    echo 'ID: ' . $m->id . ' | Path: ' . $m->getPath() . ' | URL: ' . $m->getUrl() . PHP_EOL;
}
