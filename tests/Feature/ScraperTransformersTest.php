<?php

namespace Tests\Feature;

use App\Services\Scraping\Transformers;
use Tests\TestCase;

/**
 * Two defects a dry run against eurogirlsescort.cz brought out.
 *
 * The site sprinkles runs of zero-width characters through its profile texts,
 * and its services table shares markup with the opening-hours and price
 * tables — so one selector returned services, weekday names and "12 Hodiny"
 * side by side.
 */
class ScraperTransformersTest extends TestCase
{
    private Transformers $transformers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transformers = new Transformers;
    }

    public function test_invisible_characters_are_removed(): void
    {
        // Zero-width space, ZWNJ, ZWJ and word joiner, exactly as the site
        // emits them at the end of every description.
        $text = "Hello darling.\u{200B}\u{200C}\u{200D}\u{2060}\u{200B}\u{200C}";

        $this->assertSame(
            'Hello darling.',
            $this->transformers->apply($text, ['strip_invisible'])
        );
    }

    public function test_invisible_characters_inside_a_word_are_removed_too(): void
    {
        $this->assertSame(
            'Isabel',
            $this->transformers->apply("Isa\u{200B}bel", ['strip_invisible'])
        );
    }

    public function test_a_text_without_them_is_left_alone(): void
    {
        $this->assertSame(
            'Běžný popis s diakritikou.',
            $this->transformers->apply('Běžný popis s diakritikou.', ['strip_invisible'])
        );
    }

    public function test_reject_drops_matching_entries_from_a_list(): void
    {
        $scraped = ['Úterý', 'Středa', '2 Hodiny', 'Lízání', '24 Hodiny', 'Anál'];

        $services = $this->transformers->apply($scraped, [
            ['reject', '/^(Pondělí|Úterý|Středa|Čtvrtek|Pátek|Sobota|Neděle)$/ui'],
            ['reject', '/^\d+\s*(Hodin|Hodiny|Hodina|hod)\.?$/ui'],
        ]);

        $this->assertSame(['Lízání', 'Anál'], $services);
    }

    public function test_reject_nulls_a_scalar_that_matches(): void
    {
        $this->assertNull($this->transformers->apply('Neděle', [['reject', '/^Neděle$/u']]));
        $this->assertSame('Anál', $this->transformers->apply('Anál', [['reject', '/^Neděle$/u']]));
    }

    public function test_an_empty_pattern_rejects_nothing(): void
    {
        $values = ['a', 'b'];

        $this->assertSame($values, $this->transformers->apply($values, [['reject', '']]));
    }

    /**
     * A broken pattern must not take the run down — the reviewer gets the
     * unfiltered list, which is what they had before the transform existed.
     */
    public function test_an_invalid_pattern_leaves_the_list_untouched(): void
    {
        $values = ['a', 'b'];

        $this->assertSame($values, @$this->transformers->apply($values, [['reject', '/[unclosed/']]));
    }

    /**
     * The description came out as "Isabel, nezávislý Naposledy online:
     * 12.8.2026 Hello darling…" — the page header, not the profile text.
     */
    public function test_the_profile_header_is_stripped_from_the_description(): void
    {
        $raw = "Isabel, nezávislý Naposledy online: 12.8.2026 Hello darling, I'm Isabel.\u{200B}\u{200C}";

        $about = $this->transformers->apply($raw, [
            'collapse_whitespace',
            'strip_invisible',
            ['regex', '/^(?:.*?Naposledy online:\s*\S+\s+)?(.*)$/u'],
            'trim',
        ]);

        $this->assertSame("Hello darling, I'm Isabel.", $about);
    }

    public function test_a_description_without_the_header_survives_unchanged(): void
    {
        $raw = 'If you want emotions and bright colors in your life, welcome!';

        $about = $this->transformers->apply($raw, [
            'collapse_whitespace',
            'strip_invisible',
            ['regex', '/^(?:.*?Naposledy online:\s*\S+\s+)?(.*)$/u'],
            'trim',
        ]);

        $this->assertSame($raw, $about);
    }
}
