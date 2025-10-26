<?php

namespace Modules\ReportBuilder\Tests\Unit;

use Modules\ReportBuilder\DTOs\GridPositionDTO;
use Modules\ReportBuilder\Services\GridSnapperService;
use Modules\ReportBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class GridSnapperServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function it_can_snap_valid_position(): void
    {
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();
        $position->setX(2)->setY(3)->setWidth(4)->setHeight(2);

        $snapped = $service->snap($position);

        $this->assertEquals(2, $snapped->getX());
        $this->assertEquals(3, $snapped->getY());
        $this->assertEquals(4, $snapped->getWidth());
        $this->assertEquals(2, $snapped->getHeight());
    }

    #[Test]
    #[Group('unit')]
    public function it_snaps_x_to_grid_boundaries(): void
    {
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();
        $position->setX(15)->setY(0)->setWidth(1)->setHeight(1);

        $snapped = $service->snap($position);

        $this->assertEquals(11, $snapped->getX());
    }

    #[Test]
    #[Group('unit')]
    public function it_snaps_negative_x_to_zero(): void
    {
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();
        $position->setX(-5)->setY(0)->setWidth(1)->setHeight(1);

        $snapped = $service->snap($position);

        $this->assertEquals(0, $snapped->getX());
    }

    #[Test]
    #[Group('unit')]
    public function it_snaps_negative_y_to_zero(): void
    {
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();
        $position->setX(0)->setY(-3)->setWidth(1)->setHeight(1);

        $snapped = $service->snap($position);

        $this->assertEquals(0, $snapped->getY());
    }

    #[Test]
    #[Group('unit')]
    public function it_validates_correct_position(): void
    {
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();
        $position->setX(0)->setY(0)->setWidth(6)->setHeight(4);

        $this->assertTrue($service->validate($position));
    }

    #[Test]
    #[Group('unit')]
    public function it_rejects_negative_x(): void
    {
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();
        $position->setX(-1)->setY(0)->setWidth(1)->setHeight(1);

        $this->assertFalse($service->validate($position));
    }

    #[Test]
    #[Group('unit')]
    public function it_rejects_negative_y(): void
    {
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();
        $position->setX(0)->setY(-1)->setWidth(1)->setHeight(1);

        $this->assertFalse($service->validate($position));
    }

    #[Test]
    #[Group('unit')]
    public function it_rejects_x_beyond_grid(): void
    {
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();
        $position->setX(12)->setY(0)->setWidth(1)->setHeight(1);

        $this->assertFalse($service->validate($position));
    }

    #[Test]
    #[Group('unit')]
    public function it_rejects_width_exceeding_grid(): void
    {
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();
        $position->setX(8)->setY(0)->setWidth(5)->setHeight(1);

        $this->assertFalse($service->validate($position));
    }

    #[Test]
    #[Group('unit')]
    public function it_rejects_zero_width(): void
    {
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();
        $position->setX(0)->setY(0)->setWidth(0)->setHeight(1);

        $this->assertFalse($service->validate($position));
    }

    #[Test]
    #[Group('unit')]
    public function it_rejects_zero_height(): void
    {
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();
        $position->setX(0)->setY(0)->setWidth(1)->setHeight(0);

        $this->assertFalse($service->validate($position));
    }
}
