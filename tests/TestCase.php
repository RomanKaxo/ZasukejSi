<?php

namespace Tests;

use App\Models\Setting;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Settings are memoised in a static for the life of the request, which in
     * a test run is the life of the whole process. A value one test wrote was
     * still being served to the next one, so the suite could pass or fail
     * depending on the order it ran in.
     *
     * Only the in-memory memo is dropped — nothing is queried, so this is safe
     * in tests that never migrate.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Setting::flush();
    }
}
