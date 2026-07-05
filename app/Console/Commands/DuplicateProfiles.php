<?php

namespace App\Console\Commands;

use App\Models\Profile;
use Illuminate\Console\Command;

class DuplicateProfiles extends Command
{
    protected $signature = 'profiles:duplicate {count=50}';
    protected $description = 'Duplicate profiles for pagination testing';

    public function handle()
    {
        $profiles = Profile::limit((int)$this->argument('count'))->get();
        foreach ($profiles as $p) {
            // Vytvořit nového uživatele
            $user = \App\Models\User::factory()->create();
            
            $new = $p->replicate();
            $new->display_name = $p->display_name . ' (copy)';
            $new->user_id = $user->id; // Přiřadit ID nového uživatele
            $new->save();
        }
        $this->info('Profiles duplicated successfully.');
    }
}
