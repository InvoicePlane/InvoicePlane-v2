<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Modules\Core\Enums\ReportTemplateType;
use Modules\Core\Filament\Company\Pages\ReportBuilder;
use Modules\Core\Filament\Company\Pages\ReportTemplates;
use Modules\Core\Services\ReportTemplateStorage;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\Test;

class CompanyReportBuilderTest extends AbstractCompanyPanelTestCase
{
    protected ReportTemplateStorage $storage;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(ReportTemplateStorage::DISK);

        $this->storage = new ReportTemplateStorage();

        $this->artisan('reports:sync-system');
    }

    #[Test]
    public function it_lists_system_and_company_templates_on_the_company_list_page(): void
    {
        /* Arrange */
        $this->storage->clone('system', 'default', 'Our Invoice', ReportTemplateType::INVOICE);

        /* Act & Assert */
        $this->testLivewire(ReportTemplates::class)
            ->assertSuccessful()
            ->assertSee('Default Invoice')
            ->assertSee('Our Invoice');
    }

    #[Test]
    public function it_treats_system_templates_as_read_only_in_the_company_panel(): void
    {
        /* Act */
        $component = $this->testLivewire(ReportBuilder::class, [
            'scope' => 'system',
            'type'  => 'invoice',
            'slug'  => 'default',
        ])->assertSuccessful();

        /* Assert */
        $this->assertFalse($component->instance()->canSave());

        $schema     = $component->instance()->getForm('form');
        $components = $schema->getComponents();

        $this->assertCount(5, $components);
        foreach ($components as $section) {
            $this->assertInstanceOf(\Filament\Schemas\Components\Section::class, $section);
            $this->assertTrue($section->isCollapsible());
        }
    }

    #[Test]
    public function it_blocks_saving_a_system_template_from_the_company_panel(): void
    {
        /* Arrange */
        $component = $this->testLivewire(ReportBuilder::class, [
            'scope' => 'system',
            'type'  => 'invoice',
            'slug'  => 'default',
        ]);

        /* Assert */
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        /* Act */
        $component->call('save');
    }

    #[Test]
    public function it_clones_a_system_template_and_saves_the_editable_company_copy(): void
    {
        /* Arrange */
        $this->testLivewire(ReportTemplates::class)
            ->callAction('clone', data: ['name' => 'Our Invoice'], arguments: [
                'scope' => 'system',
                'type'  => 'invoice',
                'slug'  => 'default',
            ])
            ->assertHasNoErrors();

        /* Act */
        $component = $this->testLivewire(ReportBuilder::class, [
            'scope' => 'company',
            'type'  => 'invoice',
            'slug'  => 'our-invoice',
        ])->assertSuccessful();

        $this->assertTrue($component->instance()->canSave());

        $bands             = $component->get('data.bands');
        $bands['footer'][] = [
            'type'  => 'masonBrick',
            'attrs' => ['id' => 'page_break', 'config' => []],
        ];

        $component->set('data.bands', $bands)->call('save')->assertHasNoErrors();

        /* Assert */
        $saved      = $this->storage->load('company', 'our-invoice');
        $lastFooter = end($saved['bands']['footer']);
        $this->assertSame('page_break', $lastFooter['brick']);
    }

    #[Test]
    public function it_deletes_a_company_template_from_the_list_page(): void
    {
        /* Arrange */
        $this->storage->clone('system', 'default', 'Doomed', ReportTemplateType::INVOICE);

        /* Act */
        $this->testLivewire(ReportTemplates::class)
            ->callAction('delete', arguments: [
                'scope' => 'company',
                'type'  => 'invoice',
                'slug'  => 'doomed',
            ])
            ->assertHasNoErrors();

        /* Assert */
        $this->assertNull($this->storage->load('company', 'doomed'));
    }

    #[Test]
    public function it_renames_a_company_template_from_the_list_page(): void
    {
        /* Arrange */
        $this->storage->clone('system', 'default', 'Old Name', ReportTemplateType::INVOICE);

        /* Act */
        $this->testLivewire(ReportTemplates::class)
            ->callAction('rename', data: ['name' => 'New Name'], arguments: [
                'scope' => 'company',
                'type'  => 'invoice',
                'slug'  => 'old-name',
            ])
            ->assertHasNoErrors();

        /* Assert */
        $this->assertSame('New Name', $this->storage->load('company', 'old-name')['manifest']['name']);
    }

    #[Test]
    public function it_refuses_to_rename_a_system_template_from_the_company_panel(): void
    {
        /* Act */
        $this->testLivewire(ReportTemplates::class)
            ->callAction('rename', data: ['name' => 'Hijacked'], arguments: [
                'scope'    => 'system',
                'type'     => 'invoice',
                'slug'     => 'default',
                'editable' => true,
            ])
            ->assertHasNoErrors();

        /* Assert */
        $this->assertSame(
            'Default Invoice',
            $this->storage->load('system', 'default', ReportTemplateType::INVOICE)['manifest']['name'],
        );
    }

    #[Test]
    public function it_refuses_to_delete_a_system_template_from_the_company_panel(): void
    {
        /* Arrange */
        $this->storage->clone('system', 'default', 'Spare', ReportTemplateType::INVOICE, 'system');

        /* Act */
        $this->testLivewire(ReportTemplates::class)
            ->callAction('delete', arguments: [
                'scope'    => 'system',
                'type'     => 'invoice',
                'slug'     => 'spare',
                'editable' => true,
            ])
            ->assertHasNoErrors();

        /* Assert */
        $this->assertNotNull($this->storage->load('system', 'spare', ReportTemplateType::INVOICE));
    }

    #[Test]
    public function it_does_not_take_the_editable_flag_from_action_arguments(): void
    {
        /* Arrange */
        $page = $this->testLivewire(ReportTemplates::class)->instance();

        /* Act & Assert */
        $this->assertFalse($page->canModify([
            'scope'    => 'system',
            'slug'     => 'anything',
            'type'     => 'invoice',
            'editable' => true,
        ]));
        $this->assertTrue($page->canModify([
            'scope' => 'company',
            'slug'  => 'anything',
            'type'  => 'invoice',
        ]));
    }
}
