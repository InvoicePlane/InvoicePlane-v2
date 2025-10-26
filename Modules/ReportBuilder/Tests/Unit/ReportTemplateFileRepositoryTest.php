<?php

namespace Modules\ReportBuilder\Tests\Unit;

use Illuminate\Support\Facades\Storage;
use Modules\ReportBuilder\Repositories\ReportTemplateFileRepository;
use Modules\ReportBuilder\Tests\TestCase;

class ReportTemplateFileRepositoryTest extends TestCase
{
    protected ReportTemplateFileRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new ReportTemplateFileRepository();

        // Ensure clean state before each test
        Storage::fake('report_templates');
    }

    public function test_save_creates_template_file(): void
    {
        $companyId     = 1;
        $templateSlug  = 'professional_invoice';
        $blocksArray   = [
            [
                'id'          => 'block_1',
                'type'        => 'header_company',
                'position'    => ['x' => 0, 'y' => 0, 'width' => 6, 'height' => 4],
                'config'      => ['show_vat_id' => true, 'show_phone' => true],
                'is_cloned'   => false,
                'cloned_from' => null,
            ],
        ];

        $this->repository->save($companyId, $templateSlug, $blocksArray);

        Storage::disk('report_templates')->assertExists("{$companyId}/{$templateSlug}.json");
    }

    public function test_get_returns_blocks_array(): void
    {
        $companyId     = 1;
        $templateSlug  = 'minimal_invoice';
        $blocksArray   = [
            [
                'id'          => 'block_header',
                'type'        => 'header_company',
                'position'    => ['x' => 0, 'y' => 0, 'width' => 12, 'height' => 2],
                'config'      => ['font_size' => 10],
                'is_cloned'   => false,
                'cloned_from' => null,
            ],
        ];

        $this->repository->save($companyId, $templateSlug, $blocksArray);

        $result = $this->repository->get($companyId, $templateSlug);

        $this->assertEquals($blocksArray, $result);
    }

    public function test_get_returns_empty_array_when_template_not_exists(): void
    {
        $result = $this->repository->get(999, 'non_existent_template');

        $this->assertEquals([], $result);
    }

    public function test_exists_returns_true_when_template_exists(): void
    {
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

        $this->assertTrue($this->repository->exists($companyId, $templateSlug));
    }

    public function test_exists_returns_false_when_template_not_exists(): void
    {
        $this->assertFalse($this->repository->exists(1, 'non_existent_template'));
    }

    public function test_delete_removes_template_file(): void
    {
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

        $result = $this->repository->delete($companyId, $templateSlug);

        $this->assertTrue($result);
        $this->assertFalse($this->repository->exists($companyId, $templateSlug));
    }

    public function test_delete_returns_false_when_template_not_exists(): void
    {
        $result = $this->repository->delete(1, 'non_existent_template');

        $this->assertFalse($result);
    }

    public function test_all_returns_template_slugs_for_company(): void
    {
        $companyId = 1;

        $this->repository->save($companyId, 'professional_invoice', []);
        $this->repository->save($companyId, 'payment_history_report', []);
        $this->repository->save($companyId, 'invoice_aging_report', []);

        $result = $this->repository->all($companyId);

        $this->assertCount(3, $result);
        $this->assertContains('professional_invoice', $result);
        $this->assertContains('payment_history_report', $result);
        $this->assertContains('invoice_aging_report', $result);
    }

    public function test_all_returns_empty_array_when_no_templates_exist(): void
    {
        $result = $this->repository->all(999);

        $this->assertEquals([], $result);
    }

    public function test_all_returns_only_templates_for_specific_company(): void
    {
        $this->repository->save(1, 'template_company_1', []);
        $this->repository->save(2, 'template_company_2', []);

        $resultCompany1 = $this->repository->all(1);
        $resultCompany2 = $this->repository->all(2);

        $this->assertCount(1, $resultCompany1);
        $this->assertContains('template_company_1', $resultCompany1);
        $this->assertCount(1, $resultCompany2);
        $this->assertContains('template_company_2', $resultCompany2);
    }
}
