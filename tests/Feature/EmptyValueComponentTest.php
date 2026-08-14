<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The <x-empty-value /> component backs the project-wide rule that no value is
 * ever invented: a missing attribute renders as a neutral placeholder while its
 * surrounding tile stays in place.
 *
 * See docs/conventions/empty-values.md.
 */
class EmptyValueComponentTest extends TestCase
{
    public function test_default_variant_renders_a_dash(): void
    {
        app()->setLocale('cs');

        $html = (string) $this->blade('<x-empty-value />');

        $this->assertStringContainsString('—', $html);
        $this->assertStringContainsString('empty-value', $html);
    }

    public function test_text_variant_renders_the_translated_label(): void
    {
        app()->setLocale('cs');
        $this->assertStringContainsString('Neuvedeno', (string) $this->blade('<x-empty-value variant="text" />'));

        app()->setLocale('en');
        $this->assertStringContainsString('Not specified', (string) $this->blade('<x-empty-value variant="text" />'));
    }

    /**
     * The component must not carry typography of its own — it inherits
     * font-family/size/weight from the element it sits in, so a tile keeps
     * exactly the same metrics whether the value is present or not. Only the
     * colour is muted, and that is done with currentColor so it works on any
     * background.
     */
    public function test_component_declares_no_typography_of_its_own(): void
    {
        $html = (string) $this->blade('<x-empty-value />');

        $this->assertStringNotContainsString('font-family', $html);
        $this->assertStringNotContainsString('font-size', $html);
        $this->assertStringNotContainsString('font-weight', $html);
        $this->assertStringContainsString('currentColor', $html);
    }

    public function test_caller_supplied_attributes_are_merged(): void
    {
        $html = (string) $this->blade('<x-empty-value class="profile-stat" />');

        $this->assertStringContainsString('empty-value', $html);
        $this->assertStringContainsString('profile-stat', $html);
    }

    public function test_placeholder_is_announced_to_screen_readers(): void
    {
        app()->setLocale('cs');

        $this->assertStringContainsString('aria-label="Neuvedeno"', (string) $this->blade('<x-empty-value />'));
    }
}
