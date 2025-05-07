<?php

namespace Modules\Products\Tests\Unit\Services;

use InvalidArgumentException;
use Modules\Products\Models\Product;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ProductServiceTest extends TestCase
{
    #[Test]
    #[Group('services')]
    public function it_creates_a_product(): void
    {
        $service = new ProductService();

        $product = $service->create([
            'name'  => 'Desk Lamp',
            'sku'   => 'DL001',
            'price' => 49.99,
        ]);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Desk Lamp', $product->name);
    }

    #[Test]
    #[Group('services')]
    public function it_fails_to_create_without_sku(): void
    {
        $service = new ProductService();

        $this->expectException(InvalidArgumentException::class);

        $service->create([
            'name' => 'No SKU Product',
        ]);
    }
}
