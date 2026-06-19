<?php

namespace Modules\Inventory\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Core\tests\AbstractTestCase;

class ProductInventoryTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    // region Setup

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // endregion

    // region CRUD Tests
    // endregion
}
