<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Translation;
use App\Support\Locales;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Editable translations.
 *
 * Every visible string goes through `__()`, but the values lived only in
 * `lang/*.php` — the hero copy, the advert block, the eco badge and every
 * button label could only be changed by editing files and deploying.
 * DatabaseTranslationLoader puts a database override layer on top.
 */
class DatabaseTranslationsTest extends TestCase
{
    use RefreshDatabase;

    private function override(string $locale, string $group, string $key, string $value): Translation
    {
        return Translation::create([
            'locale' => $locale,
            'group' => $group,
            'key' => $key,
            'value' => $value,
            'default_value' => 'original',
        ]);
    }

    public function test_a_database_row_overrides_the_file_value(): void
    {
        app()->setLocale('cs');
        $fromFile = __('front.nav.home');

        $this->override('cs', 'front', 'nav.home', 'Domů z databáze');

        $this->assertSame('Domů z databáze', __('front.nav.home'));
        $this->assertNotSame($fromFile, __('front.nav.home'));
    }

    public function test_nested_keys_are_placed_correctly(): void
    {
        app()->setLocale('cs');

        $this->override('cs', 'front', 'landing.advert.title', 'Nový nadpis');

        $this->assertSame('Nový nadpis', __('front.landing.advert.title'));
        // Siblings inside the same nested array must survive the merge.
        $this->assertNotSame('front.landing.advert.subtitle', __('front.landing.advert.subtitle'));
    }

    public function test_keys_without_an_override_still_come_from_the_file(): void
    {
        app()->setLocale('cs');

        $this->override('cs', 'front', 'nav.home', 'Přepsáno');

        $this->assertNotSame('front.nav.login', __('front.nav.login'));
    }

    public function test_an_override_is_scoped_to_its_locale(): void
    {
        $this->override('cs', 'front', 'nav.home', 'Jen česky');

        app()->setLocale('en');
        $this->assertNotSame('Jen česky', __('front.nav.home'));

        app()->setLocale('cs');
        $this->assertSame('Jen česky', __('front.nav.home'));
    }

    public function test_a_null_value_falls_back_to_the_file(): void
    {
        app()->setLocale('cs');
        $fromFile = __('front.nav.home');

        Translation::create([
            'locale' => 'cs',
            'group' => 'front',
            'key' => 'nav.home',
            'value' => null,
            'default_value' => $fromFile,
        ]);

        $this->assertSame($fromFile, __('front.nav.home'));
    }

    /**
     * The scenario that matters in the admin: save a translation and re-render
     * within the same request. Both the loader's memo and the Translator's map
     * of loaded groups have to be dropped, not just the cache entry.
     */
    public function test_editing_a_row_takes_effect_within_the_same_request(): void
    {
        app()->setLocale('cs');

        $row = $this->override('cs', 'front', 'nav.home', 'První');
        $this->assertSame('První', __('front.nav.home'));

        $row->update(['value' => 'Druhé']);
        $this->assertSame('Druhé', __('front.nav.home'));

        $row->delete();
        $this->assertNotSame('Druhé', __('front.nav.home'));
    }

    public function test_import_loads_file_strings_into_the_table(): void
    {
        Artisan::call('translations:import', ['--locale' => ['cs'], '--group' => ['front']]);

        $this->assertTrue(
            Translation::where(['locale' => 'cs', 'group' => 'front', 'key' => 'nav.home'])->exists()
        );
    }

    /**
     * A deploy adding new strings must not discard the operator's wording.
     */
    public function test_import_keeps_values_edited_in_the_admin(): void
    {
        Artisan::call('translations:import', ['--locale' => ['cs'], '--group' => ['front']]);

        $row = Translation::where(['locale' => 'cs', 'group' => 'front', 'key' => 'nav.home'])->first();
        $row->update(['value' => 'Ručně upraveno']);

        Artisan::call('translations:import', ['--locale' => ['cs'], '--group' => ['front']]);

        $this->assertSame('Ručně upraveno', $row->fresh()->value);
    }

    public function test_force_import_restores_the_file_value(): void
    {
        Artisan::call('translations:import', ['--locale' => ['cs'], '--group' => ['front']]);

        $row = Translation::where(['locale' => 'cs', 'group' => 'front', 'key' => 'nav.home'])->first();
        $default = $row->default_value;
        $row->update(['value' => 'Ručně upraveno']);

        Artisan::call('translations:import', ['--locale' => ['cs'], '--group' => ['front'], '--force' => true]);

        $this->assertSame($default, $row->fresh()->value);
    }

    public function test_overridden_scope_only_returns_changed_rows(): void
    {
        Translation::create([
            'locale' => 'cs', 'group' => 'front', 'key' => 'a', 'value' => 'same', 'default_value' => 'same',
        ]);
        Translation::create([
            'locale' => 'cs', 'group' => 'front', 'key' => 'b', 'value' => 'changed', 'default_value' => 'original',
        ]);

        $this->assertSame(['b'], Translation::overridden()->pluck('key')->all());
    }

    public function test_russian_is_a_supported_locale_and_partial_translations_fall_back(): void
    {
        $this->assertContains('ru', Locales::codes());
        $this->assertContains('ru', Locales::withTranslations());

        // Deliberately excluded from the audit while the translation is partial.
        $this->assertNotContains('ru', Locales::audited());

        app()->setLocale('ru');
        $this->assertSame('Главная', __('front.nav.home'));
        // Not translated yet -> falls back rather than printing the raw key.
        $this->assertNotSame('front.profiles.photos.verified_badge', __('front.profiles.photos.verified_badge'));
    }

    /**
     * Menu order used to come from `created_at`, which put FAQ ahead of
     * VIP & Premium — the opposite of the design.
     */
    public function test_pages_are_ordered_by_sort_order_not_creation_date(): void
    {
        $faq = Page::create([
            'title' => ['cs' => 'FAQ'], 'slug' => 'faq', 'type' => 'page',
            'display_in_menu' => true, 'is_published' => true, 'sort_order' => 20,
        ]);
        $vip = Page::create([
            'title' => ['cs' => 'VIP'], 'slug' => 'vip-premium', 'type' => 'page',
            'display_in_menu' => true, 'is_published' => true, 'sort_order' => 10,
        ]);

        // FAQ was created first, so creation order would put it first.
        $this->assertTrue($faq->created_at->lessThanOrEqualTo($vip->created_at));

        $this->assertSame(
            ['vip-premium', 'faq'],
            Page::inMenu()->published()->ordered()->pluck('slug')->all()
        );
    }
}
