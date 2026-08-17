<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\ScrapeItem;
use App\Models\ScrapeSource;
use App\Models\User;
use App\Services\Scraping\ScrapeItemImporter;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The columns are only worth having if every way a profile can be written
 * fills them. There are three: the Filament form, the account form and the
 * scraper's importer — and all three still write the JSON, not the columns.
 */
class ProfileFieldsPropagationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
        // Bust size and the other attribute lists come from the catalogue now,
        // and an empty catalogue means an unfillable select.
        $this->seed(\Database\Seeders\ProfileAttributeOptionSeeder::class);
    }

    private function columns(Profile $profile): object
    {
        return DB::table('profiles')
            ->where('id', $profile->id)
            ->first(['phone', 'height_cm', 'weight_kg', 'bust_size', 'nationality', 'languages', 'has_whatsapp']);
    }

    public function test_the_admin_form_fills_the_columns(): void
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create(['email' => 'test@example.com', 'gender' => 'male']);
        $admin->syncRoles(['admin', 'super_admin']);

        $profile = Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'country_code' => null,
            'local_prices' => [],
            'global_prices' => [],
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\Profiles\Pages\EditProfile::class, ['record' => $profile->getRouteKey()])
            ->fillForm([
                'content.card_height_cm' => 172,
                'content.weight_kg' => 58,
                'content.bust_size' => 'C',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $columns = $this->columns($profile);

        $this->assertSame(172, (int) $columns->height_cm);
        $this->assertSame(58, (int) $columns->weight_kg);
        $this->assertSame('C', $columns->bust_size);
    }

    public function test_the_account_form_fills_the_columns(): void
    {
        $user = User::factory()->create(['gender' => 'female']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        // The account form writes the `content` array wholesale, which is
        // exactly the path the sync has to cover.
        $profile->update([
            'content' => array_merge((array) $profile->content, [
                'card_height_cm' => 165,
                'weight_kg' => 51,
                'has_whatsapp' => true,
            ]),
        ]);

        $columns = $this->columns($profile);

        $this->assertSame(165, (int) $columns->height_cm);
        $this->assertSame(51, (int) $columns->weight_kg);
        $this->assertTrue((bool) $columns->has_whatsapp);
    }

    public function test_the_scraper_import_fills_the_columns(): void
    {
        $source = ScrapeSource::create([
            'name' => 'Test',
            'slug' => 'test',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'settings' => [],
        ]);

        $item = ScrapeItem::create([
            'scrape_source_id' => $source->id,
            'source_url' => 'https://example.test/1',
            'external_id' => '1',
            'content_hash' => 'a',
            'normalized' => [
                'display_name' => 'Jana',
                'city' => 'Praha',
                'card_height_cm' => 168,
                'weight_kg' => 54,
                'bust_size' => 'B',
                'nationality' => 'CZ',
                'languages' => 'čeština',
            ],
            'images' => [],
            'status' => ScrapeItem::STATUS_APPROVED,
        ]);

        $profile = app(ScrapeItemImporter::class)->import($item, false);

        $columns = $this->columns($profile);

        $this->assertSame(168, (int) $columns->height_cm);
        $this->assertSame(54, (int) $columns->weight_kg);
        $this->assertSame('B', $columns->bust_size);
        $this->assertSame('cz', strtolower((string) $columns->nationality));
        $this->assertSame('čeština', $columns->languages);
    }

    /**
     * The phone has a field of its own now, and the contact list the public
     * profile is built from has to follow it.
     */
    public function test_typing_a_phone_into_the_field_updates_the_contact_list(): void
    {
        $profile = Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'contacts' => [['type' => 'email', 'value' => 'jana@example.com']],
        ]);

        $profile->update(['phone' => '777 123 456']);

        $contacts = $profile->fresh()->contacts;

        $this->assertContains(
            ['type' => 'phone', 'value' => '777 123 456'],
            $contacts
        );
        // The e-mail keeps its place.
        $this->assertContains(['type' => 'email', 'value' => 'jana@example.com'], $contacts);
    }

    public function test_an_existing_phone_entry_is_edited_rather_than_duplicated(): void
    {
        $profile = Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'contacts' => [
                ['type' => 'phone', 'value' => '111 111 111'],
                ['type' => 'email', 'value' => 'jana@example.com'],
            ],
        ]);

        $profile->update(['phone' => '222 222 222']);

        $contacts = $profile->fresh()->contacts;

        $this->assertCount(2, $contacts);
        $this->assertSame('222 222 222', $contacts[0]['value']);
    }

    public function test_clearing_the_phone_removes_it_from_the_contacts(): void
    {
        $profile = Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'contacts' => [['type' => 'phone', 'value' => '777 123 456']],
        ]);

        $profile->update(['phone' => null]);

        $this->assertSame([], $profile->fresh()->contacts);
    }

    /**
     * The cards read these values out of `content`, so the sync writing back
     * into it is what keeps the site correct after a column is edited.
     */
    public function test_the_public_card_still_shows_the_value_after_a_column_edit(): void
    {
        $user = User::factory()->create(['gender' => 'female']);
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'is_public' => true,
            'content' => ['card_height_cm' => 160],
        ]);

        $profile->update(['height_cm' => 176]);

        $this->get(route('profiles.show', $profile))
            ->assertSuccessful()
            ->assertSee('176');
    }
}
