<?php

namespace Tests\Unit;

use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_a_persisted_report_with_valid_allegations(): void
    {
        $report = Report::factory()->create();

        $this->assertNotEmpty($report->reason);
        $this->assertNotEmpty($report->allegations);
        foreach ($report->allegations as $allegation) {
            $this->assertContains($allegation, Report::ALLEGATION_CATEGORIES);
        }
    }
}
