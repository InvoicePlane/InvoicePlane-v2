<?php

namespace Modules\Core\Tests\Unit;

use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Modules\Core\DTOs\BlockDTO;
use Modules\Core\DTOs\GridPositionDTO;
use Modules\Core\Models\ReportTemplate;
use Modules\Core\Repositories\ReportTemplateFileRepository;
use Modules\Core\Services\GridSnapperService;
use Modules\Core\Services\ReportTemplateService;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

class ReportTemplateServiceTest extends AbstractAdminPanelTestCase
{
    private ReportTemplateService $service;

    private ReportTemplateFileRepository $fileRepository;

    private GridSnapperService $gridSnapper;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('report_templates');

        $this->fileRepository = new ReportTemplateFileRepository();
        $this->gridSnapper    = new GridSnapperService(12);
        $this->service        = new ReportTemplateService($this->fileRepository, $this->gridSnapper);
    }

    #[Test]
    #[Group('unit')]
    public function it_creates_template(): void
    {
        /* arrange */
        $company     = new stdClass();
        $company->id = 1;
        $blocks      = [];

        /* act */
        /** @phpstan-ignore-next-line */
        $template = $this->service->createTemplate($company, 'Test Template', 'invoice', $blocks);

        /* assert */
        $this->assertInstanceOf(ReportTemplate::class, $template);
        $this->assertEquals('Test Template', $template->name);
        $this->assertEquals('invoice', $template->template_type);
        $this->assertEquals(1, $template->company_id);
    }

    #[Test]
    #[Group('unit')]
    public function it_validates_blocks_require_id(): void
    {
        /* arrange */
        // No setup needed

        /* assert */
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("must have an 'id'");
        $blocks = [
            [
                'type'     => 'company_header',
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
        /* arrange */
        // No setup needed

        /* assert */
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
        /* arrange */
        // No setup needed

        /* assert */
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("must have a 'position' array");
        $blocks = [
            [
                'id'     => 'block_1',
                'type'   => 'company_header',
                'config' => [],
            ],
        ];
        $this->service->validateBlocks($blocks);
    }

    #[Test]
    #[Group('unit')]
    public function it_validates_position_has_required_fields(): void
    {
        /* arrange */
        // No setup needed

        /* assert */
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('position must have x, y, width, and height');
        $blocks = [
            [
                'id'       => 'block_1',
                'type'     => 'company_header',
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
        /* arrange */
        // No setup needed

        /* assert */
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('has invalid position');
        $blocks = [
            [
                'id'       => 'block_1',
                'type'     => 'company_header',
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
        /* arrange */
        $position = new GridPositionDTO();

        /* act */
        $position->setX(6)->setY(0)->setWidth(6)->setHeight(4);

        $cloned = $this->service->cloneSystemBlock('company_header', 'block_cloned', $position);

        /* assert */
        $this->assertInstanceOf(BlockDTO::class, $cloned);
        $this->assertEquals('block_cloned', $cloned->getId());
        $this->assertEquals('company_header', $cloned->getType());
        $this->assertTrue($cloned->getIsCloned());
        $this->assertEquals(6, $cloned->getPosition()->getX());
    }

    #[Test]
    #[Group('unit')]
    public function it_throws_exception_for_invalid_system_block_type(): void
    {
        /* arrange */
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("System block type 'invalid_type' not found");

        $position = new GridPositionDTO();

        /* act */
        $position->setX(0)->setY(0)->setWidth(6)->setHeight(4);

        /* assert */
        $this->service->cloneSystemBlock('invalid_type', 'block_cloned', $position);
    }

    #[Test]
    #[Group('unit')]
    public function it_persists_blocks(): void
    {
        /* arrange */
        $template             = new ReportTemplate();
        $template->company_id = 1;
        $template->slug       = 'test-template';

        $position = new GridPositionDTO();
        $position->setX(0)->setY(0)->setWidth(6)->setHeight(4);

        $block = new BlockDTO();
        $block->setId('block_1')
            ->setType('company_header')
            ->setPosition($position)
            ->setConfig([])
            ->setIsCloneable(true)
            ->setIsCloned(false);

        /* act */
        $this->service->persistBlocks($template, [$block]);

        /* assert */
        Storage::disk('report_templates')->assertExists('1/test-template.json');
        $content = Storage::disk('report_templates')->get('1/test-template.json');
        $data    = json_decode($content, true);

        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertEquals('block_1', $data[0]['id']);
        $this->assertEquals('company_header', $data[0]['type']);
    }

    #[Test]
    #[Group('unit')]
    public function it_loads_blocks(): void
    {
        /* arrange */
        $template             = new ReportTemplate();
        $template->company_id = 1;
        $template->slug       = 'test-template';

        $fileData = [
            [
                'id'       => 'block_1',
                'type'     => 'company_header',
                'position' => ['x' => 0, 'y' => 0, 'width' => 6, 'height' => 4],
                'config'   => [],
            ],
        ];

        Storage::disk('report_templates')->put(
            '1/test-template.json',
            json_encode($fileData, JSON_PRETTY_PRINT)
        );

        /* act */
        $blocks = $this->service->loadBlocks($template);

        /* assert */
        $this->assertIsArray($blocks);
        $this->assertCount(1, $blocks);
        $this->assertInstanceOf(BlockDTO::class, $blocks[0]);
        $this->assertEquals('block_1', $blocks[0]->getId());
    }
}
