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
        /* arrange */
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();

        /* act */
        $position->setX(2)->setY(3)->setWidth(4)->setHeight(2);

        $snapped = $service->snap($position);

        /* assert */
        $this->assertEquals(2, $snapped->getX());
        $this->assertEquals(3, $snapped->getY());
        $this->assertEquals(4, $snapped->getWidth());
        $this->assertEquals(2, $snapped->getHeight());
    }

    #[Test]
    #[Group('unit')]
    public function it_snaps_x_to_grid_boundaries(): void
    {
        /* arrange */
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();

        /* act */
        $position->setX(15)->setY(0)->setWidth(1)->setHeight(1);

        $snapped = $service->snap($position);

        /* assert */
        $this->assertEquals(11, $snapped->getX());
    }

    #[Test]
    #[Group('unit')]
    public function it_snaps_negative_x_to_zero(): void
    {
        /* arrange */
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();

        /* act */
        $position->setX(-5)->setY(0)->setWidth(1)->setHeight(1);

        $snapped = $service->snap($position);

        /* assert */
        $this->assertEquals(0, $snapped->getX());
    }

    #[Test]
    #[Group('unit')]
    public function it_snaps_negative_y_to_zero(): void
    {
        /* arrange */
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();

        /* act */
        $position->setX(0)->setY(-3)->setWidth(1)->setHeight(1);

        $snapped = $service->snap($position);

        /* assert */
        $this->assertEquals(0, $snapped->getY());
    }

    #[Test]
    #[Group('unit')]
    public function it_validates_correct_position(): void
    {
        /* arrange */
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();

        /* act */
        $position->setX(0)->setY(0)->setWidth(6)->setHeight(4);

        /* assert */
        $this->assertTrue($service->validate($position));
    }

    #[Test]
    #[Group('unit')]
    public function it_rejects_negative_x(): void
    {
        /* arrange */
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();

        /* act */
        $position->setX(-1)->setY(0)->setWidth(1)->setHeight(1);

        /* assert */
        $this->assertFalse($service->validate($position));
    }

    #[Test]
    #[Group('unit')]
    public function it_rejects_negative_y(): void
    {
        /* arrange */
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();

        /* act */
        $position->setX(0)->setY(-1)->setWidth(1)->setHeight(1);

        /* assert */
        $this->assertFalse($service->validate($position));
    }

    #[Test]
    #[Group('unit')]
    public function it_rejects_x_beyond_grid(): void
    {
        /* arrange */
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();

        /* act */
        $position->setX(12)->setY(0)->setWidth(1)->setHeight(1);

        /* assert */
        $this->assertFalse($service->validate($position));
    }

    #[Test]
    #[Group('unit')]
    public function it_rejects_width_exceeding_grid(): void
    {
        /* arrange */
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();

        /* act */
        $position->setX(8)->setY(0)->setWidth(5)->setHeight(1);

        /* assert */
        $this->assertFalse($service->validate($position));
    }

    #[Test]
    #[Group('unit')]
    public function it_rejects_zero_width(): void
    {
        /* arrange */
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();

        /* act */
        $position->setX(0)->setY(0)->setWidth(0)->setHeight(1);

        /* assert */
        $this->assertFalse($service->validate($position));
    }

    #[Test]
    #[Group('unit')]
    public function it_rejects_zero_height(): void
    {
        /* arrange */
        $service = new GridSnapperService(12);

        $position = new GridPositionDTO();

        /* act */
        $position->setX(0)->setY(0)->setWidth(1)->setHeight(0);

        /* assert */
        $this->assertFalse($service->validate($position));
    }

    #[Test]
    #[Group('unit')]
    public function it_snaps_to_grid(): void
    {
        $this->markTestIncomplete('Test incomplete - requires investigation for PHPStan coverage and implementation details');
    }
}
