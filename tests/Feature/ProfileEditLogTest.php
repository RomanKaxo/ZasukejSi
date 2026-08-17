<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\ProfileEditLog;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Who changed what on a profile.
 *
 * An admin can rewrite somebody else's advertisement, including its price list
 * and whether it is public. That used to leave no trace at all.
 */
class ProfileEditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
    }

    private function profile(): Profile
    {
        return Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'status' => 'approved',
            'is_public' => true,
            // Explicit, so „změň město na Brno" je vždycky opravdu změna —
            // továrna losuje město a občas trefila to, na které se mění.
            'city' => 'Výchozí město',
            'age' => 21,
        ]);
    }

    public function test_a_change_is_recorded_with_its_author(): void
    {
        $admin = User::factory()->create();
        $profile = $this->profile();

        $this->actingAs($admin);
        $profile->update(['city' => 'Brno']);

        $log = ProfileEditLog::where('profile_id', $profile->id)->sole();

        $this->assertSame($admin->id, $log->user_id);
        $this->assertArrayHasKey('city', $log->change_set);
        $this->assertSame('Brno', $log->change_set['city']['to']);
    }

    public function test_the_previous_value_is_kept(): void
    {
        $profile = $this->profile();
        $profile->update(['city' => 'Praha']);
        ProfileEditLog::query()->delete();

        $profile->update(['city' => 'Ostrava']);

        $log = ProfileEditLog::where('profile_id', $profile->id)->sole();

        $this->assertSame('Praha', $log->change_set['city']['from']);
        $this->assertSame('Ostrava', $log->change_set['city']['to']);
    }

    /** A change nobody is signed in for — a command, an import. */
    public function test_a_change_without_an_author_is_still_recorded(): void
    {
        $profile = $this->profile();

        $profile->update(['city' => 'Plzeň']);

        $this->assertNull(ProfileEditLog::where('profile_id', $profile->id)->sole()->user_id);
    }

    public function test_saving_without_changing_anything_writes_nothing(): void
    {
        $profile = $this->profile();

        $profile->update(['city' => $profile->city]);
        $profile->save();

        $this->assertSame(0, ProfileEditLog::where('profile_id', $profile->id)->count());
    }

    /** The page builder payload would bury every other line in the log. */
    public function test_the_content_builder_is_not_logged(): void
    {
        $profile = $this->profile();

        $profile->update(['content_blocks' => [['type' => 'heading', 'data' => ['text' => 'Ahoj']]]]);

        $this->assertSame(0, ProfileEditLog::where('profile_id', $profile->id)->count());
    }

    public function test_several_fields_at_once_are_one_entry(): void
    {
        $profile = $this->profile();

        $profile->update(['city' => 'Zlín', 'age' => 27]);

        $log = ProfileEditLog::where('profile_id', $profile->id)->sole();

        // Pořadí určuje model, ne volání — porovnává se obsah.
        $this->assertEqualsCanonicalizing(['city', 'age'], array_keys($log->change_set));
        $this->assertStringContainsString('Město', $log->fieldList());
        $this->assertStringContainsString('Věk', $log->fieldList());
    }

    public function test_deleting_the_profile_takes_its_log_with_it(): void
    {
        $profile = $this->profile();
        $profile->update(['city' => 'Jihlava']);

        $profile->forceDelete();

        $this->assertSame(0, ProfileEditLog::where('profile_id', $profile->id)->count());
    }
}
