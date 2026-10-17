<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Never let a test touch the real IONOS buckets.
        Storage::fake('postImages');
        Storage::fake('sermons');
    }
}
