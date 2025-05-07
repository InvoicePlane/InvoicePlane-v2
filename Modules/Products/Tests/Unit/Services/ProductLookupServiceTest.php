<?php

namespace Modules\Products\Tests\Unit\Services;

use Modules\Products\Models\Product;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ProductLookupServiceTest extends TestCase
{
    #[Test]
    #[Group('services')]
    public function it_finds_products_by_sku(): void
    {
        Product::factory()->create(['sku' => 'A123']);

        $result = ProductLookupService::findBySku('A123');

        $this->assertNotNull($result);
        $this->assertEquals('A123', $result->sku);
    }

    #[Test]
    #[Group('services')]
    public function it_returns_null_for_unknown_sku(): void
    {
        $this->assertNull(ProductLookupService::findBySku('does-not-exist'));
    }
}
