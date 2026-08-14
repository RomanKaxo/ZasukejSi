<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Roles and permissions.
 *
 * The file existed but was empty, so `db:seed --class=RoleSeeder` — which
 * deploy.sh calls — failed with "Target class does not exist". The role setup
 * lived inline in DatabaseSeeder instead; it now lives here and DatabaseSeeder
 * calls this seeder, so a deploy and a full seed produce the same result.
 *
 * Idempotent: safe to run against an existing database.
 */
class RoleSeeder extends Seeder
{
    /** Roles the application checks for. */
    public const ROLES = ['super_admin', 'admin', 'user', 'vip'];

    /** Permissions, matching the Filament Shield naming. */
    public const PERMISSIONS = [
        'view_user',
        'view_any_user',
        'create_user',
        'update_user',
        'delete_user',
        'restore_user',
        'force_delete_user',
        'view_profile',
        'view_any_profile',
        'create_profile',
        'update_profile',
        'delete_profile',
        'restore_profile',
        'force_delete_profile',
    ];

    public function run(): void
    {
        $roles = [];

        foreach (self::ROLES as $name) {
            $roles[$name] = Role::firstOrCreate(['name' => $name]);
        }

        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Spatie caches the permission map; without this the grants below can
        // be written against a stale set within the same request.
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $roles['admin']->givePermissionTo(Permission::all());

        $roles['user']->givePermissionTo([
            'view_profile',
            'create_profile',
            'update_profile',
            'delete_profile',
        ]);

        $roles['vip']->givePermissionTo([
            'view_profile',
        ]);
    }
}
