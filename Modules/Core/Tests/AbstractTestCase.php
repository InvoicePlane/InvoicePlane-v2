<?php

namespace Modules\Core\Tests;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Core\Tests\CreatesApplication;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class AbstractTestCase extends BaseTestCase
{
    use CreatesApplication;
}
