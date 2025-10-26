<?php

namespace Modules\ReportBuilder\Tests\Feature;

use Modules\Core\Tests\AbstractAdminPanelTestCase;
use Modules\ReportBuilder\DTOs\GridPositionDTO;
use Modules\ReportBuilder\Services\GridSnapperService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class GridSnapperTest extends AbstractAdminPanelTestCase
{
    private GridSnapperService $gridSnapper;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->gridSnapper = app(GridSnapperService::class);
    }

    #[Test]
    #[Group('grid')]
    /**
     * @payload
     * {
     *   "position": {"x": 0, "y": 0, "width": 6, "height": 4}
     * }
     */
    public function it_snaps_position_to_grid(): void
    {
        /* Arrange */
        $position = new GridPositionDTO();
        $position->setX(0)->setY(0)->setWidth(6)->setHeight(4);

        /* Act */
        $snapped = $this->gridSnapper->snap($position);

        /* Assert */
        $this->assertEquals(0, $snapped->getX());
        $this->assertEquals(0, $snapped->getY());
        $this->assertEquals(6, $snapped->getWidth());
        $this->assertEquals(4, $snapped->getHeight());
    }

    #[Test]
    #[Group('grid')]
    /**
     * @payload
     * {
     *   "position": {"x": 0, "y": 0, "width": 6, "height": 4}
     * }
     */
    public function it_validates_position_constraints(): void
    {
        /* Arrange */
        $validPosition = new GridPositionDTO();
        $validPosition->setX(0)->setY(0)->setWidth(6)->setHeight(4);

        $invalidPositionX = new GridPositionDTO();
        $invalidPositionX->setX(-1)->setY(0)->setWidth(6)->setHeight(4);

        $invalidPositionWidth = new GridPositionDTO();
        $invalidPositionWidth->setX(0)->setY(0)->setWidth(0)->setHeight(4);

        /* Act & Assert */
        $this->assertTrue($this->gridSnapper->validate($validPosition));
        $this->assertFalse($this->gridSnapper->validate($invalidPositionX));
        $this->assertFalse($this->gridSnapper->validate($invalidPositionWidth));
    }
}
