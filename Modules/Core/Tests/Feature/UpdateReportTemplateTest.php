<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Services\ReportTemplateService;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class UpdateReportTemplateTest extends AbstractAdminPanelTestCase
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
     *   "blocks": [
     *     {
     *       "id": "block_header_company",
     *       "type": "header_company",
     *       "position": {"x": 2, "y": 2, "width": 8, "height": 6},
     *       "config": {"show_vat_id": false},
     *       "label": "Updated Company Header",
     *       "isCloneable": true,
     *       "dataSource": "company",
     *       "isCloned": false,
     *       "clonedFrom": null
     *     }
     *   ]
     * }
     */
    public function it_updates_template_blocks(): void
    {
        /* arrange */
        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company);
        session(['current_company_id' => $company->id]);

        $initialBlocks = [
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

        $template = $this->service->createTemplate(
            $company,
            'Test Template',
            'invoice',
            $initialBlocks
        );

        $updatedBlocks = [
            [
                'id'          => 'block_header_company',
                'type'        => 'header_company',
                'position'    => ['x' => 2, 'y' => 2, 'width' => 8, 'height' => 6],
                'config'      => ['show_vat_id' => false],
                'label'       => 'Updated Company Header',
                'isCloneable' => true,
                'dataSource'  => 'company',
                'isCloned'    => false,
                'clonedFrom'  => null,
            ],
        ];

        /* act */
        $this->service->updateTemplate($template, $updatedBlocks);

        /* assert */
        $fileContents = Storage::disk('report_templates')->get(
            "{$company->id}/test-template.json"
        );
        $savedBlocks = json_decode($fileContents, true);

        $this->assertCount(1, $savedBlocks);
        $this->assertEquals(2, $savedBlocks[0]['position']['x']);
        $this->assertEquals(2, $savedBlocks[0]['position']['y']);
        $this->assertEquals(8, $savedBlocks[0]['position']['width']);
        $this->assertEquals(6, $savedBlocks[0]['position']['height']);
        $this->assertFalse($savedBlocks[0]['config']['show_vat_id']);
    }

    #[Test]
    #[Group('crud')]
    public function it_snaps_blocks_to_grid_on_update(): void
    {
        /* arrange */
        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company);
        session(['current_company_id' => $company->id]);

        $template = $this->service->createTemplate(
            $company,
            'Test Template',
            'invoice',
            []
        );

        $blocksWithValidPosition = [
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
        $this->service->updateTemplate($template, $blocksWithValidPosition);

        /* assert */
        $fileContents = Storage::disk('report_templates')->get(
            "{$company->id}/test-template.json"
        );
        $savedBlocks = json_decode($fileContents, true);

        $this->assertEquals(0, $savedBlocks[0]['position']['x']);
        $this->assertEquals(0, $savedBlocks[0]['position']['y']);
        $this->assertEquals(6, $savedBlocks[0]['position']['width']);
        $this->assertEquals(4, $savedBlocks[0]['position']['height']);
    }

    #[Test]
    #[Group('crud')]
    public function it_persists_updates_to_filesystem(): void
    {
        /* arrange */
        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company);
        session(['current_company_id' => $company->id]);

        $initialBlocks = [
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

        $template = $this->service->createTemplate(
            $company,
            'Test Template',
            'invoice',
            $initialBlocks
        );

        $updatedBlocks = [
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
            [
                'id'          => 'block_footer_totals',
                'type'        => 'footer_totals',
                'position'    => ['x' => 6, 'y' => 14, 'width' => 6, 'height' => 4],
                'config'      => ['show_subtotal' => true],
                'label'       => 'Invoice Totals',
                'isCloneable' => true,
                'dataSource'  => 'invoice',
                'isCloned'    => false,
                'clonedFrom'  => null,
            ],
        ];

        /* act */
        $this->service->updateTemplate($template, $updatedBlocks);

        /* assert */
        Storage::disk('report_templates')->assertExists(
            "{$company->id}/test-template.json"
        );

        $fileContents = Storage::disk('report_templates')->get(
            "{$company->id}/test-template.json"
        );
        $savedBlocks = json_decode($fileContents, true);

        $this->assertCount(2, $savedBlocks);
        $this->assertEquals('block_header_company', $savedBlocks[0]['id']);
        $this->assertEquals('block_footer_totals', $savedBlocks[1]['id']);
    }
}
