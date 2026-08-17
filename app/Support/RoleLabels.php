<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * How a role's name is written where a person reads it.
 *
 * Roles are stored as slugs — `super_admin` — because that is what the code
 * checks against, and the admin was showing that slug raw in some places and
 * as „Super Admin" in others. Neither is how Czech is written.
 *
 * The stored name never changes; only its label does.
 */
class RoleLabels
{
    /** Roles whose label is not just the slug tidied up. */
    private const OVERRIDES = [
        'super_admin' => 'Super admin',
        'admin' => 'Administrátor',
        'user' => 'Uživatel',
        'vip' => 'VIP',
    ];

    public static function for(?string $name): string
    {
        $name = trim((string) $name);

        if ($name === '') {
            return '';
        }

        if (isset(self::OVERRIDES[$name])) {
            return self::OVERRIDES[$name];
        }

        // Sentence case, not title case: „Správce obsahu", ne „Správce Obsahu".
        return Str::ucfirst(str_replace(['_', '-'], ' ', $name));
    }

    /**
     * Labels for a select, keyed by the stored name.
     *
     * @param  iterable<object|string>  $roles
     * @return array<string, string>
     */
    public static function options(iterable $roles): array
    {
        $options = [];

        foreach ($roles as $role) {
            $name = is_string($role) ? $role : ($role->name ?? '');

            if ($name !== '') {
                $options[$name] = self::for($name);
            }
        }

        return $options;
    }
}
