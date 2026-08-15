<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Reset account passwords in a non-production environment.
 *
 * Seeded accounts are split between 'password' and the admin's hardcoded
 * 'admin123', which leaves nobody sure how to sign in to a fresh staging
 * database. This sets them all to one known value.
 */
class SetUserPasswords extends Command
{
    protected $signature = 'users:set-password
        {password=password : The password to set}
        {--email= : Limit to a single account}
        {--force : Required to run when the environment is production}';

    protected $description = 'Set every user password to a known value (development and staging only)';

    public function handle(): int
    {
        $password = (string) $this->argument('password');

        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Refusing to run in production. Pass --force if that is really what you want.');

            return self::FAILURE;
        }

        $query = User::query();

        if ($email = $this->option('email')) {
            $query->where('email', $email);
        }

        $count = (clone $query)->count();

        if ($count === 0) {
            $this->warn('No matching accounts.');

            return self::SUCCESS;
        }

        // One statement rather than a save() per user: the hash is identical
        // for every row, and there is no model event worth firing here.
        $query->update(['password' => Hash::make($password)]);

        $this->info("Password set to '{$password}' for {$count} account(s).");

        if ($email === null) {
            $admin = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first();

            if ($admin) {
                $this->line("Admin sign-in: {$admin->email}");
            }
        }

        return self::SUCCESS;
    }
}
