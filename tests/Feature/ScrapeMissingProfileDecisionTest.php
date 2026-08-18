<?php

namespace Tests\Feature;

use App\Filament\Resources\ScrapeItems\Pages\ListScrapeItems;
use App\Models\Profile;
use App\Models\ScrapeItem;
use App\Models\ScrapeSource;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Rozhodnutí o profilu, který na zdroji zmizel.
 *
 * Tohle je to podstatné celé funkce: scraper umí poznat, že inzerát zmizel,
 * ale odstranit profil smí jenom člověk. Zmizení ze zdroje má několik nevinných
 * vysvětlení a jedno špatné a rozeznat je od sebe je úsudek o inzerátu
 * skutečného člověka.
 *
 * Tlačítka se tu proto opravdu mačkají — vykreslení stránky by nechytlo, že
 * akce nedělá nic nebo dělá něco jiného.
 */
class ScrapeMissingProfileDecisionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
        $this->seed(RoleSeeder::class);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $admin->syncRoles(['admin', 'super_admin']);

        return $admin->fresh();
    }

    /** Položka, u které kontrola potvrdila, že na zdroji už není. */
    private function vanished(): ScrapeItem
    {
        $source = ScrapeSource::create([
            'name' => 'Příklad',
            'slug' => 'priklad',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
        ]);

        $profile = Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'status' => 'approved',
        ]);

        return ScrapeItem::create([
            'scrape_source_id' => $source->id,
            'source_url' => 'https://example.test/p/1',
            'external_id' => '1',
            'status' => ScrapeItem::STATUS_IMPORTED,
            'imported_profile_id' => $profile->id,
            'imported_at' => now()->subMonth(),
            'missing_since' => now()->subDays(3),
            'missing_checks' => 3,
        ]);
    }

    public function test_hiding_the_profile_archives_it_and_closes_the_question(): void
    {
        $item = $this->vanished();
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ListScrapeItems::class)
            // Výpis se otevírá na frontě ke kontrole; tyhle položky jsou
            // importované, takže výchozí filtr by je schoval.
            ->removeTableFilter('status')
            ->callTableAction('archiveProfile', $item)
            ->assertHasNoTableActionErrors();

        $item->refresh();

        $this->assertSame('archived', $item->profile->status);
        $this->assertSame(ScrapeItem::MISSING_REMOVED, $item->missing_resolution);
        $this->assertSame($admin->id, $item->missing_resolved_by);
        $this->assertFalse($item->isAwaitingRemovalDecision());
    }

    /** „Skrýt" znamená skrýt. Profil se nesmí smazat ani teď. */
    public function test_hiding_does_not_delete_anything(): void
    {
        $item = $this->vanished();
        $profileId = $item->imported_profile_id;

        Livewire::actingAs($this->admin())
            ->test(ListScrapeItems::class)
            // Výpis se otevírá na frontě ke kontrole; tyhle položky jsou
            // importované, takže výchozí filtr by je schoval.
            ->removeTableFilter('status')
            ->callTableAction('archiveProfile', $item);

        $this->assertNotNull(Profile::find($profileId));
        $this->assertNotNull(ScrapeItem::find($item->id));
    }

    public function test_keeping_the_profile_leaves_it_published(): void
    {
        $item = $this->vanished();

        Livewire::actingAs($this->admin())
            ->test(ListScrapeItems::class)
            // Výpis se otevírá na frontě ke kontrole; tyhle položky jsou
            // importované, takže výchozí filtr by je schoval.
            ->removeTableFilter('status')
            ->callTableAction('keepProfile', $item)
            ->assertHasNoTableActionErrors();

        $item->refresh();

        $this->assertSame('approved', $item->profile->status);
        $this->assertSame(ScrapeItem::MISSING_KEPT, $item->missing_resolution);
        $this->assertFalse($item->isAwaitingRemovalDecision());
    }

    /** Rozhodovat se dá jen o tom, co na rozhodnutí čeká. */
    public function test_the_decision_is_not_offered_on_an_ordinary_item(): void
    {
        $item = $this->vanished();
        $item->forceFill(['missing_since' => null, 'missing_checks' => 0])->save();

        Livewire::actingAs($this->admin())
            ->test(ListScrapeItems::class)
            // Výpis se otevírá na frontě ke kontrole; tyhle položky jsou
            // importované, takže výchozí filtr by je schoval.
            ->removeTableFilter('status')
            ->assertTableActionHidden('archiveProfile', $item)
            ->assertTableActionHidden('keepProfile', $item);
    }

    /** Ve výpisu jde na frontu k rozhodnutí filtrovat. */
    public function test_the_queue_can_be_filtered(): void
    {
        $waiting = $this->vanished();

        $decided = ScrapeItem::create([
            'scrape_source_id' => $waiting->scrape_source_id,
            'source_url' => 'https://example.test/p/2',
            'external_id' => '2',
            'status' => ScrapeItem::STATUS_IMPORTED,
            'imported_profile_id' => $waiting->imported_profile_id,
            'missing_since' => now()->subDays(9),
            'missing_resolution' => ScrapeItem::MISSING_KEPT,
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListScrapeItems::class)
            // Výpis se otevírá na frontě ke kontrole; tyhle položky jsou
            // importované, takže výchozí filtr by je schoval.
            ->removeTableFilter('status')
            ->filterTable('missing_at_source')
            ->assertCanSeeTableRecords([$waiting])
            ->assertCanNotSeeTableRecords([$decided]);
    }
}
