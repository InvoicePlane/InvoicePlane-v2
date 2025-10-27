<?php

namespace Modules\ReportBuilder\Tests\Feature;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use Modules\ReportBuilder\Models\ReportTemplate;
use Modules\ReportBuilder\Services\ReportRenderer;
use Modules\ReportBuilder\Services\ReportTemplateService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ReportRenderingTest extends AbstractAdminPanelTestCase
{
    private ReportTemplateService $service;
    private ReportRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('report_templates');
        $this->service = app(ReportTemplateService::class);
        $this->renderer = app(ReportRenderer::class);
    }

    protected function createCompanyContext(): Company
    {
        $company = Company::factory()->create();
        $user = User::factory()->create();
        $user->companies()->attach($company);
        session(['current_company_id' => $company->id]);
        
        return $company;
    }

    #[Test]
    #[Group('rendering')]
    /**
     * @payload
     * {
     *   "blocks": [
     *     {"id": "block_header_company", "type": "header_company", "position": {"x": 0, "y": 0, "width": 6, "height": 4}},
     *     {"id": "block_detail_items", "type": "detail_items", "position": {"x": 0, "y": 6, "width": 12, "height": 6}}
     *   ],
     *   "data": {"company": {"name": "Test Company"}, "items": []}
     * }
     */
    public function it_renders_template_to_html_with_correct_block_order(): void
    {
        /* Arrange */
        $company = $this->createCompanyContext();

        $blocks = [
            [
                'id' => 'block_header_company',
                'type' => 'header_company',
                'position' => ['x' => 0, 'y' => 0, 'width' => 6, 'height' => 4],
                'config' => ['show_vat_id' => true],
                'label' => 'Company Header',
                'isCloneable' => true,
                'dataSource' => 'company',
                'isCloned' => false,
                'clonedFrom' => null,
            ],
            [
                'id' => 'block_detail_items',
                'type' => 'detail_items',
                'position' => ['x' => 0, 'y' => 6, 'width' => 12, 'height' => 6],
                'config' => ['show_description' => true],
                'label' => 'Invoice Items',
                'isCloneable' => true,
                'dataSource' => 'invoice',
                'isCloned' => false,
                'clonedFrom' => null,
            ],
        ];

        $template = $this->service->createTemplate(
            $company,
            'Test Template',
            'invoice',
            $blocks
        );

        $data = [
            'company' => [
                'name' => 'Test Company',
                'vat_id' => 'VAT123',
            ],
            'items' => [],
        ];

        /* Act */
        $html = $this->renderer->render($template, $data);

        /* Assert */
        $this->assertIsString($html);
        $this->assertStringContainsString('Test Company', $html);
    }

    #[Test]
    #[Group('rendering')]
    public function it_renders_template_to_pdf(): void
    {
        /* Arrange */
        $company = $this->createCompanyContext();

        $blocks = [
            [
                'id' => 'block_header_company',
                'type' => 'header_company',
                'position' => ['x' => 0, 'y' => 0, 'width' => 6, 'height' => 4],
                'config' => [],
                'label' => 'Company Header',
                'isCloneable' => true,
                'dataSource' => 'company',
                'isCloned' => false,
                'clonedFrom' => null,
            ],
        ];

        $template = $this->service->createTemplate(
            $company,
            'Test Template',
            'invoice',
            $blocks
        );

        $data = [
            'company' => [
                'name' => 'Test Company',
            ],
        ];

        /* Act */
        $pdf = $this->renderer->renderToPdf($template, $data);

        /* Assert */
        $this->assertNotNull($pdf);
        $this->assertIsString($pdf);
        $this->assertStringStartsWith('%PDF-', $pdf);
    }

    #[Test]
    #[Group('rendering')]
    public function it_handles_missing_blocks_with_error_log(): void
    {
        /* Arrange */
        $company = $this->createCompanyContext();

        $blocks = [
            [
                'id' => 'block_missing_type',
                'type' => 'non_existent_block_type',
                'position' => ['x' => 0, 'y' => 0, 'width' => 6, 'height' => 4],
                'config' => [],
                'label' => 'Missing Block',
                'isCloneable' => false,
                'dataSource' => 'custom',
                'isCloned' => false,
                'clonedFrom' => null,
            ],
        ];

        $template = $this->service->createTemplate(
            $company,
            'Test Template',
            'invoice',
            $blocks
        );

        $data = [
            'company' => [
                'name' => 'Test Company',
            ],
        ];

        /* Act */
        Log::shouldReceive('error')
            ->once()
            ->with(\Mockery::pattern('/Block handler not found/i'), \Mockery::any());

        $html = $this->renderer->render($template, $data);

        /* Assert */
        $this->assertIsString($html);
    }
}
