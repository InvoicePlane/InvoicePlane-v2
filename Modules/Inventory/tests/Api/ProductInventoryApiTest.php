<?php

namespace Modules\Inventory\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Core\tests\AbstractTestCase;
use Modules\Core\tests\ApiTestTrait;

class ProductInventoryApiTest extends AbstractTestCase
{
    use ApiTestTrait;
    use RefreshDatabase;
    use WithoutMiddleware;

    // region CRUD Tests
    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
    // endregion

    // region CRUD Tests
    // endregion
}
