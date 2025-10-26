<?php

namespace Modules\ReportBuilder\Tests\Unit;

use InvalidArgumentException;
use Modules\Core\Models\Company;
use Modules\ReportBuilder\DTOs\BlockDTO;
use Modules\ReportBuilder\DTOs\GridPositionDTO;
use Modules\ReportBuilder\Models\ReportTemplate;
use Modules\ReportBuilder\Repositories\ReportTemplateFileRepository;
use Modules\ReportBuilder\Services\GridSnapperService;
use Modules\ReportBuilder\Services\ReportTemplateService;
use Modules\ReportBuilder\Tests\TestCase;
use Modules\ReportBuilder\Transformers\BlockTransformer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ReportTemplateServiceTest extends TestCase
{
    private ReportTemplateService $service;

    private ReportTemplateFileRepository $fileRepository;

    private GridSnapperService $gridSnapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileRepository = $this->createMock(ReportTemplateFileRepository::class);
        $this->gridSnapper    = new GridSnapperService(12);
        $this->service        = new ReportTemplateService($this->fileRepository, $this->gridSnapper);
    }

    #[Test]
    #[Group('unit')]
    public function it_creates_template(): void
    {
        $company = new Company();
        $company->id   = 1;
        $company->name = 'Test Company';

        $blocks = [
            [
                'id'       => 'block_1',
                'type'     => 'header_company',
                'position' => ['x' => 0, 'y' => 0, 'width' => 6, 'height' => 4],
                'config'   => [],
            ],
        ];

        $this->fileRepository->expects($this->once())
            ->method('save');

        $template = $this->service->createTemplate($company, 'Test Template', 'invoice', $blocks);

        $this->assertInstanceOf(ReportTemplate::class, $template);
        $this->assertEquals('Test Template', $template->name);
        $this->assertEquals('invoice', $template->template_type);
        $this->assertEquals(1, $template->company_id);
    }

    #[Test]
    #[Group('unit')]
    public function it_validates_blocks_require_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("must have an 'id'");

        $blocks = [
            [
                'type'     => 'header_company',
                'position' => ['x' => 0, 'y' => 0, 'width' => 6, 'height' => 4],
                'config'   => [],
            ],
        ];

        $this->service->validateBlocks($blocks);
    }

    #[Test]
    #[Group('unit')]
    public function it_validates_blocks_require_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("must have a 'type'");

        $blocks = [
            [
                'id'       => 'block_1',
                'position' => ['x' => 0, 'y' => 0, 'width' => 6, 'height' => 4],
                'config'   => [],
            ],
        ];

        $this->service->validateBlocks($blocks);
    }

    #[Test]
    #[Group('unit')]
    public function it_validates_blocks_require_position(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("must have a 'position' array");

        $blocks = [
            [
                'id'     => 'block_1',
                'type'   => 'header_company',
                'config' => [],
            ],
        ];

        $this->service->validateBlocks($blocks);
    }

    #[Test]
    #[Group('unit')]
    public function it_validates_position_has_required_fields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('position must have x, y, width, and height');

        $blocks = [
            [
                'id'       => 'block_1',
                'type'     => 'header_company',
                'position' => ['x' => 0, 'y' => 0],
                'config'   => [],
            ],
        ];

        $this->service->validateBlocks($blocks);
    }

    #[Test]
    #[Group('unit')]
    public function it_validates_position_is_valid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('has invalid position');

        $blocks = [
            [
                'id'       => 'block_1',
                'type'     => 'header_company',
                'position' => ['x' => -1, 'y' => 0, 'width' => 6, 'height' => 4],
                'config'   => [],
            ],
        ];

        $this->service->validateBlocks($blocks);
    }

    #[Test]
    #[Group('unit')]
    public function it_clones_system_block(): void
    {
        $position = new GridPositionDTO();
        $position->setX(6)->setY(0)->setWidth(6)->setHeight(4);

        $cloned = $this->service->cloneSystemBlock('header_company', 'block_cloned', $position);

        $this->assertInstanceOf(BlockDTO::class, $cloned);
        $this->assertEquals('block_cloned', $cloned->getId());
        $this->assertEquals('header_company', $cloned->getType());
        $this->assertTrue($cloned->getIsCloned());
        $this->assertEquals(6, $cloned->getPosition()->getX());
    }

    #[Test]
    #[Group('unit')]
    public function it_throws_exception_for_invalid_system_block_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("System block type 'invalid_type' not found");

        $position = new GridPositionDTO();
        $position->setX(0)->setY(0)->setWidth(6)->setHeight(4);

        $this->service->cloneSystemBlock('invalid_type', 'block_cloned', $position);
    }

    #[Test]
    #[Group('unit')]
    public function it_persists_blocks(): void
    {
        $template              = new ReportTemplate();
        $template->company_id  = 1;
        $template->slug        = 'test-template';

        $position = new GridPositionDTO();
        $position->setX(0)->setY(0)->setWidth(6)->setHeight(4);

        $block = new BlockDTO();
        $block->setId('block_1')
            ->setType('header_company')
            ->setPosition($position)
            ->setConfig([])
            ->setIsCloneable(true)
            ->setIsCloned(false);

        $this->fileRepository->expects($this->once())
            ->method('save')
            ->with(1, 'test-template', $this->isType('array'));

        $this->service->persistBlocks($template, [$block]);
    }

    #[Test]
    #[Group('unit')]
    public function it_loads_blocks(): void
    {
        $template             = new ReportTemplate();
        $template->company_id = 1;
        $template->slug       = 'test-template';

        $blockData = [
            [
                'id'          => 'block_1',
                'type'        => 'header_company',
                'position'    => ['x' => 0, 'y' => 0, 'width' => 6, 'height' => 4],
                'config'      => [],
                'label'       => null,
                'isCloneable' => true,
                'dataSource'  => null,
                'isCloned'    => false,
                'clonedFrom'  => null,
            ],
        ];

        $this->fileRepository->expects($this->once())
            ->method('get')
            ->with(1, 'test-template')
            ->willReturn($blockData);

        $blocks = $this->service->loadBlocks($template);

        $this->assertIsArray($blocks);
        $this->assertCount(1, $blocks);
        $this->assertInstanceOf(BlockDTO::class, $blocks[0]);
        $this->assertEquals('block_1', $blocks[0]->getId());
    }
}
