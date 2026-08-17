<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Oprávnění musí mít stejné názvy, jaké se ptají politiky.
 *
 * Seeder zakládal `update_profile`, politiky se ptaly na `Update:Profile`.
 * Role `admin` tak držela sadu, kterou nikdo nekonzultoval, a editace profilu
 * v administraci byla zamítnutá každému, kdo nebyl `super_admin` — ten projde,
 * protože ho Shield pouští ještě před politikou, což je přesně důvod, proč si
 * toho nikdo nevšiml.
 */
class RolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
        $this->seed(RoleSeeder::class);
    }

    public function test_every_permission_a_policy_asks_for_exists(): void
    {
        $missing = [];

        foreach ($this->permissionsUsedByPolicies() as $name) {
            if (! Permission::where('name', $name)->exists()) {
                $missing[] = $name;
            }
        }

        $this->assertSame([], $missing, 'Chybějící oprávnění: ' . implode(', ', $missing));
    }

    public function test_an_admin_may_edit_a_profile(): void
    {
        $admin = User::factory()->create();
        $admin->syncRoles(['admin']);

        $profile = Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
        ]);

        $this->assertTrue($admin->fresh()->can('update', $profile));
        $this->assertTrue($admin->fresh()->can('Update:Profile'));
    }

    public function test_an_admin_reaches_the_edit_screen(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->syncRoles(['admin']);

        $profile = Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
        ]);

        $this->actingAs($admin->fresh())
            ->get("/admin/profiles/{$profile->id}/edit")
            ->assertSuccessful();
    }

    /** Provozovatelka smí svůj inzerát, ne administraci. */
    public function test_a_plain_user_may_edit_a_profile_but_not_users(): void
    {
        $user = User::factory()->create();
        $user->syncRoles(['user']);

        $this->assertTrue($user->fresh()->can('Update:Profile'));
        $this->assertFalse($user->fresh()->can('Update:User'));
    }

    public function test_the_roles_the_application_checks_for_all_exist(): void
    {
        foreach (RoleSeeder::ROLES as $name) {
            $this->assertTrue(Role::where('name', $name)->exists(), "Role {$name} chybí.");
        }
    }

    /** Seeder se dá pustit na existující databázi bez následků. */
    public function test_running_it_twice_changes_nothing(): void
    {
        $before = Permission::count();

        $this->seed(RoleSeeder::class);

        $this->assertSame($before, Permission::count());
    }

    /** @return array<int, string> */
    private function permissionsUsedByPolicies(): array
    {
        $names = [];

        foreach (File::glob(app_path('Policies/*.php')) as $file) {
            preg_match_all("/can\('([^']+)'\)/", File::get($file), $matches);
            $names = array_merge($names, $matches[1] ?? []);
        }

        return array_values(array_unique($names));
    }
}
