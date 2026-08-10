<?php

namespace Tests\Feature;

use App\Models\Segment;
use Database\Seeders\SegmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SegmentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_default_segments_idempotently(): void
    {
        (new SegmentSeeder())->run();
        (new SegmentSeeder())->run();

        $this->assertSame(3, Segment::count());
        $this->assertTrue(Segment::where('slug', 'nova')->exists());
        $this->assertTrue(Segment::where('slug', 'overena')->exists());
        $this->assertTrue(Segment::where('slug', 'top-lokalita')->exists());
    }
}
