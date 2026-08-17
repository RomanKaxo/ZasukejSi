<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Roles and permissions.
 *
 * The names have to match what the policies ask for, and they did not: the
 * seeder created `update_profile` while every policy checks `Update:Profile`.
 * The `admin` role therefore held a set of permissions nothing consulted, and
 * editing a profile in the admin was refused for anyone who was not
 * `super_admin` — that role passes because Shield lets it through the gate
 * before any policy runs, which is exactly why the gap went unnoticed.
 *
 * The list is now read out of the policies themselves, so a new resource
 * cannot drift away from it.
 *
 * Idempotent: safe to run against an existing database.
 */
class RoleSeeder extends Seeder
{
    /** Roles the application checks for. */
    public const ROLES = ['super_admin', 'admin', 'user', 'vip'];

    /**
     * What a provider may do with her own advertisement.
     *
     * Deliberately narrow: everything else in the admin is off limits.
     */
    private const USER_PERMISSIONS = [
        'View:Profile',
        'Create:Profile',
        'Update:Profile',
        'Delete:Profile',
    ];

    public function run(): void
    {
        $roles = [];

        foreach (self::ROLES as $name) {
            $roles[$name] = Role::firstOrCreate(['name' => $name]);
        }

        foreach (self::permissions() as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Spatie caches the permission map; without this the grants below can
        // be written against a stale set within the same request.
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $roles['admin']->givePermissionTo(Permission::all());

        $roles['user']->givePermissionTo(
            array_values(array_intersect(self::USER_PERMISSIONS, self::permissions()))
        );

        $roles['vip']->givePermissionTo(
            array_values(array_intersect(['View:Profile'], self::permissions()))
        );

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Every permission any policy checks for.
     *
     * Read from the policy files rather than kept as a second list: two lists
     * that have to agree are how this broke in the first place.
     *
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        $names = [];

        foreach (File::glob(app_path('Policies/*.php')) as $file) {
            preg_match_all("/can\('([^']+)'\)/", File::get($file), $matches);

            foreach ($matches[1] ?? [] as $name) {
                $names[$name] = true;
            }
        }

        ksort($names);

        return array_keys($names);
    }
}
