<?php

namespace Tests\Feature;

use App\Models\Segment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileListSegmentFilterUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_segment_filter_select_lists_active_segments(): void
    {
        // Segment names are locale-translatable; pin the locale so this assertion
        // doesn't depend on the app's configured default locale (APP_LOCALE=en
        // in this repo's .env, used by the test suite).
        app()->setLocale('cs');

        $segment = Segment::factory()->create(['name' => ['cs' => 'Nová', 'en' => 'New'], 'is_active' => true]);
        Segment::factory()->create(['is_active' => false]);

        Livewire::test(\App\Livewire\ProfileList::class)
            ->assertSee('Nová')
            ->assertSee('wire:model.live="segmentId"', false);
    }
}
