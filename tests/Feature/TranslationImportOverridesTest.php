<?php

namespace Tests\Feature;

use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Co se stane s uloženým překladem, když se opraví soubor.
 *
 * Slib importu zní: co administrátor přepsal, se nepřepíše zpátky. Otázka „je
 * to přepsané?" se ale ptá porovnáním uložené hodnoty proti výchozí ze
 * souboru — takže záleží na tom, kdy se ta výchozí aktualizuje.
 *
 * Dělo se to ve špatném pořadí: nejdřív se výchozí hodnota nastavila na novou
 * ze souboru, a teprve pak se ptalo. Po opravě překlepu tedy vyšlo, že se
 * hodnoty liší, a nedotčený řádek se od té chvíle tvářil jako něčí vědomá
 * úprava. Navždy.
 *
 * Přesně to potkalo „číst článek": soubor to tak měl v červenci, import to
 * uložil, v srpnu se soubor opravil na „Číst článek" — a web dál psal malé č.
 */
class TranslationImportOverridesTest extends TestCase
{
    use RefreshDatabase;

    /** Řádek, který vznikl importem starší verze souboru. */
    private function importedRow(string $value): Translation
    {
        return Translation::create([
            'locale' => 'cs',
            'group' => 'blogs',
            'key' => 'read_article',
            'value' => $value,
            'default_value' => $value,
        ]);
    }

    public function test_a_corrected_file_value_reaches_an_untouched_row(): void
    {
        // Soubor kdysi říkal malé č a import to uložil.
        $row = $this->importedRow('číst článek');

        // Soubor je dnes opravený; `lang/cs/blogs.php` má „Číst článek".
        $this->artisan('translations:import')->assertSuccessful();

        $row->refresh();

        $this->assertSame('Číst článek', $row->value, 'Oprava v souboru se musí projevit.');
        $this->assertSame('Číst článek', $row->default_value);
    }

    /** A zároveň: co někdo opravdu přepsal, se nepřepíše zpátky. */
    public function test_a_real_admin_edit_survives_the_import(): void
    {
        $row = Translation::create([
            'locale' => 'cs',
            'group' => 'blogs',
            'key' => 'read_article',
            // Výchozí odpovídá souboru, hodnota je něčí vědomá úprava.
            'value' => 'Přečíst si to',
            'default_value' => 'Číst článek',
        ]);

        $this->artisan('translations:import')->assertSuccessful();

        $this->assertSame('Přečíst si to', $row->refresh()->value);
    }

    /** S --force ustoupí i vědomá úprava — o to jde. */
    public function test_force_discards_an_admin_edit(): void
    {
        $row = Translation::create([
            'locale' => 'cs',
            'group' => 'blogs',
            'key' => 'read_article',
            'value' => 'Přečíst si to',
            'default_value' => 'Číst článek',
        ]);

        $this->artisan('translations:import', ['--force' => true])->assertSuccessful();

        $this->assertSame('Číst článek', $row->refresh()->value);
    }

    /** Po importu musí web ukazovat to opravené, ne to z mezipaměti. */
    public function test_the_site_shows_the_corrected_text(): void
    {
        $this->importedRow('číst článek');

        $this->artisan('translations:import')->assertSuccessful();

        Translation::flushAll();

        $this->assertSame('Číst článek', __('blogs.read_article'));
    }
}
