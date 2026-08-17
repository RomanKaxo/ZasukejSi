<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Height, weight, bust, nationality, languages and the messenger flags lived
 * inside the `content` blob; the phone lived inside `contacts`. Nothing could
 * be searched, filtered, sorted or joined on any of them.
 */
class ProfileStructuredFieldsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
    }

    private function profile(array $attributes = []): Profile
    {
        return Profile::factory()->create(array_merge([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
        ], $attributes));
    }

    public function test_writing_the_json_fills_the_columns(): void
    {
        // The account form and the importer both still write `content`.
        $profile = $this->profile([
            'content' => [
                'card_height_cm' => 170,
                'weight_kg' => 55,
                'bust_size' => 'C',
                'nationality' => 'CZ',
                'languages' => 'čeština, angličtina',
                'has_whatsapp' => true,
            ],
            'contacts' => [
                ['type' => 'email', 'value' => 'jana@example.com'],
                ['type' => 'phone', 'value' => '+420 777 123 456'],
            ],
        ]);

        $row = DB::table('profiles')->where('id', $profile->id)->first();

        $this->assertSame(170, (int) $row->height_cm);
        $this->assertSame(55, (int) $row->weight_kg);
        $this->assertSame('C', $row->bust_size);
        $this->assertSame('cz', strtolower((string) $row->nationality));
        $this->assertSame('čeština, angličtina', $row->languages);
        $this->assertTrue((bool) $row->has_whatsapp);
        $this->assertSame('+420 777 123 456', $row->phone);
    }

    public function test_writing_a_column_updates_the_json(): void
    {
        $profile = $this->profile(['content' => ['card_height_cm' => 160]]);

        $profile->update(['height_cm' => 175]);

        // Views that still read `content` must not be left saying 160.
        $this->assertSame(175, (int) $profile->fresh()->content['card_height_cm']);
        $this->assertSame(175, $profile->fresh()->height);
    }

    public function test_the_accessors_keep_working(): void
    {
        $profile = $this->profile([
            'content' => [
                'card_height_cm' => 168,
                'weight_kg' => 52,
                'bust_size' => 'B',
                'nationality' => 'SK',
                'languages' => 'slovenština',
                'has_telegram' => true,
            ],
        ])->fresh();

        $this->assertSame(168, $profile->height);
        $this->assertSame(52, $profile->weight);
        $this->assertSame('B', $profile->bust_size);
        $this->assertSame('sk', $profile->nationality);
        $this->assertSame('slovenština', $profile->languages);
        $this->assertTrue($profile->has_telegram);
        $this->assertFalse($profile->has_whatsapp);
    }

    public function test_a_profile_with_no_values_stays_empty(): void
    {
        $profile = $this->profile(['content' => [], 'contacts' => []])->fresh();

        // "Not filled in" must not become a zero.
        $this->assertNull($profile->height);
        $this->assertNull($profile->weight);
        $this->assertNull($profile->bust_size);
        $this->assertNull($profile->phone);
    }

    public function test_the_first_phone_wins_and_other_contacts_are_ignored(): void
    {
        $profile = $this->profile([
            'contacts' => [
                ['type' => 'email', 'value' => 'jana@example.com'],
                ['type' => 'phone', 'value' => '777 111 222'],
                ['type' => 'phone', 'value' => '777 999 888'],
            ],
        ])->fresh();

        $this->assertSame('777 111 222', $profile->phone);
    }

    /**
     * The point of the columns: these queries were impossible before.
     */
    public function test_the_columns_can_be_searched_and_filtered(): void
    {
        $this->profile(['contacts' => [['type' => 'phone', 'value' => '777123456']]]);
        $this->profile(['content' => ['card_height_cm' => 180]]);
        $this->profile(['content' => ['card_height_cm' => 160]]);

        $this->assertSame(1, Profile::where('phone', 'like', '%123456%')->count());
        $this->assertSame(1, Profile::where('height_cm', '>=', 175)->count());
        $this->assertSame(
            [160, 180],
            Profile::whereNotNull('height_cm')->orderBy('height_cm')->pluck('height_cm')->all()
        );
    }

    public function test_an_edit_that_touches_neither_side_leaves_both_alone(): void
    {
        $profile = $this->profile(['content' => ['card_height_cm' => 165]]);

        $profile->update(['city' => 'Brno']);

        $fresh = $profile->fresh();

        $this->assertSame(165, (int) $fresh->height_cm);
        $this->assertSame(165, (int) $fresh->content['card_height_cm']);
    }
}
