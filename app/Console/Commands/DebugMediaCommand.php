<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DebugMediaCommand extends Command
{
    protected $signature = 'debug:media';
    protected $description = 'Debug media paths and URLs';

    public function handle()
    {
        foreach (Media::all() as $m) {
            $this->info('ID: ' . $m->id . ' | Path: ' . $m->getPath() . ' | URL: ' . $m->getUrl());
        }
    }
}
