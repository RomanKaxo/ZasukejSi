<?php

namespace Tests\Feature;

use Tests\TestCase;

class SegmentTranslationsTest extends TestCase
{
    public function test_segment_translation_keys_exist_in_both_locales(): void
    {
        foreach (['cs', 'en'] as $locale) {
            app()->setLocale($locale);

            $this->assertNotEquals('segments.form.name', __('segments.form.name'));
            $this->assertNotEquals('segments.table.name', __('segments.table.name'));
            $this->assertNotEquals('filament.navigation.segments', __('filament.navigation.segments'));
            $this->assertNotEquals('common.Segment', __('common.Segment'));
            $this->assertNotEquals('common.Segments', __('common.Segments'));
            $this->assertNotEquals('profiles.filters.segment', __('profiles.filters.segment'));
        }
    }
}
