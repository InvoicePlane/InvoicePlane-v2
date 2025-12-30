<?php

namespace Modules\ReportBuilder\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use Modules\ReportBuilder\Models\ReportTemplate;
use Modules\ReportBuilder\Services\ReportTemplateService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class CreateReportTemplateTest extends AbstractAdminPanelTestCase
{
    private ReportTemplateService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('report_templates');
        $this->service = app(ReportTemplateService::class);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "name": "Test Invoice Template",
     *   "template_type": "invoice",
     *   "blocks": [
     *     {
     *       "id": "block_header_company",
     *       "type": "header_company",
     *       "position": {"x": 0, "y": 0, "width": 6, "height": 4},
     *       "config": {"show_vat_id": true},
     *       "label": "Company Header",
     *       "isCloneable": true,
     *       "dataSource": "company",
     *       "isCloned": false,
     *       "clonedFrom": null
     *     }
     *   ]
     * }
     */
    public function it_creates_report_template_with_valid_blocks(): void
    {
        /* arrange */
        $company = $this->createCompanyContext();

        $blocks = [
            [
                'id'          => 'block_header_company',
                'type'        => 'header_company',
                'position'    => ['x' => 0, 'y' => 0, 'width' => 6, 'height' => 4],
                'config'      => ['show_vat_id' => true],
                'label'       => 'Company Header',
                'isCloneable' => true,
                'dataSource'  => 'company',
                'isCloned'    => false,
                'clonedFrom'  => null,
            ],
        ];

        /* act */
        $template = $this->service->createTemplate(
            $company,
            'Test Invoice Template',
            'invoice',
            $blocks
        );

        /* assert */
        $this->assertDatabaseHas('report_templates', [
            'company_id'    => $company->id,
            'name'          => 'Test Invoice Template',
            'slug'          => 'test-invoice-template',
            'template_type' => 'invoice',
            'is_system'     => false,
            'is_active'     => true,
        ]);

        $this->assertInstanceOf(ReportTemplate::class, $template);
        $this->assertEquals('Test Invoice Template', $template->name);
    }

    #[Test]
    #[Group('crud')]
    public function it_persists_blocks_to_filesystem(): void
    {
        /* arrange */
        $company = $this->createCompanyContext();

        $blocks = [
            [
                'id'          => 'block_header_company',
                'type'        => 'header_company',
                'position'    => ['x' => 0, 'y' => 0, 'width' => 6, 'height' => 4],
                'config'      => ['show_vat_id' => true],
                'label'       => 'Company Header',
                'isCloneable' => true,
                'dataSource'  => 'company',
                'isCloned'    => false,
                'clonedFrom'  => null,
            ],
        ];

        /* act */
        $_template = $this->service->createTemplate(
            $company,
            'Test Template',
            'invoice',
            $blocks
        );

        /* assert */
        Storage::disk('report_templates')->assertExists(
            "{$company->id}/test-template.json"
        );

        $fileContents = Storage::disk('report_templates')->get(
            "{$company->id}/test-template.json"
        );
        $savedBlocks = json_decode($fileContents, true);

        $this->assertIsArray($savedBlocks);
        $this->assertCount(1, $savedBlocks);
        $this->assertEquals('block_header_company', $savedBlocks[0]['id']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload invalid block type
     * {
     *   "name": "Test Template",
     *   "template_type": "invoice",
     *   "blocks": [
     *     {
     *       "id": "block_invalid",
     *       "type": "",
     *       "position": {"x": 0, "y": 0, "width": 6, "height": 4},
     *       "config": {}
     *     }
     *   ]
     * }
     */
    public function it_rejects_invalid_block_types(): void
    {
        /* arrange */
        $company = $this->createCompanyContext();

        $invalidBlocks = [
            [
                'id'       => 'block_invalid',
                'type'     => '',
                'position' => ['x' => 0, 'y' => 0, 'width' => 6, 'height' => 4],
                'config'   => [],
            ],
        ];

        /* Act & Assert */
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("must have a 'type'");

        $this->service->createTemplate(
            $company,
            'Test Template',
            'invoice',
            $invalidBlocks
        );
    }

    #[Test]
    #[Group('multi-tenancy')]
    public function it_respects_company_tenancy(): void
    {
        /* arrange */
        $company1 = $this->createCompanyContext();
        $company2 = Company::factory()->create();

        $blocks = [
            [
                'id'          => 'block_header_company',
                'type'        => 'header_company',
                'position'    => ['x' => 0, 'y' => 0, 'width' => 6, 'height' => 4],
                'config'      => [],
                'label'       => 'Company Header',
                'isCloneable' => true,
                'dataSource'  => 'company',
                'isCloned'    => false,
                'clonedFrom'  => null,
            ],
        ];

        /* act */
        $template = $this->service->createTemplate(
            $company1,
            'Company 1 Template',
            'invoice',
            $blocks
        );

        /* assert */
        $this->assertEquals($company1->id, $template->company_id);
        $this->assertNotEquals($company2->id, $template->company_id);

        Storage::disk('report_templates')->assertExists(
            "{$company1->id}/company-1-template.json"
        );
        Storage::disk('report_templates')->assertMissing(
            "{$company2->id}/company-1-template.json"
        );
    }

    protected function createCompanyContext(): Company
    {
        /** @var Company $company */
        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company);
        session(['current_company_id' => $company->id]);

        return $company;
    }
}
