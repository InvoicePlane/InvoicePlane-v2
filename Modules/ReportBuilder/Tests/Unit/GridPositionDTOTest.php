<?php

namespace Modules\ReportBuilder\Tests\Unit;

use Modules\ReportBuilder\DTOs\GridPositionDTO;
use Modules\ReportBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class GridPositionDTOTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function it_can_set_and_get_x(): void
    {
        $dto = new GridPositionDTO();
        $dto->setX(5);

        $this->assertEquals(5, $dto->getX());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_and_get_y(): void
    {
        $dto = new GridPositionDTO();
        $dto->setY(10);

        $this->assertEquals(10, $dto->getY());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_and_get_width(): void
    {
        $dto = new GridPositionDTO();
        $dto->setWidth(6);

        $this->assertEquals(6, $dto->getWidth());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_set_and_get_height(): void
    {
        $dto = new GridPositionDTO();
        $dto->setHeight(4);

        $this->assertEquals(4, $dto->getHeight());
    }

    #[Test]
    #[Group('unit')]
    public function setters_return_self_for_method_chaining(): void
    {
        $dto = (new GridPositionDTO())
            ->setX(0)
            ->setY(0)
            ->setWidth(12)
            ->setHeight(8);

        $this->assertInstanceOf(GridPositionDTO::class, $dto);
        $this->assertEquals(0, $dto->getX());
        $this->assertEquals(0, $dto->getY());
        $this->assertEquals(12, $dto->getWidth());
        $this->assertEquals(8, $dto->getHeight());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_handle_zero_values(): void
    {
        $dto = (new GridPositionDTO())
            ->setX(0)
            ->setY(0)
            ->setWidth(0)
            ->setHeight(0);

        $this->assertEquals(0, $dto->getX());
        $this->assertEquals(0, $dto->getY());
        $this->assertEquals(0, $dto->getWidth());
        $this->assertEquals(0, $dto->getHeight());
    }

    #[Test]
    #[Group('unit')]
    public function it_can_handle_large_values(): void
    {
        $dto = (new GridPositionDTO())
            ->setX(1000)
            ->setY(2000)
            ->setWidth(500)
            ->setHeight(300);

        $this->assertEquals(1000, $dto->getX());
        $this->assertEquals(2000, $dto->getY());
        $this->assertEquals(500, $dto->getWidth());
        $this->assertEquals(300, $dto->getHeight());
    }
}
