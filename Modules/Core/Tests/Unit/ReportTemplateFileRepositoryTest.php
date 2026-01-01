<?php

namespace Modules\Core\Tests\Unit;

use Illuminate\Support\Facades\Storage;
use Modules\Core\Repositories\ReportTemplateFileRepository;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Test;

class ReportTemplateFileRepositoryTest extends AbstractAdminPanelTestCase
{
    protected ReportTemplateFileRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new ReportTemplateFileRepository();

        // Ensure clean state before each test
        Storage::fake('report_templates');
    }

    #[Test]
    public function it_save_creates_template_file(): void
    {
        /* arrange */
        $companyId    = 1;
        $templateSlug = 'professional_invoice';
        $blocksArray  = [
            [
                'id'          => 'block_1',
                'type'        => 'company_header',
                'position'    => ['x' => 0, 'y' => 0, 'width' => 6, 'height' => 4],
                'config'      => ['show_vat_id' => true, 'show_phone' => true],
                'is_cloned'   => false,
                'cloned_from' => null,
            ],
        ];

        /* act */
        $this->repository->save($companyId, $templateSlug, $blocksArray);

        /* assert */
        Storage::disk('report_templates')->assertExists("{$companyId}/{$templateSlug}.json");
    }

    #[Test]
    public function it_get_returns_blocks_array(): void
    {
        /* arrange */
        $companyId    = 1;
        $templateSlug = 'minimal_invoice';
        $blocksArray  = [
            [
                'id'          => 'block_header',
                'type'        => 'company_header',
                'position'    => ['x' => 0, 'y' => 0, 'width' => 12, 'height' => 2],
                'config'      => ['font_size' => 10],
                'is_cloned'   => false,
                'cloned_from' => null,
            ],
        ];
        $this->repository->save($companyId, $templateSlug, $blocksArray);

        /* act */
        $result = $this->repository->get($companyId, $templateSlug);

        /* assert */
        $this->assertEquals($blocksArray, $result);
    }

    #[Test]
    public function it_get_returns_empty_array_when_template_not_exists(): void
    {
        /* arrange */
        // No setup needed

        /* act */
        $result = $this->repository->get(999, 'non_existent_template');

        /* assert */
        $this->assertEquals([], $result);
    }

    #[Test]
    public function it_exists_returns_true_when_template_exists(): void
    {
        /* arrange */
        $companyId    = 1;
        $templateSlug = 'payment_history_report';
        $blocksArray  = [
            [
                'id'          => 'block_1',
                'type'        => 'table',
                'position'    => ['x' => 0, 'y' => 0, 'width' => 12, 'height' => 8],
                'config'      => [],
                'is_cloned'   => false,
                'cloned_from' => null,
            ],
        ];
        $this->repository->save($companyId, $templateSlug, $blocksArray);

        /* act */
        $result = $this->repository->exists($companyId, $templateSlug);

        /* assert */
        $this->assertTrue($result);
    }

    #[Test]
    public function it_exists_returns_false_when_template_not_exists(): void
    {
        /* arrange */
        // No setup needed

        /* act */
        $result = $this->repository->exists(1, 'non_existent_template');

        /* assert */
        $this->assertFalse($result);
    }

    #[Test]
    public function it_delete_removes_template_file(): void
    {
        /* arrange */
        $companyId    = 1;
        $templateSlug = 'invoice_aging_report';
        $blocksArray  = [
            [
                'id'          => 'block_1',
                'type'        => 'chart',
                'position'    => ['x' => 0, 'y' => 0, 'width' => 6, 'height' => 6],
                'config'      => ['chart_type' => 'bar'],
                'is_cloned'   => false,
                'cloned_from' => null,
            ],
        ];
        $this->repository->save($companyId, $templateSlug, $blocksArray);

        /* act */
        $result = $this->repository->delete($companyId, $templateSlug);

        /* assert */
        $this->assertTrue($result);
        $this->assertFalse($this->repository->exists($companyId, $templateSlug));
    }

    #[Test]
    public function it_delete_returns_false_when_template_not_exists(): void
    {
        /* arrange */
        // No setup needed

        /* act */
        $result = $this->repository->delete(1, 'non_existent_template');

        /* assert */
        $this->assertFalse($result);
    }

    #[Test]
    public function it_all_returns_template_slugs_for_company(): void
    {
        /* arrange */
        $companyId = 1;
        $this->repository->save($companyId, 'professional_invoice', []);
        $this->repository->save($companyId, 'payment_history_report', []);
        $this->repository->save($companyId, 'invoice_aging_report', []);

        /* act */
        $result = $this->repository->all($companyId);

        /* assert */
        $this->assertCount(3, $result);
        $this->assertContains('professional_invoice', $result);
        $this->assertContains('payment_history_report', $result);
        $this->assertContains('invoice_aging_report', $result);
    }

    #[Test]
    public function it_all_returns_empty_array_when_no_templates_exist(): void
    {
        /* arrange */
        // No setup needed

        /* act */
        $result = $this->repository->all(999);

        /* assert */
        $this->assertEquals([], $result);
    }

    #[Test]
    public function it_all_returns_only_templates_for_specific_company(): void
    {
        /* arrange */
        $this->repository->save(1, 'template_company_1', []);
        $this->repository->save(2, 'template_company_2', []);

        /* act */
        $resultCompany1 = $this->repository->all(1);
        $resultCompany2 = $this->repository->all(2);

        /* assert */
        $this->assertCount(1, $resultCompany1);
        $this->assertContains('template_company_1', $resultCompany1);
        $this->assertCount(1, $resultCompany2);
        $this->assertContains('template_company_2', $resultCompany2);
    }

    #[Test]
    public function it_handles_grouped_blocks(): void
    {
        /* Arrange */
        $groupedData = [
            'header' => [
                ['id' => 'block1', 'band' => 'header', 'type' => 'test'],
            ],
            'details' => [
                ['id' => 'block2', 'band' => 'details', 'type' => 'test'],
            ],
        ];

        Storage::disk('report_templates')->put(
            '1/grouped.json',
            json_encode($groupedData)
        );

        /* Act */
        $blocks = $this->repository->get(1, 'grouped');

        /* Assert */
        $this->assertCount(2, $blocks);
        $this->assertEquals('block1', $blocks[0]['id']);
        $this->assertEquals('block2', $blocks[1]['id']);
    }
}
