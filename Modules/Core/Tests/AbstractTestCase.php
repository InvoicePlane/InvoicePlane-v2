<?php

namespace Modules\Core\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class AbstractTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
