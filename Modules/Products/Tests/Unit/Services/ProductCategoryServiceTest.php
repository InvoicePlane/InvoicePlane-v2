<?php

namespace Modules\Products\Tests\Unit\Services;

use InvalidArgumentException;
use Modules\Products\Models\ProductCategory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ProductCategoryServiceTest extends AbstractTestCase
{
    #[Test]
    #[Group('services')]
    public function it_creates_a_family(): void
    {
        $service = new ProductCategoryService();

        $family = $service->create(['name' => 'Lighting']);

        $this->assertInstanceOf(ProductCategory::class, $family);
        $this->assertEquals('Lighting', $family->name);
    }

    #[Test]
    #[Group('services')]
    public function it_requires_a_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ProductCategoryService())->create([]);
    }
}
