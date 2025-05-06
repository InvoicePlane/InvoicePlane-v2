<?php

namespace Modules\Products\Tests\Unit\Services;

use InvalidArgumentException;
use Modules\Products\Models\ProductUnit;
use Modules\Products\Services\ProductUnitService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ProductUnitServiceTest extends TestCase
{
    #[Test]
    #[Group('services')]
    public function it_creates_unit_of_measure(): void
    {
        $unit = (new ProductUnitService())->create(['name' => 'Box']);

        $this->assertInstanceOf(ProductUnit::class, $unit);
        $this->assertEquals('Box', $unit->name);
    }

    #[Test]
    #[Group('services')]
    public function it_fails_if_name_is_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ProductUnitService())->create([]);
    }
}
